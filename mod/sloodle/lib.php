<?php                          

    /**
    * Sloodle module core library functionality.
    *
    * This script is required by Moodle to contain certain key functionality for the module.
    *
    * @package sloodle
    * @copyright Copyright (c) 2007 Sloodle (various contributors)
    * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
    *
    * @contributor Peter R. Bloomfield
    *
    */                   
    
    require_once($CFG->dirroot.'/mod/sloodle/sl_config.php');
    require_once(SLOODLE_LIBROOT.'/general.php');
    

    /**
    * Processes the module configuration options prior to saving them.
    * Only used when the "config.html" file is used for module configuration.
    * Moodle 1.9 now prefers the "settings.php" file.
    *
    * @param object &$config Reference to an object containing the configuration settings (can be edited)
    * @return void
    */
    function sloodle_process_options(&$config)
    {
    }
    

    /**
     * Given an object containing all the necessary data, 
     * (defined by the form in mod.html) this function 
     * will create a new instance and return the id number 
     * of the new instance.
     *
     * @param object $instance An object from the form in mod.html
     * @return int The id of the newly inserted sloodle record
     **/
    function sloodle_add_instance($sloodle) {
        global $CFG;

        // Set the creation and modification times
        $sloodle->timecreated = time();
        $sloodle->timemodified = time();
        
        // Prefix the full type name on the instance name
        $sloodle->name = get_string("moduletype:{$sloodle->type}", 'sloodle') . ': ' . $sloodle->name;
            
        // Attempt to insert the new Sloodle record
        if (!$sloodle->id = insert_record('sloodle', $sloodle)) {
            error(get_string('failedaddinstance', 'sloodle'));
        }
        
        // We need to create a new secondary table for this module
        $sec_table = new stdClass();
        $sec_table->sloodleid = $sloodle->id;
        
        // Check the type of this module
        $result = FALSE;
        $errormsg = '';
        switch ($sloodle->type) {
        case SLOODLE_TYPE_CTRL:
            // Create a new controller record
            if (isset($sloodle->controller_enabled) && $sloodle->controller_enabled) $sec_table->enabled = 1;
            else $sec_table->enabled = 0;
            $sec_table->password = $sloodle->controller_password;
            
            // Attempt to add it to the database
            if (!insert_record('sloodle_controller', $sec_table)) {
                $errormsg = get_string('failedaddsecondarytable', 'sloodle');
            } else {
                $result = TRUE;
            }
            break;

        case SLOODLE_TYPE_DISTRIB:
            // Add in a default blank channel number and update time
            $sec_table->channel = '';
            $sec_table->timeupdated = 0;
        
            // Attempt to add it to the database
            if (!insert_record('sloodle_distributor', $sec_table)) {
                $errormsg = get_string('failedaddsecondarytable', 'sloodle');
            } else {
                $result = TRUE;
            }        
            break;
            
        case SLOODLE_TYPE_PRESENTER:
            // Add in the dimensions of the frame
            $sec_table->framewidth = (int)$sloodle->presenter_framewidth;
            $sec_table->frameheight = (int)$sloodle->presenter_frameheight;
            // Attempt to add it to the database
            if (!insert_record('sloodle_presenter', $sec_table)) {
                $errormsg = get_string('failedaddsecondarytable', 'sloodle');
            } else {
                $result = TRUE;
            }
            break;

        // ADD FURTHER MODULE TYPES HERE!
            
        default:
            // Type not recognised - this really shouldn't kill the script though... makes development very hard
            //$errormsg = error(get_string('moduletypeunknown', 'sloodle'));
            $result = TRUE;
            break;
        }
        
        // Was there a problem?
        if (!$result) {
            // Yes
            // Delete the Sloodle instance
            delete_records('sloodle', 'id', $sloodle->id);
            
            // Show the error message (if there was one)
            if (!empty($errormsg)) error($errormsg);
            return FALSE;
        }
        
        // Success!
        return $sloodle->id;
    }

    /**
     * Given an object containing all the necessary data, 
     * (defined by the form in mod.html) this function 
     * will update an existing instance with new data.
     *
     * @param object $instance An object from the form in mod.html
     * @return boolean Success/Fail
     **/
    function sloodle_update_instance($sloodle) {
        global $CFG;

        // Update the modification time
        $sloodle->timemodified = time();
        // Make sure the ID is correct for update
        $sloodle->id = $sloodle->instance;
        
        // Make sure the type is the same as the existing record
        $existing_record = get_record('sloodle', 'id', $sloodle->id);
        if (!$existing_record) error(get_string('modulenotfound', 'sloodle'));
        if ($existing_record->type != $sloodle->type) error(get_string('moduletypemismatch', 'sloodle'));
        
        // Check the type of this module
        switch ($sloodle->type) {
        case SLOODLE_TYPE_CTRL:
            // Attempt to fetch the controller record
            $ctrl = get_record('sloodle_controller', 'sloodleid', $sloodle->id);
            if (!$ctrl) error(get_string('secondarytablenotfound', 'sloodle'));
            
            // Add the updated 'enabled' value
            if (isset($sloodle->controller_enabled) && $sloodle->controller_enabled) $ctrl->enabled = 1;
            else $ctrl->enabled = 0;
            // Add the updated password
            $ctrl->password = $sloodle->controller_password;
            
            
            // Update the database
            update_record('sloodle_controller', $ctrl);
            
            break;
            
        case SLOODLE_TYPE_DISTRIB:
            // Attempt to fetch the distributor record
            $distrib = get_record('sloodle_distributor', 'sloodleid', $sloodle->id);
            if (!$distrib) error(get_string('secondarytablenotfound', 'sloodle'));
            
            // Has a reset been requested?
            if ($sloodle->distributor_reset) {
                // Yes - clear the distributor channel
                $distrib->channel = '';
                
                // Delete all objects associated with the Distributor
                delete_records('sloodle_distributor_entry', 'distributorid', $distrib->id);
            }
            
            
            // Update the database
            update_record('sloodle_distributor', $distrib);
            
            break;
            
        case SLOODLE_TYPE_PRESENTER:
            // Attempt to fetch the Presenter record
            $presenter = get_record('sloodle_presenter', 'sloodleid', $sloodle->id);
            if (!$presenter) error(get_string('secondarytablenotfound', 'sloodle'));

            // Add the updated frame dimensions
            $presenter->framewidth = (int)$sloodle->presenter_framewidth;
            $presenter->frameheight = (int)$sloodle->presenter_frameheight;

            // Update the database
            update_record('sloodle_presenter', $presenter); 

            break;

            
        // ADD FURTHER MODULE TYPES HERE!
            
        default:
            // Type not recognised
            //error(get_string('moduletypeunknown', 'sloodle'));
            break;
        }

        // Attempt the update
        return update_record('sloodle', $sloodle);
    }

    /**
     * Given an ID of an instance of this module, 
     * this function will permanently delete the instance 
     * and any data that depends on it. 
     *
     * @param int $id Id of the module instance
     * @return boolean Success/Failure
     **/
    function sloodle_delete_instance($id) {

        // Determine our success or otherwise
        $result = true;

        // Attempt to delete the main Sloodle instance
        if (!delete_records('sloodle', 'id', $id)) {
            $result = false;
        }
        
        // Delete any secondary controller tables
        delete_records('sloodle_controller', 'sloodleid', $id);
        
        // Attempt to get any secondary distributor tables
        $distribs = get_records('sloodle_distributor', 'sloodleid', $id);
        if (is_array($distribs) && count($distribs) > 0) {
            // Go through each distributor
            foreach ($distribs as $d) {
                // Delete any related distributor entries
                delete_records('sloodle_distributor_entry', 'distributorid', $d->id);
            }
        }
        // Delete all the distributors
        delete_records('sloodle_distributor', 'sloodleid', $id);

        // Delete any presenter entries
        delete_records('sloodle_presenter_entry', 'sloodleid', $id);

        // ADD FURTHER MODULE TYPES HERE!

        return $result;
    }

    /**
     * Return a small object with summary information about what a 
     * user has done with a given particular instance of this module
     * Used for user activity reports.
     * $return->time = the time they did it
     * $return->info = a short text description
     *
     * @return null
     * @todo Do we need this function to do anything?
     **/
    function sloodle_user_outline($course, $user, $mod, $sloodle) {
        return NULL;
    }

    /**
     * Print a detailed representation of what a user has done with 
     * a given particular instance of this module, for user activity reports.
     *
     * @return boolean
     * @todo Do we need this function to do anything?
     **/
    function sloodle_user_complete($course, $user, $mod, $sloodle) {
        return true;
    }

    /**
     * Given a course and a time, this module should find recent activity 
     * that has occurred in sloodle activities and print it out. 
     * Return true if there was output, or false is there was none. 
     *
     * @uses $CFG
     * @return boolean
     * @todo Figure out if we need this function to do anything!
     **/
    function sloodle_print_recent_activity($course, $isteacher, $timestart) {
        global $CFG;

        return false;  //  True if anything was printed, otherwise false 
    }

    /**
     * Function to be run periodically according to the Moodle cron.
     * Clears out expired pending user entries, session keys, etc.
     *
     * @return boolean
     * @todo Delete expired user entries
     * @todo Use a custom delay for expiries of users/objects
     **/
    function sloodle_cron () {
        echo "\nProcessing Sloodle cron tasks:\n";
        
        // Delete any pending user entries which have expired (more than 30 minutes old)
        $expirytime = time() - 1800;
        echo "Removing expired pending avatar entries...\n";
        delete_records_select('sloodle_pending_avatars', "timeupdated < $expirytime");
        
        // Delete any LoginZone allocations which have expired (more than 15 minutes old)
        $expirytime = time() - 900;
        echo "Removing expired LoginZone allocations...\n";
        delete_records_select('sloodle_loginzone_allocation', "timecreated < $expirytime");
        
        // Fetch our configuration settings, if they exist
        $active_object_days = 7; // Give active objects a week by default
        if (!empty($CFG->sloodle_active_object_lifetime)) $active_object_days = $CFG->sloodle_active_object_lifetime;
        $user_object_days = 21; // Give user objects 3 weeks by default
        if (!empty($CFG->sloodle_user_object_lifetime)) $user_object_days = $CFG->sloodle_user_object_lifetime;
        
        // Delete any active objects and session keys which have expired
        // Deletes any authorised objects which have not checked-in for more than X days (where X is specified in module config)
        // Deletes any unauthorised objects which have not checked-in for more than 1 hour
        $expirytime_auth = time() - (86400 * $active_object_days);
        $expirytime_unauth = time() - 3600;
        echo "Searching for expired active objects...\n";
        $recs = get_records_select('sloodle_active_object', "(((controllerid = 0 AND userid = 0) OR type = '') AND timeupdated < $expirytime_unauth) OR timeupdated < $expirytime_auth");
        if ($recs) {
            // Go through each object
            echo "Removing " . count($recs) . " expired active objects and associated configuration settings...\n";
            foreach ($recs as $r) {
                delete_records('sloodle_object_config', 'object', $r->id);
                delete_records('sloodle_active_object', 'id', $r->id);
            }            
        }
        
        // Delete any user-authorised objects which have expired
        // (will eventually use custom expiry times, chosen in the configuration)
        // Deletes any authorised objects which have not checked-in for more than X days (where X is specified in module config)
        // Deletes any unauthorised objects which have not checked-in for more than 1 hour
        $expirytime_auth = time() - (86400 * $user_object_days);
        $expirytime_unauth = time() - 3600;
        echo "Deleting expired user objects...\n";
        delete_records_select('sloodle_user_object', "(authorised = 0 AND timeupdated < $expirytime_unauth) OR timeupdated < $expirytime_auth");
        
        // More stuff?
        //...
        
        // Email login details to auto-registered avatars in-world
        echo "Sending out login notifications...\n";
        sloodle_process_login_notifications();

        echo "Done processing Sloodle cron tasks.\n\n";
        return true;
    }

    /**
     * Must return an array of grades for a given instance of this module, 
     * indexed by user.  It also returns a maximum allowed grade.
     * 
     * Example:
     *    $return->grades = array of grades;
     *    $return->maxgrade = maximum allowed grade;
     *
     *    return $return;
     *
     * @param int $sloodleid ID of an instance of this module
     * @return mixed Null or object with an array of grades and with the maximum grade
     **/
    function sloodle_grades($sloodleid) {
       return NULL;
    }

    /**
     * Must return an array of user records (all data) who are participants
     * for a given instance of sloodle. Must include every user involved
     * in the instance, independient of his role (student, teacher, admin...)
     * See other modules as example.
     *
     * @param int $sloodleid ID of an instance of this module
     * @return mixed boolean/array of students
     * @todo Possibly make it return a list of users registered via the Sloodle instance?
     **/
    function sloodle_get_participants($sloodleid) {
        return false;
    }

    /**
     * This function returns if a scale is being used by one sloodle
     * it it has support for grading and scales. Commented code should be
     * modified if necessary. See forum, glossary or journal modules
     * as reference.
     *
     * @param int $sloodleid ID of an instance of this module
     * @return mixed
     **/
    function sloodle_scale_used ($sloodleid,$scaleid) {
        $return = false;
       
        return $return;
    }


    /**
    * Gets the different sub-types of Sloodle module available as a list for the "Add Activity..." menu.
    * Also adds the 'Sloodle Map' resource type.
    *
    * $return array Entries for the "Add Activity..." and "Add Resource..." menus.
    */

    function sloodle_get_types() {
        global $CFG, $SLOODLE_TYPES;
        $types = array();

        // Start the group of activities
        $type = new object();
        $type->modclass = MOD_CLASS_ACTIVITY;
        $type->type = "sloodle_group_start";
        $type->typestr = '--'.get_string('modulenameplural', 'sloodle');
        $types[] = $type;
         
        // Go through each Sloodle module type, and add it
        foreach ($SLOODLE_TYPES as $st) {
            $type = new object();
            $type->modclass = MOD_CLASS_ACTIVITY;
            $type->type = "sloodle&amp;type=$st";
            $type->typestr = get_string("moduletype:$st", 'sloodle');
            $types[] = $type;
        }

        // End the group of activities
        $type = new object();
        $type->modclass = MOD_CLASS_ACTIVITY;
        $type->type = "sloodle_group_end";
        $type->typestr = '--';
        $types[] = $type;

        return $types;
    }



?>
