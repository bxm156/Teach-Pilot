<?php
    /**
    * SLOODLE chat linker
    * Allows a Sloodle WebIntercom tool link into a Moodle chatroom.
    * Fetches a recent chat history, and optionally inserts a new message.
    *
    * @package sloodlechat
    * @copyright Copyright (c) 2007-10 (various contributors)
    * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
    *
    * @todo Implement capabilities to make sure users can write to the chatroom (will need special handling to let guest users be permitted if desired)
    *
    * @contributor (various)
    * @contributor Peter R. Bloomfield
	* @contributor Edmund Edgar
    */
    
    // This script should be called with the following parameters:
    //  sloodlecontrollerid = ID of a Sloodle Controller through which to access Moodle
    //  sloodlepwd = the prim password or object-specific session key to authenticate the request
    //  sloodlemoduleid = ID of a chatroom
    //
    // If adding a new message, the following parameters should be provided:
    //  sloodleuuid = UUID of the avatar
    //  sloodleavname = name of the avatar
    //  message = the body of the message.
    //
    // The following parameters are optional:
	//  firstmessageid = if specified, only messages whose ID number is greater than or equal to this value will be returned in the chat history
    //  sloodledebug = if 'true', then Sloodle debugging mode is activated
	//
    

    /** Lets Sloodle know we are in a linker script. */
    define('SLOODLE_LINKER_SCRIPT', true);
    
    /** Grab the Sloodle/Moodle configuration. */
    require_once('../../sl_config.php');
    /** Include the Sloodle PHP API. */
    require_once(SLOODLE_LIBROOT.'/sloodle_session.php');
    
    // Authenticate the request, and load a chat module
    $sloodle = new SloodleSession();
    $sloodle->authenticate_request();
    $sloodle->load_module('chat', true);
    // Attempt to validate the user
    // (this will auto-register/enrol users where necessary and allowed)
    // If server access level is public, then validation is not essential... otherwise, it is
    $sloodleserveraccesslevel = $sloodle->request->get_server_access_level(false);
    if ($sloodleserveraccesslevel == 0) $sloodle->validate_user(false);
    else $sloodle->validate_user(true);
    
    // Has an incoming message been provided?
    $message = sloodle_clean_for_db($sloodle->request->optional_param('message', null));
    if ($message != null) {
        // Add it to the chatroom.
        if (!$sloodle->module->add_message($message)) {
			add_to_log($sloodle->course->get_course_id(), 'sloodle', 'add message', '', 'Added chat message to chatroom', $sloodle->request->get_module_id());
        } else {
			add_to_log($sloodle->course->get_course_id(), 'sloodle', 'add message', '', 'Failed to add chat message to chatroom', $sloodle->request->get_module_id());
        }
    }
    
    // Start preparing the response
    $sloodle->response->set_status_code(1);
    $sloodle->response->set_status_descriptor('OK');
    
    // Fetch a chat history.
	// Always limit it to the last 60 seconds, but optionally also ignore everything before a certain point.
    $messages = $sloodle->module->get_chat_history(60, $sloodle->request->optional_param('firstmessageid', 0));
    foreach ($messages as $m) {
        $author = sloodle_clean_for_output($m->authorname);
        $sloodle->response->add_data_line(array($m->id, $author, sloodle_clean_for_output($m->message)));
    }
    
    // Output our response
    $sloodle->response->render_to_output();
    
?>