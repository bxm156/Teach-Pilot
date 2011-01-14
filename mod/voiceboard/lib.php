<?PHP
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
 * Date: 15th April 2006                                                      *                                                                        *
 *                                                                            *
 ******************************************************************************/


/* $Id: lib.php 76186 2009-09-11 11:39:12Z trollinger $ */
/// Library of functions and constants for module voiceboard

if (!function_exists('getKeysOfGeneralParameters')) {
    require_once('lib/php/common/WimbaLib.php');
}
if(!function_exists('voicetools_api_create_resource')){
    require_once('lib/php/common/DatabaseManagement.php');
    require_once('lib/php/vt/WimbaVoicetoolsAPI.php');
    require_once('lib/php/vt/WimbaVoicetools.php');
    require_once('lib/php/vt/VtAction.php');
    
}
if (!function_exists('grade_update') && file_exists($CFG->libdir.'/gradelib.php')) { //workaround for buggy PHP versions
  require_once($CFG->libdir.'/gradelib.php');
}


require_once($CFG->libdir.'/datalib.php');
require_once($CFG->dirroot.'/course/lib.php');


define("voiceboard_MODULE_VERSION", "3.3.0");
define("voiceboard_LOGS", "voiceboard");


/**
* Validate the data in passed in the configuration page
* @param $config - the information from the form mod.html
*/
function voiceboard_process_options ($config) {
   global $CFG;
  
  /*******
    we do the following verfication before submitting the configuration
  	-The parameters sent can not be empty
  	-The url of the server can not finish with a /
  	-The url must start with http:// or https://
  	-The api account has to valid
  ********/
   $config->module="voicetools";
    if(empty($config->servername))
    {
       wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,get_string('wrongconfigurationURLunavailable', 'voiceboard'));
       error(get_string('wrongconfigurationURLunavailable', 'voiceboard'), $_SERVER["HTTP_REFERER"]);
    }
    
    if(empty($config->adminusername))
    {
        wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,get_string('emptyAdminUsername', 'voiceboard'));
        error(get_string('emptyAdminUsername', 'voiceboard'), $_SERVER["HTTP_REFERER"]);
    }  
    
    if(empty($config->adminpassword))
    {
        wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,get_string('emptyAdminPassword', 'voiceboard'));
        error(get_string('emptyAdminPassword', 'voiceboard'), $_SERVER["HTTP_REFERER"]);
    } 
    
    if ($config->servername{strlen($config->servername)-1} == '/')
    {
        wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,get_String('trailingSlash', 'voiceboard'));
        error(get_String('trailingSlash', 'voiceboard'), $_SERVER["HTTP_REFERER"]);
    }
  
    if (!preg_match('/^http:\/\//', $config->servername) && !preg_match('/^https:\/\//', $config->servername)) 
    {
        wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,get_String('trailingHttp', 'voiceboard'));    
        error(get_String('trailingHttp', 'voiceboard'), $_SERVER["HTTP_REFERER"]);
    }  
    
    //check if the api account filled is correct and allowed
    $result = voicetools_api_check_documentbase ($config->servername, $config->adminusername,$config->adminpassword,$CFG->wwwroot);  
    
    if ($result != "ok") 
    {
        if(get_string($result, 'voiceboard' ) == "[[]]")
        {//the error description is not in the bundle
            wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,$result);
            error($result, 'javascript:history.back();'); 
        }  
        else{
            wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,get_String($result, 'voiceboard'));
            error(get_string($result, 'voiceboard' ), 'javascript:history.back();');    
        }
    }
    
    //to make sure that all the necessary module are installed
    wimba_add_log(WIMBA_INFO,voiceboard_LOGS,"php info :\n" .print_r(get_loaded_extensions(),true)); 
    
    wimba_add_log(WIMBA_INFO,voiceboard_LOGS,"The module is well configured");
}

function voiceboard_add_instance($voicetool) {
  /// Given an object containing all the necessary data, 
  /// (defined by the form in mod.html) this function 
  /// will create a new instance and return the id number 
  /// of the new instance.
  global $USER;
 
  //get the resource information(type and id)               
  $voicetool->timemodified = time();  
  $voicetool->rid = $voicetool->resource;     
  $voicetool->name = $voicetool->name;  
 
   if (!$voicetool->id = insert_record('voiceboard', $voicetool)) 
  {
      wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,"Problem to add a new instance");  
      return false;
  }
  
  if(isset($voicetool->calendar_event) &&  $voicetool->calendar_event==true) 
  { 
      voiceboard_addCalendarEvent($voicetool,$voicetool->id); 
  }
  
  
  
  $resource = get_record('voiceboard_resources',"rid",$voicetool->resource);
  if($resource->gradeid != -1){//the grade settings is enabled for this resource
     $activity = get_record('voiceboard',"id",$resource->gradeid);
     if(empty($activity)){//the grade id is not a valid activity id( random number from delete process)
         //now a valid activity is linked to the resource, we have to update the grade.
         
         $students=getStudentsEnrolled($resource->course);
         $users_key = array_keys($students);
         
         $oldgrade = grade_get_grades($voicetool->course, "mod", "voiceboard", $resource->gradeid,$users_key);
         if(isset($oldgrade->items[0]))
         {
           voiceboard_delete_grade_column($resource->rid,$resource->course);
           $resource->gradeid = voiceboard_add_grade_column($resource->rid,$resource->course,$oldgrade->items[0]->name,$oldgrade->items[0]->grademax,voiceboard_build_gradeObject_From_ArrayOfGradeInfoObjects($oldgrade->items[0]->grades));
         
           update_record('voiceboard_resources',$resource);
         }
     }
   
  }
  
 
  wimba_add_log(WIMBA_INFO,voiceboard_LOGS,"Add Instance".$voicetool->id);  
  //for the debug
  wimba_add_log(WIMBA_DEBUG,voiceboard_LOGS,print_r($voicetool, true )); 
  return $voicetool->id;  
}    

function voiceboard_update_instance($voicetool) {
  /// Given an object containing all the necessary data, 
  /// (defined by the form in mod.html) this function 
  /// will update an existing instance with new data.
    global $USER;

    //get the course_module instance linked to the liveclassroom instance
    if (! $cm = get_coursemodule_from_instance("voiceboard", $voicetool->instance, $voicetool->course)) 
    {
        wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,"Problem to update the instance : ".$voicetool->instance); 
        error("Course Module ID was incorrect");
    }      
    
    if($voicetool->section != $cm->section)//the scetion has changed
    {
        //Find the right section in the course_section
        if (!$section = get_record("course_sections", "id", $cm->section))
        {
            return false;
        }
        //delete in the course section
        if (! delete_mod_from_section($cm->id, $cm->section)) 
        {
            return false;
        }
        
        //update the course module section
        if (! $sectionid = add_mod_to_section($voicetool) ) 
        {
            wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,"Problem to update the instance : ".$voicetool->instance); 
            error("Could not add the new course module to that section");
        }
        //update the course modules  
        if (! set_field("course_modules", "section", $sectionid, "id", $cm->id)) 
        {
            wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,"Problem to update the instance : ".$voicetool->instance); 
            error("Could not update the course module with the correct section");
        }
    }
  
    $voicetool->timemodified = time();  
    $voicetool->id = $voicetool->instance;
    $voicetool->rid = $voicetool->resource;     
    $voicetool->name = $voicetool->name;  
    
    if (!$voicetool->id = update_record('voiceboard', $voicetool)) 
    {
      return false;
    }
    
    if(isset($voicetool->calendar_event) && $voicetool->calendar_event) 
    {//no problem
        voiceboard_addCalendarEvent($voicetool,$voicetool->instance); 
    } 
    else 
    {
        voiceboard_deleteCalendarEvent($voicetool->instance );          
    }
    
    wimba_add_log(WIMBA_INFO,voiceboard_LOGS,"Update of the instance : ".$voicetool->id); 
    return $voicetool->id ;
}


function voiceboard_delete_instance($id) {
    /// Given an ID of an instance of this module, 
    /// this function will permanently delete the instance 
    /// and any data that depends on it.  
    $result = true;  
    if (! $voicetool = get_record("voiceboard", "id", $id)) 
    {
        return false;
    }
  
    # Delete any dependent records here #
    if (! $instanceNumber=delete_records("voiceboard", "id", $voicetool->id)) 
    {
        wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,"Problem to delete the instance : ".$voicetool->id); 
        $result = false;
    } 

    if("voiceboard" == "voiceboard"){
      $resource = get_record("voiceboard_resources", "rid", $voicetool->rid);
      if($resource->gradeid !=  -1){
         
        $students  = getStudentsEnrolled( $voicetool->course);
        $users_key = array_keys($students);
         
        $oldgrade = grade_get_grades($voicetool->course, "mod", "voiceboard", $resource->gradeid,$users_key);
        if(isset($oldgrade->items[0])){
          //the activity linked to the grade is no longer available
          //If there is still an activity linked to the resource, we will use this one,
          //else we will use an random numer as instance number(the title of the column will not be a link)
          voiceboard_delete_grade_column($resource->rid,$voicetool->course);
          $resource->gradeid = voiceboard_add_grade_column($resource->rid,$voicetool->course,$oldgrade->items[0]->name,$oldgrade->items[0]->grademax,voiceboard_build_gradeObject_From_ArrayOfGradeInfoObjects($oldgrade->items[0]->grades));
        }
        update_record('voiceboard_resources', $resource);
      }
    }
    voiceboard_deleteCalendarEvent($voicetool->id);
    // delete the related calendar event
       
    return $result;
}
  

function voiceboard_addCalendarEvent($activity_informations,$instanceNumber){
    global $CFG;  

    //get some complementary of the resource       
    $resource=get_record('voiceboard_resources','rid',$activity_informations->rid);
    
    $event = new Object();
    $event->name         = $activity_informations->name;
    $event->description  = $activity_informations->description."<br><a href=".$CFG->wwwroot."/mod/voiceboard/view.php?id=".$activity_informations->id."&action=launchCalendar target=_self >".get_string("launch_calendar","voiceboard").get_string("board","voiceboard")." ...</a>";   
    $event->format       = 1;           
    $event->userid       = 0;    
    $event->courseid     = $activity_informations->course;  //course event
    $event->groupid      = 0;
    $event->modulename   = 'voiceboard';
    $event->instance     = $instanceNumber;
    $event->eventtype    = '';
    $event->visible      = 1;
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
    
    wimba_add_log(WIMBA_DEBUG,voiceboard_LOGS,"Add calendar event\n".print_r($event, true ));  
    
    $oldEvent=get_record('event','instance',$instanceNumber,'modulename',"voiceboard");
    if(!empty($oldEvent) &&  $oldEvent!=false) //old event exsit    
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


function voiceboard_deleteCalendarEvent($instanceNumber){
  /// Basic event record for the database.
    global $CFG;        
    
    if(!$event=get_record('event','instance',$instanceNumber,'modulename',"voiceboard"))
    {
        wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,"Problem to delete calendar event : ".$instanceNumber); 
        return false;
    }
    $result=delete_records("event", "id", $event->id);               
}  
  

function voiceboard_delete_resource($rid) {
    /// Given an ID of an instance of this module, 
    /// this function will permanently delete the instance 
    /// and any data that depends on it.  
    
    if (! $voicetool = get_record("voiceboard_resources", "rid", $rid)) {
        wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,"Problem to delete a resource : ".$rid); 
        return false;
    }
    # Delete any dependent records here #
    if (! delete_records("voiceboard_resources", "id", "$voicetool->id")) {
        wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,"Problem to delete a resource : ".$rid); 
        return false;
    } 
       
    return true;
}

function voiceboard_user_outline($course, $user, $mod, $voicetool) {
  /// Return a small object with summary information about what a 
  /// user has done with a given particular instance of this module
  /// Used for user activity reports.
  /// $return->time = the time they did it
  /// $return->info = a short text description
    return $return;
}

function voiceboard_user_complete($course, $user, $mod, $voicetool) {
  /// Print a detailed representation of what a  user has done with 
  /// a given particular instance of this module, for user activity reports.

  return true;
}

function voiceboard_print_recent_activity($course, $isteacher, $timestart) {
  /// Given a course and a time, this module should find recent activity 
  /// that has occurred in voicetool activities and print it out. 
  /// Return true if there was output, or false is there was none.

  global $CFG;

  return false;  //  True if anything was printed, otherwise false 
}

function voiceboard_cron () {
  /// Function to be run periodically according to the moodle cron
  /// This function searches for things that need to be done, such 
  /// as sending out mail, toggling flags etc ... 

  global $CFG;

  return true;
}

function voiceboard_grades($voicetoolid) {
  /// Must return an array of grades for a given instance of this module, 
  /// indexed by user.  It also returns a maximum allowed grade.
  ///
  ///    $return->grades = array of grades;
  ///    $return->maxgrade = maximum allowed grade;
  ///
  ///    return $return;

  return NULL;
}

function voiceboard_get_participants($voicetoolid) {
  //Must return an array of user records (all data) who are participants
  //for a given instance of voicetool. Must include every user involved
  //in the instance, independient of his role (student, teacher, admin...)
  //See other modules as example.

  return false;
}

function voiceboard_scale_used ($voicetoolid,$scaleid) {
  //This function returns if a scale is being used by one voicetool
  //it it has support for grading and scales. Commented code should be
  //modified if necessary. See forum, glossary or journal modules
  //as reference.

  $return = false;

  //$rec = get_record("voicetool","id","$voicetoolid","scale","-$scaleid");
  //
  //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}

    return $return;
}


function voiceboard_store_new_element($voicetool) {

  $id=insert_record("voiceboard_resources", $voicetool);  

  return $id;
} 

function voiceboard_update_element($voicetool) {
  $oldId = get_record('voiceboard_resources','rid',$voicetool->rid);
 
  $voicetool->id = $oldId->id;  
  $id=update_record("voiceboard_resources", $voicetool);  

  return $id;      
}


/*
* Delete all the activities on Moodle database for a vt given
* @praram $roomid : the id of the room associated to the activities
*  return a boolean true if all is well done
*/
function voiceboard_delete_all_instance_of_resource($id) {
/// Given an ID of an instance of this module, 
/// this function will permanently delete the instance 
/// and any data that depends on it.  

    //delete the resource of the vt list
	
    $result = true;
   	wimba_add_log(WIMBA_INFO,voiceboard_LOGS,"Delete the resouce ". $id);
    if ($voiceboard = get_records("voiceboard", "rid", $id)) 
    {
      # Delete any dependent records here #
	    foreach($voiceboard as $voicetool){       
		    //get the course_module instance linked to the liveclassroom instance
		    $cm=get_coursemodule_from_instance("voiceboard", $voicetool->id, $voicetool->course);
            
		    if(!empty($cm)) //old event exsit    
		    {
    		    if (! delete_course_module($cm->id)) 
    		    {
    		         wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,"Problem to delete the course module : ".$cm->id);  
    		         $result = false;
    		         //Delete a course module and any associated data at the course level (events)
    		    } 
    		    //delete the instance
    		    if (! delete_records("voiceboard", "id", $voicetool->id)) 
    		    {
                    wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,"Problem to delete all the activities associated to the voice tools");  
                    $result = false;
    		    } 
    			//delete in the course section too
    			if (! delete_mod_from_section($cm->id, $cm->section)) 
    			{
    				wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,"Could not delete the ".$cm->id." from that section : ".$cm->section);
                    $result = false;
    			}
    			voiceboard_deleteCalendarEvent($voicetool->id);
		    }
	    }
  	}
    voiceboard_delete_resource($id);
    return $result;
}



/**
* List all the resource for the course given
* @param $courseId - the current course id
* @return the  of the boards rid 
*         false if there is no resources
*         null if problem    
*/
function voiceboard_get_voicetools_list($courseId) {
    $tools_list = get_records('voiceboard_resources','course',$courseId);
    $result= array();
    $result["rid"]= array();
    $result["info"]= array();
    
    if(empty($tools_list))
    {
        wimba_add_log(WIMBA_INFO,voiceboard_LOGS,"No resources have been created yet");  
    	return $result;  
    }
    else if($tools_list === false) 
    {
        wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,"Problem to get the list of resources");        
        error( "Response get_board_list : query to database failed"); 
        return false;
    }   
    $result = array();    
    foreach($tools_list as $tool) {
        //if($tool->type!="recorder")
        //{
            $result["rid"][]= $tool->rid;   
            $result["info"][$tool->rid]= $tool;              
       // } 
    }
    wimba_add_log(WIMBA_DEBUG,voiceboard_LOGS,"list of resource :\n".print_r($result, true ));      
    
    return $result;
}                       

/**
* List all the resource for the course given
* @param $courseId - the current course id
* @return the  of the boards rid 
*/
function voiceboard_get_voicetool_informations($rid) {

    $tool = get_record('voiceboard_resources','rid',$rid);
    
    if(empty($tool))
    {
        wimba_add_log(WIMBA_INFO,voiceboard_LOGS,"No resources have been created yet"); 
        return null;
    }
    else if($tool === false) 
    {
        wimba_add_log(WIMBA_ERROR,voiceboard_LOGS,"Problem to get the list of resources");      
        error( "Response get_board_list : query to database failed"); 
        return "error_moodleDatabase";
    }   
    wimba_add_log(WIMBA_DEBUG,voiceboard_LOGS,"list of resource :\n".print_r($tool, true ));      
           
    return $tool;
} 



/**
* List all the informnations(availability,start_date ..)of the board for the rid given
* @param $rid - the current course rid  of the board
* @return the list of informations 
*/
function voiceboard_get_wimbaVoice_Informations($rid) {

    if(!($board_informations = get_record('voiceboard_resources','rid',$rid))) 
    {
        error( "Response get_board_list : query to database failed"); 
        return "error_moodleDatabase";
    }

  return $board_informations;
}




/**
* get the calendar event which matches the id
* @param $id - the voicetool instance 
* @return the calendar event or false 
*/
function voiceboard_get_event_calendar($id) {

  $event = get_record('event', 'instance', $id,'modulename','voiceboard');
  if($event === false || empty($event)) {
  
    return false;
  }
  return $event;
}



function voiceboard_get_version() {
    $answer = voicetools_api_get_version();
    
    if (!strcmp($answer, "error")) 
    {
        return get_string('voiceboard', "error");
    }
    elseif (!strcmp($answer, 'unknown')) 
    {
        return get_string('voiceboard', 'unknown');
    }
    
    return $answer;
}

function voiceboard_getRole($context)
{
    global $CFG;
    global $USER;
    $role = "";

    if (has_capability('mod/voiceboard:presenter', $context)) {
      $role = 'Instructor';
    } else {
      $role = 'Student';
    }

    return $role;
}

function voiceboard_get_url_params($courseid)
{
    global $USER;
    global $CFG;

    $context = get_context_instance(CONTEXT_COURSE, $courseid) ;

    $role = voiceboard_getRole($context);
    $signature = md5($courseid . $USER->email . $USER->firstname . $USER->lastname . $role);
    
    $url_params = "enc_course_id=" . rawurlencode($courseid) . 
                  "&enc_email=" . rawurlencode($USER->email) . 
                  "&enc_firstname=" . rawurlencode($USER->firstname) . 
                  "&enc_lastname=" . rawurlencode($USER->lastname) . 
                  "&enc_role=" . rawurlencode($role) . 
                  "&signature=" . rawurlencode($signature);
    return $url_params;
}


/**
* Implementation of the function for printing the form elements that control
* whether the course reset functionality affects the chat.
* @param $mform form passed by reference
*/
 function voiceboard_reset_course_form_definition(&$mform) {
     $currentProduct="voiceboard";
     $mform->addElement('header', 'classroomheader', get_string('modulenameplural', 'voiceboard'));   
  
    
    if($currentProduct != "voicepodcaster"){
          $mform->addElement('checkbox',
                             'reset_content_voiceboard_replies',
                              get_string("voiceboard_reset_only_replies", 'voiceboard'));
        
    }
    $mform->addElement('checkbox', 
                        'reset_content_voiceboard', 
                        get_string("voiceboard_reset_all", 'voiceboard'));
    
    $mform->disabledIf('reset_content_voiceboard', 'reset_content_voiceboard_replies', 'checked');
    $mform->disabledIf('reset_content_voiceboard_replies','reset_content_voiceboard',  'checked');
                      
}

/**
* Implementation of the function for printing the form elements that control
* whether the course reset functionality affects the chat.
* @param $mform form passed by reference
*/
 function voiceboard_reset_course_form($course) {
     $currentProduct="voiceboard";

    $activities = get_record("voiceboard","course",$course->id);
  
    if($activities){
        if($currentProduct != "voicepodcaster"){
           
             print_checkbox('reset_content_voiceboard_replies', 1, false, get_string("voiceboard_reset_only_replies", 'voiceboard'), '', "if (this.checked) {document.getElementsByName('reset_content_voiceboard')[0].disabled = 'true'} else {document.getElementsByName('reset_content_voiceboard')[0].disabled=''}");  echo '<br />';
        
        }
       
        print_checkbox('reset_content_voiceboard',1, false, get_string("voiceboard_reset_all", 'voiceboard'), '', "if (this.checked ) {document.getElementsByName('reset_content_voiceboard_replies')[0].disabled = 'true'} else {document.getElementsByName('reset_content_voiceboard_replies')[0].disabled=''}");  echo '<br />';
    }
    else
    {
        echo "There is not Voice Board in this course";    
    }
}

/**
* Actual implementation of the rest coures functionality, delete all the
* chat messages for course $data->courseid.
* @param $data the data submitted from the reset course.
* @return array status array
*/
function voiceboard_reset_userdata($data) {
   global $CFG;

   $componentstr = get_string('modulenameplural', 'voiceboard');
   $status = array();

   if (!empty($data->reset_content_voiceboard_replies)) {
        $resources = get_records("voiceboard_resources","course",$data->id) ;
        
        foreach (array_keys($resources) as $id) 
        {
            $rid=$resources[$id]->rid;
            
            echo grade_get_grades($resources[$id]->course, "mod", "voiceboard", $resources[$id]->gradeid); 
            $activities=get_records("voiceboard","rid",$rid);
            $newResource = voicetools_api_copy_resource($rid,"",1);
            //delete the old one and update the stored record
            //voicetools_api_delete_resource($rid);
            $resources[$id]->rid=$newResource->getRid();
            update_record("voiceboard_resources",$resources[$id]);
            //need to update the rid linked to the activity
            foreach (array_keys($activities) as $activity_id) 
            {
                $activities[$activity_id]->rid=$newResource->getRid();
                update_record("voiceboard",$activities[$activity_id]);    
            }
        }
        $typestr = get_string("voiceboard_reset_only_replies", 'voiceboard');
        $status[] = array('component'=>$componentstr, 'item'=>$typestr, 'error'=>false);
   }
   
   if (!empty($data->reset_content_voiceboard)) {
        $resources = get_records("voiceboard_resources","course",$data->id) ;
        
        foreach (array_keys($resources) as $id) 
        {
            $rid=$resources[$id]->rid;
            $activities=get_records("voiceboard","rid",$rid);
            $newResource = voicetools_api_copy_resource($rid,"",2);
            //delete the old one and update the stored record
         //   voicetools_api_delete_resource($rid);
            $resources[$id]->rid=$newResource->getRid();
            update_record("voiceboard_resources",$resources[$id]);
            foreach (array_keys($activities) as $activity_id) 
            {
                $activities[$activity_id]->rid=$newResource->getRid();
                update_record("voiceboard",$activities[$activity_id]);
                
            }
        }
        $typestr = get_string("voiceboard_reset_all", 'voiceboard');
        $status[] = array('component'=>$componentstr, 'item'=>$typestr, 'error'=>false);
   }
   
   if (!empty($data->reset_gradebook_items)) { 
      $resources = get_records("voiceboard_resources","course",$data->id) ;
        
      foreach (array_keys($resources) as $id) 
      {
        if($resources[$id]->gradeid != "-1"){
          $resources[$id]->gradeid = "-1";
        
          update_record("voiceboard_resources",$resources[$id]);
          $resource = voicetools_api_get_resource($resources[$id]->rid);
          $options = $resource->getOptions();
          $options->setPointsPossible("");
          $options->setGrade(false);
          $resource->setOptions($options);
          voicetools_api_modify_resource($resource->getResource());
        }
      }
    
   }
     
   return $status;
}
  
/**
* Actual implementation of the rest coures functionality, delete all the
* chat messages for course $data->courseid.
* @param $data the data submitted from the reset course.
* @return array status array
*/
function voiceboard_delete_userdata($data,$showfeedback=true) {
   global $CFG;

   $componentstr = get_string('modulenameplural', 'voiceboard');

   if (!empty($data->reset_content_voiceboard_replies)) {
        $resources = get_records("voiceboard_resources","course",$data->id) ;
        
        foreach (array_keys($resources) as $id) 
        {
            $rid=$resources[$id]->rid;
            $activities=get_records("voiceboard","rid",$rid);
            $newResource = voicetools_api_copy_resource($rid,"",1);
            //delete the old one and update the stored record
            //voicetools_api_delete_resource($rid);
            $resources[$id]->rid=$newResource->getRid();
            update_record("voiceboard_resources",$resources[$id]);
            //need to update the rid linked to the activity
            foreach (array_keys($activities) as $activity_id) 
            {
                $activities[$activity_id]->rid=$newResource->getRid();
                update_record("voiceboard",$activities[$activity_id]);    
            }
        }
        $typestr = get_string("voiceboard_reset_only_replies", 'voiceboard');
        if($showfeedback)
        {
            $strreset = get_string('reset');
            notify($strreset.': '.$typestr, 'notifysuccess');
        }   
   }
   
   if (!empty($data->reset_content_voiceboard)) {
        $resources = get_records("voiceboard_resources","course",$data->id) ;
        
        foreach (array_keys($resources) as $id) 
        {
            $rid=$resources[$id]->rid;
            $activities=get_records("voiceboard","rid",$rid);
            $newResource = voicetools_api_copy_resource($rid,"",2);
            //delete the old one and update the stored record
            //voicetools_api_delete_resource($rid);
            $resources[$id]->rid=$newResource->getRid();
            update_record("voiceboard_resources",$resources[$id]);
            foreach (array_keys($activities) as $activity_id) 
            {
                $activities[$activity_id]->rid=$newResource->getRid();
                update_record("voiceboard",$activities[$activity_id]);
            }
        }
        $typestr = get_string("voiceboard_reset_all", 'voiceboard');    
        
        if($showfeedback)
        {
            $strreset = get_string('reset');
            notify($strreset.': '.$typestr, 'notifysuccess');
        }
   }
   //the gradebook is completely reset, we have to manage that on our side. The grade setting of the resource has to be updated
   if (!empty($data->reset_gradebook_items)) { 
      $resources = get_records("voiceboard_resources","course",$data->id) ;
        
      foreach (array_keys($resources) as $id) 
      {
        if($resources[$id]->gradeid != "-1"){
          $resources[$id]->gradeid = "-1";
        
          update_record("voiceboard_resources",$resources[$id]);
          $resource = voicetools_api_get_resource($resources[$id]->rid);
          $options = $resource->getOptions();
          $options->setPointsPossible("");
          $options->setGrade(false);
          $resource->setOptions($options);
          voicetools_api_modify_resource($resource->getResource());
        }
      }
     
   }  

   
}

function voiceboard_createResourceFromResource($rid,$new_rid,$new_course,$options="0")
{
    
    $voicetools = get_record("voiceboard_resources","rid",$rid);
    $voicetools->id = null;
    $voicetools->rid = $new_rid;
    $voicetools->course = $new_course;
    $voicetools->fromrid = $rid;
    $voicetools->copyOptions = $options;
     
    return voiceboard_store_new_element($voicetools);
} 


/*
 * This function add a column to the gradebook
 * return the activity id which will be used to do the link between this column and the vb resource
 */
function voiceboard_add_grade_column($rid,$course_id,$resource_name,$points_possible,$grades=null)
{        
  //check if the resource is already linked to an activity
  $activities = get_records("voiceboard","rid",$rid);
  if(!empty($activities)){
    $keys=array_keys($activities);
    $activity_id = $keys[0];//take the first one
  }else{
    srand ((double) microtime( )*1000000);
    $activity_id = rand(1000000,9000000);//we generate a big random number 
  }

  if($grades == null)
  {//check if there are some grades associated to the column
    $students=getStudentsEnrolled($course_id);
    $users_key = array_keys($students);
    $gradesData = grade_get_grades($course_id, "mod", "voiceboard", $activity_id,$users_key);
    if(isset($gradesData->items[0]))
    {
      $grades = voiceboard_build_gradeObject_From_ArrayOfGradeInfoObjects($gradesData->items[0]->grades);
    }   
  } 
  $grade_params = array('itemname'=>$resource_name,'grademax'=>$points_possible);//we will use this column to get the grade item associated to the resource.
  
  grade_update("mod/voiceboard", $course_id, "mod", "voiceboard", $activity_id, 0, $grades, $grade_params); 
  return $activity_id;
}

/*
 * This function add a grades to a specific column of the gradebook
 */
function voiceboard_add_grades($rid, $course_id, $grades){
    //check if the resource is already linked to an activity
    $voicetools = get_record("voiceboard_resources","rid",$rid);
  
    grade_update("mod/voiceboard", $course_id, "mod", "voiceboard", $voicetools->gradeid, 0, voiceboard_build_gradeObject($grades)); 
  
    
}

/*
 * This function build the object that we have to pass to the grade_update function.
 * The entry param is the data that we get from the form
 */
function voiceboard_build_gradeObject($grades){

      $gradesArray = array();
	  foreach($grades as $k=>$g) {
	  	$gradeObj = new object();
	  	$gradeObj->rawgrade=$g;
	  	$gradeObj->userid=$k;
		$gradesArray[$k]=$gradeObj;
	  }	
	  return $gradesArray;
}

/*
 * This function build the object that we have to pass to the grade_update function.
 * The entry param is the result of the function grade_get_grades which is an array of Grade Info Objects 
 */

function voiceboard_build_gradeObject_From_ArrayOfGradeInfoObjects($grades){

      $gradesArray = array();
	  foreach($grades as $k=>$g) {
	  	$gradeObj = new object();
	  	$gradeObj->rawgrade=$g->grade;
	  	$gradeObj->userid=$k;
		$gradesArray[$k]=$gradeObj;
	  }	
	  return $gradesArray;
}

/*
 * THis function delete the grade column associated to the resource.
 * We can pass directly the activity id if we know it(restore process for example)
 */
function voiceboard_delete_grade_column($rid,$course_id,$activity_id=null){
    $voicetools = get_record("voiceboard_resources","rid",$rid);
    if(!isset($activity_id)){
      $activity_id = $voicetools->gradeid;//store the activity id to be able to delete the grade column
    }
    $voicetools->gradeid = "-1";
    update_record("voiceboard_resources",$voicetools);
    
    grade_update("mod/voiceboard", $course_id, "mod", "voiceboard", $activity_id, 0, null, array("deleted"=>"1")); 
    
}

?>
