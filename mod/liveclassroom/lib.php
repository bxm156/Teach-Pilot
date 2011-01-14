<?php

/******************************************************************************
 *                                                                            *
 * Copyright (c) 1999-2008  Wimba, All Rights Reserved.                       *
 *                                                                            *
 * COPYRIGHT:                                                                 *
 *      This software is the property of Wimba.                               *
 *      You can redistribute it and/or modify it under the terms of           *
 *      the GNU General Public License as published by the                    *
 *      Free Software Foundation.                                             *
 *                                                                            *
 * WARRANTIES:                                                                *
 *      This software is distributed in the hope that it will be useful,      *
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *      GNU General Public License for more details.                          *
 *                                                                            *
 *      You should have received a copy of the GNU General Public License     *
 *      along with the Wimba Moodle Integration;                              *
 *      if not, write to the Free Software Foundation, Inc.,                  *
 *      51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA                *
 *                                                                            *
 * Author: Hugues Pisapia                                                     *
 *                                                                            *
 * Date: 15th April 2006                                                      *
 *                                                                            *
 ******************************************************************************/
/* $Id: lib.php 76480 2009-09-30 21:11:56Z trollinger $ */
// / Library of functions and constants for module liveclassroom

if (!function_exists('getKeysOfGeneralParameters')) {
    require_once('lib/php/common/WimbaLib.php');
}
require_once($CFG->libdir . '/datalib.php');

require_once("lib/php/lc/lcapi.php");
require_once("lib/php/lc/LCAction.php");
require_once("lib/php/lc/PrefixUtil.php");
define("LIVECLASSROOM_MODULE_VERSION", "4.0.1-1");
define("WC", "wimbaclassroom");

/**
 * Validate the data in passed in the configuration page
 * 
 * @param  $config - the information from the form mod.html
 * @return nothing , but returns an error if the configuration is wrong
 */
function liveclassroom_process_options (&$config)
{
    global $CFG, $USER;
    /*******
    we do the following verfication before submitting the configuration
    -The parameters sent can not be empty
    -The url of the server can not finish with a /
    -The url must start with http:// 
    -The api account has to valid
    ********/
    
    $config->servername    = trim($config->servername);
    $config->adminusername = trim($config->adminusername);
    $config->adminpassword = trim($config->adminpassword);

    if (! isadmin($USER->id)) 
    {
        wimba_add_log(WIMBA_ERROR,WC,get_string('wrongconfigurationURLunavailable', 'liveclassroom'));
        error(get_string('errormustbeadmin', 'liveclassroom'));
    } 

    if (empty($config->servername)) 
    {
        wimba_add_log(WIMBA_ERROR,WC,get_string('wrongconfigurationURLunavailable', 'liveclassroom'));
        error(get_string('wrongconfigurationURLunavailable', 'liveclassroom'), $_SERVER["HTTP_REFERER"]);
    } 
    else if (empty($config->adminusername)) 
    {
        wimba_add_log(WIMBA_ERROR,WC,get_string('emptyAdminUsername', 'liveclassroom'));
        error(get_string('emptyAdminUsername', 'liveclassroom'), $_SERVER["HTTP_REFERER"]);
    } 
    else if (empty($config->adminpassword)) 
    {
        wimba_add_log(WIMBA_ERROR,WC,get_string('emptyAdminPassword', 'liveclassroom'));
        error(get_string('emptyAdminPassword', 'liveclassroom'), $_SERVER["HTTP_REFERER"]);
    } 

    $length = strlen($config->servername);
    if ($config->servername {$length-1} == '/') 
    {
        wimba_add_log(WIMBA_ERROR,WC,get_String('trailingSlash', 'liveclassroom'));
        error(get_String('trailingSlash', 'liveclassroom'), $_SERVER["HTTP_REFERER"]);
    } 
    
    if (!preg_match('/^http:\/\//', $config->servername)) 
    {
        wimba_add_log(WIMBA_ERROR,WC,get_String('trailingHttp', 'liveclassroom'));    
        error(get_String('trailingHttp', 'liveclassroom'), $_SERVER["HTTP_REFERER"]);
    } 
    $prefixUtil = new PrefixUtil();
    $prefix = $prefixUtil->getPrefix($config->adminusername);
    $api = new LCApi($config->servername,
        $config->adminusername,
        $config->adminpassword, $prefix);

    if (! $api->lcapi_authenticate()) 
    {
        wimba_add_log(WIMBA_ERROR,WC,get_string('wrongadminpass', 'liveclassroom'));
        error(get_string('wrongadminpass', 'liveclassroom'), $_SERVER["HTTP_REFERER"]);
    } 
    $domxml = false;
    $php_extension = get_loaded_extensions();
    for( $i = 0; $i< count($php_extension); $i++)
    {
        if($php_extension[$i] == "libxml" || $php_extension[$i] == "domxml")
        {
             $domxml=true;
        }
    }
    if($domxml === false)
    {
        wimba_add_log(WIMBA_ERROR,WC,get_string('domxml', 'liveclassroom'));
        error(get_string('domxml', 'liveclassroom'), $_SERVER["HTTP_REFERER"]);
    }
    return;
} 

/*
*  Create a new instance of liveclassroom
* @param $liveclassroom : object liveclassroom
*/
function liveclassroom_add_instance($liveclassroom)
{

    global $CFG;
    // / Given an object containing all the necessary data,
    // / (defined by the form in mod.html) this function
    // / will create a new instance and return the id number
    // / of the new instance.
    $liveclassroom->timemodified = time();
    $liveclassroom->type = $liveclassroom->resource;
    
    $api = new LCAction(null, 
                        $CFG->liveclassroom_servername, 
                        $CFG->liveclassroom_adminusername, 
                        $CFG->liveclassroom_adminpassword, 
                        $CFG->dataroot);
    // May have to add extra stuff in here #
    $roomname = $api->getRoomName($liveclassroom->type); 
        
    if (!$liveclassroom->id  = insert_record("liveclassroom", $liveclassroom)) {
        wimba_add_log(WIMBA_ERROR,WC,"Problem to add a new instance");
        return false;
    } 
    
    if (isset($liveclassroom->calendar_event) && $liveclassroom->calendar_event == true) 
    { // no problem
        liveclassroom_addCalendarEvent($liveclassroom, $liveclassroom->id, $roomname);
    } 
    
    wimba_add_log(WIMBA_INFO,WC,"Add Instance");  
    //for the debug
    wimba_add_log(WIMBA_DEBUG,WC,print_r($liveclassroom, true )); 
    return $liveclassroom->id;
} 

function liveclassroom_update_instance($liveclassroom)
{
	global $CFG;
    // / Given an object containing all the necessary data,
    // / (defined by the form in mod.html) this function
    // / will update an existing instance with new data.
    $liveclassroom->timemodified = time();
    $liveclassroom->type = $liveclassroom->resource;
    $liveclassroom->id = $liveclassroom->instance;
    $api = new LCAction(null, 
                        $CFG->liveclassroom_servername, 
                        $CFG->liveclassroom_adminusername, 
                        $CFG->liveclassroom_adminpassword, 
                        $CFG->dataroot);
    // May have to add extra stuff in here #
    $roomname = $api->getRoomName($liveclassroom->type); 
    // Need to update the section
    // get the course_module instance linked to the liveclassroom instance
    if (! $cm = get_coursemodule_from_instance("liveclassroom", $liveclassroom->id, $liveclassroom->course))
    {
        wimba_add_log(WIMBA_ERROR,WC,"Problem to update the instance : ".$liveclassroom->id); 
        error("Course Module ID was incorrect");
    } 
    $old_section = $cm->section; 
    // Find the right section in the course_section
    $section = get_record("course_sections", "id", $cm->section); 
    // delete in the course section
    if (! delete_mod_from_section($cm->id, $cm->section)) 
    {
        $result = false;
        error("Could not delete the $mod->modulename from that section");
    } 
    // update the course module section
    if (! $sectionid = add_mod_to_section($liveclassroom)) 
    {
        wimba_add_log(WIMBA_ERROR,WC,"Problem to update the instance : ".$liveclassroom->id);   
        error("Could not add the new course module to that section");
    } 
    // update the course modules
    if (! set_field("course_modules", "section", $sectionid, "id", $cm->id)) 
    {
        wimba_add_log(WIMBA_ERROR,WC,"Problem to update the instance : ".$liveclassroom->id);     
        error("Could not update the course module with the correct section");
    } 

    if (!isset($liveclassroom->section)) 
    {
        $liveclassroom->section = 0;
    } 

    $instanceNumber = update_record("liveclassroom", $liveclassroom);
    if ($instanceNumber != false && isset($liveclassroom->calendar_event) && $liveclassroom->calendar_event) { // no problem
        liveclassroom_addCalendarEvent($liveclassroom, $liveclassroom->instance, $roomname);
    } 
    else 
    {
        liveclassroom_deleteCalendarEvent($liveclassroom->instance);
    } 
    return $instanceNumber;
} 

function liveclassroom_delete_instance($id)
{
	global $CFG;
    // / Given an ID of an instance of this module,
    // / this function will permanently delete the instance
    // / and any data that depends on it.
    $api = new LCAction(null, 
                        $CFG->liveclassroom_servername, 
                        $CFG->liveclassroom_adminusername, 
                        $CFG->liveclassroom_adminpassword, 
                        $CFG->dataroot);
    // May have to add extra stuff in here #
    $roomname = $api->getRoomName($liveclassroom->type); 

    if (! $liveclassroom = get_record("liveclassroom", "id", "$id")) 
    {
        return false;
    } 
    $result = true; 
    // Delete any dependent records here #
    if (! delete_records("liveclassroom", "id", $liveclassroom->id)) 
    {
        wimba_add_log(WIMBA_ERROR,WC,"Problem to delete the instance : ".$liveclassroom->id); 
        $result = false;
    } 
    
    liveclassroom_deleteCalendarEvent("$liveclassroom->id");
    return $result;
} 

function liveclassroom_user_outline($course, $user, $mod, $liveclassroom)
{
    // / Return a small object with summary information about what a
    // / user has done with a given particular instance of this module
    // / Used for user activity reports.
    // / $return->time = the time they did it
    // / $return->info = a short text description
    return false; //$return;
} 

function liveclassroom_user_complete($course, $user, $mod, $liveclassroom)
{
    // / Print a detailed representation of what a  user has done with
    // / a given particular instance of this module, for user activity reports.
    return true;
} 

function liveclassroom_print_recent_activity($course, $isteacher, $timestart)
{
    // / Given a course and a time, this module should find recent activity
    // / that has occurred in liveclassroom activities and print it out.
    // / Return true if there was output, or false is there was none.
    global $CFG;

    return false; //  True if anything was printed, otherwise false 
} 

function liveclassroom_cron ()
{
    // / Function to be run periodically according to the moodle cron
    // / This function searches for things that need to be done, such
    // / as sending out mail, toggling flags etc ...
    global $CFG;

    return true;
} 

function liveclassroom_grades($liveclassroomid)
{
    // / Must return an array of grades for a given instance of this module,
    // / indexed by user.  It also returns a maximum allowed grade.
    // /
    // /    $return->grades = array of grades;
    // /    $return->maxgrade = maximum allowed grade;
    // /
    // /    return $return;
    return null;
} 

function liveclassroom_get_participants($liveclassroomid)
{
    // Must return an array of user records (all data) who are participants
    // for a given instance of liveclassroom. Must include every user involved
    // in the instance, independient of his role (student, teacher, admin...)
    // See other modules as example.
    return false;
} 

function liveclassroom_scale_used ($liveclassroomid, $scaleid)
{
    // This function returns if a scale is being used by one liveclassroom
    // it it has support for grading and scales. Commented code should be
    // modified if necessary. See forum, glossary or journal modules
    // as reference.
    $return = false; 

    return $return;
} 

/**
 * CALENDAR
 */
function liveclassroom_addCalendarEvent($activity_informations, $instanceNumber, $name)
{ 
    // / Basic event record for the database.
    global $CFG;

    $event = new Object();
    $event->name        = $activity_informations->name;
    $event->description = $activity_informations->description . "<br><a href=" . $CFG->wwwroot . "/mod/liveclassroom/view.php?id=" . $instanceNumber . "&action=launchCalendar target=_self >" . get_string("launch_calendar", "liveclassroom") ." ".$name. " ...</a>";
    $event->format      = 1;
    $event->userid      = 0;
    $event->courseid    = $activity_informations->course; //course event
    $event->groupid     = 0;
    $event->modulename  = 'liveclassroom';
    $event->instance    = $instanceNumber;
    $event->eventtype   = '';
    $event->visible     = 1;
    $event->timemodified = time();
    
    if($activity_informations->course_format !="weeks" && $activity_informations->course_format !="weekscss")
    {//tppics or social
        $event->timestart  = mktime($activity_informations->start_hr,$activity_informations->start_min,0,$activity_informations->start_month,$activity_informations->start_day,$activity_informations->start_year);
    }
    else
    {
        $event->timestart = mktime($activity_informations->start_hr,$activity_informations->start_min,0,date('m',$activity_informations->calendar_start),date('d',$activity_informations->calendar_start),date('Y',$activity_informations->calendar_start));    
    }
    
    $duration = $activity_informations->duration_hr*3600 + $activity_informations->duration_min*60;
    if ($duration < 0)
    {
        $event->timeduration = 0;
    }
    else 
    {
        $event->timeduration = $duration;
    }  
    
    wimba_add_log(WIMBA_DEBUG,WC,"Add calendar event\n".print_r($event, true ));  

    $oldEvent=get_record('event','instance',$activity_informations->id,'modulename','liveclassroom');
    if(!empty($oldEvent) &&  $oldEvent!=false) //old event exsit    exsit 
    {  
        $event->id =  $oldEvent->id  ;
        $result=update_record('event', $event);               
    }
    else
    {
        $result = insert_record('event', $event);    
    }       

    return $result;
} 

function liveclassroom_deleteCalendarEvent($instanceNumber)
{ 
    // / Basic event record for the database.
    global $CFG;
    $oldEvent = get_record('event', 'instance', $instanceNumber,'modulename','liveclassroom');

    if (!empty($oldEvent) && $oldEvent != false) {
        $result = delete_records("event", "id", $oldEvent->id);
    } else {
        return false;
    } 
    return $result;
} 
/**
 * get the calendar event which matches the id
 * 
 * @param  $id - the voicetool instance
 * @return the calendar event or false
 */
function liveclassroom_get_event_calendar($id)
{
    $event = get_record('event', 'instance', $id,'modulename','liveclassroom');
    if ($event === false || empty($event)) {
        return false;
    } 
    return $event;
} 

/*
* Give the shortname for a courseid given
* @param $courseid : the id of the course
* Return a string : the shortname of the course
*/
function liveclassroom_get_course_shortname($courseid)
{
    if (!($course = get_record('course', 'id', $courseid))) 
    {
        // error( "Response get room name: query to database failed");
        return false;
    } 
    // $name = $course->shortname;
    return $course->shortname;
} 

/*
* Delete all the activities on Moodle database for a room given
* @praram $roomid : the id of the room associated to the activities
*  return a boolean true if all is well done
*/
function liveclassroom_delete_all_instance_of_room($roomid)
{
    global $CFG;
    // / Given an ID of an instance of this module,
    // / this function will permanently delete the instance
    // / and any data that depends on it.
    $api = new LCApi($CFG->liveclassroom_servername,
                     $CFG->liveclassroom_adminusername,
                     $CFG->liveclassroom_adminpassword);

    $result = true;
    if ($liveclassrooms = get_records("liveclassroom", "type", $roomid)) 
    {
        $roomname = $api->lcapi_get_room_name($liveclassroom->type); 
        // Delete any dependent records here #
        foreach($liveclassrooms as $liveclassroom) 
        {
            // get the course_module instance linked to the liveclassroom instance
            if (! $cm = get_coursemodule_from_instance("liveclassroom", $liveclassroom->id, $liveclassroom->course))
            {
                error("Course Module ID was incorrect");
            } 
            if (! delete_course_module($cm->id)) 
            {
                wimba_add_log(WIMBA_ERROR,WC,"Problem to delete the course module : ".$cm->id);  
                $result = false; 
                // Delete a course module and any associated data at the course level (events)
                // notify("Could not delete the $cm->id (coursemodule)");
            } 
            if (! delete_records("liveclassroom", "id", "$liveclassroom->id")) 
            {
                wimba_add_log(WIMBA_ERROR,WC,"Problem to delete all the activities associated to the voice tools");  
                $result = false;
            } 
            // delete in the course section too
            if (! delete_mod_from_section($cm->id, "$cm->section")) 
            {
                wimba_add_log(WIMBA_ERROR,WC,"Could not delete the ".$cm->id." from that section : ".$cm->section);
                $result = false; 
                // notify("Could not delete the $mod->modulename from that section");
            } 
        } 
    } 
    
    return $result;
} 

function liveclassroom_getRole($context)
{
    global $CFG;
    global $USER;
    $role = "";

    if (has_capability('mod/liveclassroom:presenter', $context)) {
      $role = 'Instructor';
    } else {
      $role = 'Student';
    }

    return $role;
}

function liveclassroom_get_url_params($courseid)
{
    global $USER;
    global $CFG;

    $context = get_context_instance(CONTEXT_COURSE, $courseid) ;

    $role = liveclassroom_getRole($context);
    $signature = md5($courseid . $USER->email . $USER->firstname . $USER->lastname . $role);
    $url_params = "enc_course_id=" . rawurlencode($courseid) . "&enc_email=" . rawurlencode($USER->email) . "&enc_firstname=" . rawurlencode($USER->firstname) . "&enc_lastname=" . rawurlencode($USER->lastname) . "&enc_role=" . rawurlencode($role) . "&signature=" . rawurlencode($signature);
    return $url_params;
}


/* Management of the reset functionnality */


/**
* Implementation of the function for printing the form elements that control
* whether the course reset functionality affects the chat.
* @param $mform form passed by reference
*/
 function liveclassroom_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'classroomheader', get_string('modulenameplural', 'liveclassroom'));   
    $mform->addElement('checkbox', 'reset_content_liveclassroom_all', "Delete all the archives and content");
    $mform->addElement('checkbox', 'reset_content_liveclassroom_archives', "Delete only the archives");
  
    $mform->disabledIf('reset_content_liveclassroom_all', 'reset_content_liveclassroom_archives', 'checked');
    $mform->disabledIf('reset_content_liveclassroom_archives','reset_content_liveclassroom_all',  'checked');

}
/**
 * For version < 1.9
* Implementation of the function for printing the form elements that control
* whether the course reset functionality affects the chat.
* @param $mform form passed by reference
*/
 function liveclassroom_reset_course_form($course) {
      $activities = get_record("liveclassroom","course",$course->id);
  
    if($activities)
    {
        print_checkbox('reset_content_liveclassroom_all', 1, false, "Delete all the archives and content", '', "if (this.checked) {document.getElementsByName('reset_content_liveclassroom_archive')[0].disabled = 'true'} else {document.getElementsByName('reset_content_liveclassroom_archive')[0].disabled=''}");  echo '<br />';
        print_checkbox('reset_content_liveclassroom_archives',1, false, "Delete only the archives", '', "if (this.checked) {document.getElementsByName('reset_content_liveclassroom_all')[0].disabled = 'true'} else {document.getElementsByName('reset_content_liveclassroom_all')[0].disabled=''}");  echo '<br />';
    }
    else
    {
         echo "There is not Wimba Classroom in this course";  
    }
}
 
/**
* Actual implementation of the rest coures functionality, delete all the
* chat messages for course $data->courseid.
* @param $data the data submitted from the reset course.
* @return array status array
*/
function liveclassroom_delete_userdata($data, $showfeedback=true) {
   global $CFG,$COURSE;

   $componentstr = get_string('modulenameplural', 'liveclassroom');

   if (!empty($data->reset_content_liveclassroom_all)) 
   {
       $api = new LCAction(null,$CFG->liveclassroom_servername,
                        $CFG->liveclassroom_adminusername,
                        $CFG->liveclassroom_adminpassword,$CFG->dataroot); 
       $rooms=$api->getRooms($data->id."_T");
      
       foreach ($rooms as $room)
       {
           if($room->isArchive() == 0)
           { 
                $isAdmin=$api->isStudentAdmin($room->getRoomId(), $data->id."_S");
                $api->cloneRoom($data->id,$room->getRoomId(),"0",$isAdmin,$room->isPreview());
                if($isAdmin == "true")
                {    
                    $api->removeRole($room->getRoomId(), $data->id."_S", "Student");
                    $api->removeRole($room->getRoomId(), $data->id."_T", "ClassAdmin");
                }
                else
                {
                    $api->removeRole($room->getRoomId(), $data->id."_S", "Instructor");
                    $api->removeRole($room->getRoomId(), $data->id."_T", "ClassAdmin");
                }
           }
           else
           {
                $api->deleteRoom($room->getRoomId());
           }
           $activities = get_records("liveclassroom","id",$room->getRoomId()) ;
            foreach (array_keys($activities) as $id) 
            {
                $activities[$id]->rid=new_rid;
                update_record("liveclassroom",$activities[$id]);
                
            }
       }
       $typesstr = "Delete all the archives and content";    
        
   }
   else  if (!empty($data->reset_content_liveclassroom_archives)) 
   {
             $api = new LCAction(null,$CFG->liveclassroom_servername,
                        $CFG->liveclassroom_adminusername,
                        $CFG->liveclassroom_adminpassword,$CFG->dataroot); 
       $rooms=$api->getRooms($data->id."_T");
      
       foreach ($rooms as $room)
       {
           if($room->isArchive() == 1)
           { 
                $api->deleteRoom($room->getRoomId());
           }
       }
       $typesstr = "Delete only the archives";
      
   }
  
   if($showfeedback)
   {
        $strreset = get_string('reset');
        notify($strreset.': '.$typestr, 'notifysuccess');
   }
}


/**
* Actual implementation of the rest coures functionality, delete all the
* chat messages for course $data->courseid.
* @param $data the data submitted from the reset course.
* @return array status array
*/
function liveclassroom_reset_userdata($data,$showfeedback=true) {
   global $CFG,$COURSE;

   $componentstr = get_string('modulenameplural', 'liveclassroom');
   $status = array();
   if (!empty($data->reset_content_liveclassroom_all)) 
   {
       $api = new LCAction(null,$CFG->liveclassroom_servername,
                        $CFG->liveclassroom_adminusername,
                        $CFG->liveclassroom_adminpassword,$CFG->dataroot); 
       $rooms=$api->getRooms($data->id."_T");
      
       foreach ($rooms as $room)
       {
           if($room->isArchive() == 0)
           { 
                $isAdmin=$api->isStudentAdmin($room->getRoomId(), $data->id."_S");
                $api->cloneRoom($data->id,$room->getRoomId(),"0",$isAdmin,$room->isPreview());
                if($isAdmin == "true")
                {    
                    $api->removeRole($room->getRoomId(), $data->id."_S", "Student");
                    $api->removeRole($room->getRoomId(), $data->id."_T", "ClassAdmin");
                }
                else
                {
                    $api->removeRole($room->getRoomId(), $data->id."_S", "Instructor");
                    $api->removeRole($room->getRoomId(), $data->id."_T", "ClassAdmin");
                }
      
           }
           else
           {
                $api->deleteRoom($room->getRoomId());
           }
           $activities = get_records("liveclassroom","id",$room->getRoomId()) ;
            foreach (array_keys($activities) as $id) 
            {
                $activities[$id]->rid=new_rid;
                update_record("liveclassroom",$activities[$id]);
                
            }
       }
       $typesstr = "Delete all the archives and content";    
       $status[] = array('component'=>$componentstr, 'item'=>$typesstr, 'error'=>false);
       
   }
   else  if (!empty($data->reset_content_liveclassroom_archives)) 
   {
             $api = new LCAction(null,$CFG->liveclassroom_servername,
                        $CFG->liveclassroom_adminusername,
                        $CFG->liveclassroom_adminpassword,$CFG->dataroot); 
       $rooms=$api->getRooms($data->id."_T");
      
       foreach ($rooms as $room)
       {
           if($room->isArchive() == 1)
           { 
                $api->deleteRoom($room->getRoomId());
           }
       }
       $typesstr = "Delete only the archives";
       $status[] = array('component'=>$componentstr, 'item'=>$typesstr, 'error'=>false);
   }
   return $status;
  
}
?>
