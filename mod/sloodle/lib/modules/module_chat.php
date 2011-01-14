<?php
    // This file is part of the Sloodle project (www.sloodle.org)
    
    /**
    * This file defines a chat module for Sloodle.
    *
    * @package sloodle
    * @copyright Copyright (c) 2008 Sloodle (various contributors)
    * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
    *
    * @contributor Peter R. Bloomfield
    */
    
    /** The Sloodle module base. */
    require_once(SLOODLE_LIBROOT.'/modules/module_base.php');
    /** General Sloodle functions. */
    require_once(SLOODLE_LIBROOT.'/general.php');
    
    /**
    * The Sloodle chat module class.
    * @package sloodle
    */
    class SloodleModuleChat extends SloodleModule
    {
    // DATA //
    
        /**
        * Internal for Moodle only - course module instance.
        * Corresponds to one record from the Moodle 'course_modules' table.
        * @var object
        * @access private
        */
        var $cm = null;
    
        /**
        * Internal only - Moodle chat module instance database object.
        * Corresponds to one record from the Moodle 'chat' table.
        * @var object
        * @access private
        */
        var $moodle_chat_instance = null;

                
        
    // FUNCTIONS //
    
        /**
        * Constructor
        */
        function SloodleModuleChat(&$_session)
        {
            $constructor = get_parent_class($this);
            parent::$constructor($_session);
        }
        
        /**
        * Loads data from the database.
        * Note: even if the function fails, it may still have overwritten some or all existing data in the object.
        * @param mixed $id The site-wide unique identifier for all modules. Type depends on VLE. On Moodle, it is an integer course module identifier ('id' field of 'course_modules' table)
        * @return bool True if successful, or false otherwise
        */
        function load($id)
        {
            // Make sure the ID is valid
            $id = (int)$id;
            if ($id <= 0) return false;
            
            // Fetch the course module data
            if (!($this->cm = get_coursemodule_from_id('chat', $id))) {
                sloodle_debug("Failed to load course module instance #$id.<br/>");
                return false;
            }
            // Make sure the module is visible
            if ($this->cm->visible == 0) {
                sloodle_debug("Error: course module instance #$id not visible.<br/>");
                return false;
            }
            
            // Load from the primary table: chat instance
            if (!($this->moodle_chat_instance = get_record('chat', 'id', $this->cm->instance))) {
                sloodle_debug("Failed to load chatroom with instance ID #{$cm->instance}.<br/>");
                return false;
            }
            
            return true;
        }
        
        
        /**
        * Gets a recent history of messages from the chatroom.
        * @param int $time Optional. How far back to search the database (in seconds) (default: 1 minute)
		* @param int $firstmessageid Optional. If specified then no messages with an ID below this value will be returned. This can be useful for ignoring messages which have already been received.
        * @return array A numeric array of {@link SloodleChatMessage} object, in order of oldest to newest
        */
        function get_chat_history($time = 60, $firstmessageid = 0)
        {
			global $CFG;
            // Calculate the earliest acceptable timestamp
            $earliest = time() - (int)$time;
			// Construct a condition for the first message ID if it was specified
			$firstmessageid = (int)$firstmessageid;
			$messageidcond = '';
			if ($firstmessageid > 0) $messageidcond = "AND cmsg.id >= {$firstmessageid}";
			
            // Get all message records for this chatroom.
			// Join user data on so that we don't need to do separate requests for each user.
			$query = "
				SELECT
					cmsg.id, cmsg.chatid, cmsg.message, cmsg.timestamp,
					u.firstname, u.lastname
				FROM
					{$CFG->prefix}chat_messages cmsg
				JOIN
					{$CFG->prefix}user u
					ON cmsg.userid = u.id
				WHERE
					cmsg.chatid = {$this->moodle_chat_instance->id}
					AND cmsg.timestamp >= {$earliest}
					{$messageidcond}
				ORDER BY
					cmsg.timestamp ASC,
					cmsg.id
			";
			$recs = get_records_sql($query);
			if (!$recs) return array();
            
			// Go through each result from the database query and add it to a numeric array of chat message objects
			$chatmessages = array();
			foreach ($recs as $r) {
				//$chatmessages[] = new SloodleChatMessage($r['cmsg.id'], $r['cmsg.message'], $r['u.id'], $r['u.firstname'].' '.$r['u.lastname'], $r['cmsg.timestamp']);
				$chatmessages[] = new SloodleChatMessage($r->id, $r->message, $r->firstname.' '.$r->lastname, $r->timestamp);
			}
            
            return $chatmessages;
        }
        
        
        /**
        * Adds a new chat message.
        * <b>Note:</b> if the $author parameter is omitted or invalid, then the function will attempt to use the {@link SloodleUser} member
        * of the current {@link SloodleSession} object;
        * If that is unavailable, then it will try to use the user currently 'logged-in' to the VLE (i.e. the $USER variable in Moodle).
        * If all else fails, it will attempt to attribute the message to the guest user.
        * @param string $message The text of the message.
        * @param mixed $user The user who wrote the message -- either a VLE user ID or (preferably) a {@link SloodleUser} object. If null, then the user in the current SloodleSession object will be used. If that fails, then the guest user is used if possible.
        * @param int $timestamp Timestamp of the message. If omitted or <= 0 then the current timestamp is used
        * @return bool True if successful, or false otherwise
        */
        function add_message($message, $user = null, $timestamp = null)
        {
            // Ignore empty messages
            if (empty($message)) return false;
            // Make sure the message is safe
            $message = addslashes(clean_text(stripslashes($message)));
            
            // We need to get the user ID for the message
            $userid = 0;
            
            // Has a user object been provided?
            if (is_object($user)) {
                // Yes - grab the user ID
                $userid = $user->get_user_id();
            } else if ($user != null) {
                // May be an ID
                $userid = (int)$user;
            }
            
            // Did we end up with a valid user ID?
            if ((int)$userid <= 0) {
                // No - do we have a user in the session parameter?
                if (isset($this->_session->user)) {
                    // Store the user ID
                    $userid = $this->_session->user->get_user_id();
                }
            }
            
            // Are we still lacking a valid user?
            if ((int)$userid <= 0) {
                // Yes - user the guest user
                $guest = guest_user();
                if ($guest) $userid = $guest->id;
            }            
            
            // Prepare the timestamp variable if necessary
            if (is_null($timestamp)) $timestamp = time();
            
            // Create a chat message record object
            $rec = new stdClass();
            $rec->chatid = $this->moodle_chat_instance->id;
            $rec->userid = $userid;
            $rec->message = $message;
            $rec->timestamp = $timestamp;
            // Attempt to insert the chat message
            $result = insert_record('chat_messages', $rec);
            if (!$result)
			{
				// Insertion failed.
				// If possible, add an appropriate side effect code to our response
				if (isset($this->_session->response)) {
					$this->_session->response->add_side_effect(-10101);
				}
			}
            
            // We successfully added a chat message
            // If possible, add an appropriate side effect code to our response
            if (isset($this->_session->response)) {
                $this->_session->response->add_side_effect(10101);
            }
            
            return true;
        }
        
        
    // ACCESSORS //
    
        /**
        * Gets the name of this module instance.
        * @return string The name of this controller
        */
        function get_name()
        {
            return $this->moodle_chat_instance->name;
        }
        
        /**
        * Gets the intro description of this module instance, if available.
        * @return string The intro description of this controller
        */
        function get_intro()
        {
            return $this->moodle_chat_instance->intro;
        }
        
        /**
        * Gets the identifier of the course this controller belongs to.
        * @return mixed Course identifier. Type depends on VLE. (In Moodle, it will be an integer).
        */
        function get_course_id()
        {
            return (int)$this->moodle_chat_instance->course;
        }
        
        /**
        * Gets the time at which this instance was created, or 0 if unknown.
        * @return int Timestamp
        */
        function get_creation_time()
        {
            return 0;
        }
        
        /**
        * Gets the time at which this instance was last modified, or 0 if unknown.
        * @return int Timestamp
        */
        function get_modification_time()
        {
            return $this->moodle_chat_instance->timemodified;
        }
        
        
        /**
        * Gets the short type name of this instance.
        * @return string
        */
        function get_type()
        {
            return 'chat';
        }

        /**
        * Gets the full type name of this instance, according to the current language pack, if available.
        * Note: should be overridden by sub-classes.
        * @return string Full type name if possible, or the short name otherwise.
        */
        function get_type_full()
        {
            return get_string('modulename', 'chat');
        }

    }
    
    
    /**
    * Represents a single chat message
    * @package sloodle
    */
    class SloodleChatMessage
    {
        /**
        * Constructor - initialises members.
        * @param mixed $id The ID of this message - type depends on VLE, but is typically an integer
        * @param string $message The chat message
        * @param int $authorid ID of the user who wrote this message
		* @param int $authorname Name of the user who wrote this message
        * @param int $timestamp The timestamp of the message
        */
        function SloodleChatMessage($id, $message, $authorname, $timestamp)
        {
            $this->id = $id;
            $this->message = $message;
			$this->authorname = $authorname;
            $this->timestamp = $timestamp;
        }
        
        /**
        * Accessor - set all members in a single call.
        * @param mixed $id The ID of this message - type depends on VLE, but is typically an integer
        * @param string $message The chat message
        * @param int $authorid ID of the user who wrote this message
		* @param int $authorname Name of the user who wrote this message
        * @param int $timestamp The timestamp of the message
        */
        function set($id, $message, $authorname, $timestamp)
        {
            $this->id = $id;
            $this->message = $message;
			$this->authorname = $authorname;
            $this->timestamp = $timestamp;
        }
        
        /**
        * The ID of the message.
        * The type depends on the VLE, but typically is an integer.
        * @var mixed
        * @access public
        */
        var $id = 0;
    
        /**
        * The text of the message.
        * @var string
        * @access public
        */
        var $message = '';
		
		/**
		* Full name of the person who wrote this message.
		* @var string
		* @access public
		*/
		var $authorname = '';
        
        /**
        * Timestamp of the message.
        * @var int
        * @access public
        */
        var $timestamp = 0;
    }


?>