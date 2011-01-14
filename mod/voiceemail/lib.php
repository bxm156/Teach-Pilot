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


/* $Id: lib.php 64437 2008-06-16 15:03:21Z thomasr $ */
require_once($CFG->libdir.'/datalib.php');
require_once($CFG->dirroot.'/course/lib.php');

if (!function_exists('getKeysOfGeneralParameters')) {
    require_once('lib/php/common/WimbaLib.php');
}
if(!function_exists('voicetools_api_create_resource')){
    require_once('lib/php/common/DatabaseManagement.php');
    require_once('lib/php/vt/WimbaVoicetoolsAPI.php');
    require_once('lib/php/vt/WimbaVoicetools.php');
    require_once('lib/php/vt/VtAction.php');
    
}

define("VOICEEMAIL_MODULE_VERSION", "3.3.0");
define("voiceemail_LOGS", "voiceemail");

/**
* Validate the data in passed in the configuration page
* @param $config - the information from the form mod.html
*/
function voiceemail_process_options ($config) {
   global $CFG;
  
}

function voiceemail_add_instance($voicetool) {
   
  /// Given an object containing all the necessary data, 
  /// (defined by the form in mod.html) this function 
  /// will create a new instance and return the id number 
  /// of the new instance.
    global $USER;
    //get the resource information(type and id) 
    $voicetool->timemodified = time();  
    //Create the Voice E-mail linked to this actvity
    $paramVMail=array();
    $paramVMail["name"]=$voicetool->name;
    $paramVMail["audio_format"]=$voicetool->audio_format;
    $paramVMail["max_length"]=$voicetool->max_length;
    $paramVMail["reply_link"]=$voicetool->reply_link;
    $paramVMail["subject"]="";
    if(isset($voicetool->subject))
    {
      $paramVMail["subject"]=$voicetool->subject;
    }
    $paramVMail["recipients"]=voiceemail_getEnrolledUsers($voicetool->course,$voicetool->recipients_email);
    $vtAction=new VtAction($USER->email,$paramVMail);
    
    $vt=$vtAction->createVMmail($voicetool->name);
    if($vt === false || $vt->error == "error"){
        error(get_string('problem_vt','voiceemail'));
        return false;
    }
    $resource_id= storeVmailResource($vt->getRid(),$voicetool->course);//default availability
    if(empty($resource_id))
    {
        return false;  
    }
    $voicetool->rid = $resource_id;   
    
    if (!$voicetool->id = insert_record('voiceemail', $voicetool)) 
    {
      wimba_add_log(WIMBA_ERROR,voiceemail_LOGS,"Problem to add a new instance");  
      return false;
    }
    
    if(isset($voicetool->calendar_event) &&  $voicetool->calendar_event==true) 
    { 
      voiceemail_addCalendarEvent($voicetool,$voicetool->id); 
    }
    
    wimba_add_log(WIMBA_INFO,voiceemail_LOGS,"Add Instance".$voicetool->id);  
    //for the debug
    wimba_add_log(WIMBA_DEBUG,voiceemail_LOGS,print_r($voicetool, true )); 
    return $voicetool->id;  
}    

function voiceemail_update_blocks_instance($voicetool) {
   
    $config=get_record('voiceemail_block','block_id', $voicetool->block_id);
    $voicetool->id = $config->id;
    if(!empty($config) &&  $config!=false) //old event exsit    
    { 
        if( !update_record('voiceemail_block', $voicetool))
        {
            wimba_add_log(WIMBA_ERROR,voiceemail_LOGS,"Problem to update a new instance");  
            return false;
        }
    }
    else 
    {
        if( !insert_record('voiceemail_block', $voicetool))
        {
            wimba_add_log(WIMBA_ERROR,voiceemail_LOGS,"Problem to add a new instance");  
            return false;
        }
    }
    
  
} 


function voiceemail_update_instance($voicetool) {
  /// Given an object containing all the necessary data, 
  /// (defined by the form in mod.html) this function 
  /// will update an existing instance with new data.
    global $USER;

    //get the course_module instance linked to the liveclassroom instance
    if (! $cm = get_coursemodule_from_instance("voiceemail", $voicetool->instance, $voicetool->course)) 
    {
        wimba_add_log(WIMBA_ERROR,voiceemail_LOGS,"Problem to update the instance : ".$voicetool->instance); 
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
            wimba_add_log(WIMBA_ERROR,voiceemail_LOGS,"Problem to update the instance : ".$voicetool->instance); 
            error("Could not add the new course module to that section");
        }
        //update the course modules  
        if (! set_field("course_modules", "section", $sectionid, "id", $cm->id)) 
        {
            wimba_add_log(WIMBA_ERROR,voiceemail_LOGS,"Problem to update the instance : ".$voicetool->instance); 
            error("Could not update the course module with the correct section");
        }
    }
  
     $voicetool->timemodified = time();  
    //Create the Voice E-mail linked to this actvity
    
    $paramVMail=array();
    $paramVMail["name"]=$voicetool->name;
    $paramVMail["audio_format"]=$voicetool->audio_format;
    $paramVMail["max_length"]=$voicetool->max_length;
    $paramVMail["reply_link"]=$voicetool->reply_link;
    $paramVMail["subject"]=$voicetool->subject;
    $paramVMail["recipients"]=voiceemail_getEnrolledUsers($voicetool->course,$voicetool->recipients_email);
    $vtAction=new VtAction($USER->email,$paramVMail);
   
    $vt=$vtAction->updateVMmail($voicetool->rid,$voicetool->name);
    if($vt->error == "error"){
        return false;
    }
    
    $resource_id= updateVmailResource($vt->getRid(),$voicetool->course);//default availability
 
    if(empty($resource_id))
    {
        return false;  
    }
    $voicetool->rid = $resource_id;  
    $voicetool->id = $voicetool->instance;
    if (!$voicetool->id = update_record('voiceemail', $voicetool)) 
    {
      return false;
    }
    
    if(isset($voicetool->calendar_event) && $voicetool->calendar_event) 
    {//no problem
        voiceemail_addCalendarEvent($voicetool,$voicetool->instance); 
    } 
    else 
    {
        voiceemail_deleteCalendarEvent($voicetool->instance );          
    }
    
    wimba_add_log(WIMBA_INFO,voiceemail_LOGS,"Update of the instance : ".$voicetool->id); 
    return $voicetool->id ;
}
    



function voiceemail_delete_instance($id) {
    /// Given an ID of an instance of this module, 
    /// this function will permanently delete the instance 
    /// and any data that depends on it.  
    $result = true;  
    if (! $voicetool = get_record("voiceemail", "id", $id)) 
    {
        return false;
    }
    # Delete any dependent records here #
    if (! $instanceNumber=delete_records("voiceemail", "id", $voicetool->id)) 
    {
        wimba_add_log(WIMBA_ERROR,voiceemail_LOGS,"Problem to delete the instance : ".$voicetool->id); 
        $result = false;
    } 
    
    voiceemail_deleteCalendarEvent($voicetool->id);
    // delete the related calendar event
       
    return $result;  
}
  

function voiceemail_user_outline($course, $user, $mod, $voicetool) {
  /// Return a small object with summary information about what a 
  /// user has done with a given particular instance of this module
  /// Used for user activity reports.
  /// $return->time = the time they did it
  /// $return->info = a short text description
    return $return;
}

function voiceemail_user_complete($course, $user, $mod, $voicetool) {
  /// Print a detailed representation of what a  user has done with 
  /// a given particular instance of this module, for user activity reports.

  return true;
}

function voiceemail_print_recent_activity($course, $isteacher, $timestart) {
  /// Given a course and a time, this module should find recent activity 
  /// that has occurred in voicetool activities and print it out. 
  /// Return true if there was output, or false is there was none.

  global $CFG;

  return false;  //  True if anything was printed, otherwise false 
}

function voiceemail_cron () {
  /// Function to be run periodically according to the moodle cron
  /// This function searches for things that need to be done, such 
  /// as sending out mail, toggling flags etc ... 

  global $CFG;

  return true;
}

function voiceemail_grades($voicetoolid) {
  /// Must return an array of grades for a given instance of this module, 
  /// indexed by user.  It also returns a maximum allowed grade.
  ///
  ///    $return->grades = array of grades;
  ///    $return->maxgrade = maximum allowed grade;
  ///
  ///    return $return;

  return NULL;
}

function voiceemail_get_participants($voicetoolid) {
  //Must return an array of user records (all data) who are participants
  //for a given instance of voicetool. Must include every user involved
  //in the instance, independient of his role (student, teacher, admin...)
  //See other modules as example.

  return false;
}

function voiceemail_scale_used ($voicetoolid,$scaleid) {
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

/**
* List all the informnations(availability,start_date ..)of the board for the rid given
* @param $rid - the current course rid  of the board
* @return the list of informations 
*/
function voiceemail_get_voicetool_informations($rid) {

    if(!($informations = get_record('voiceemail_resources','rid',$rid))) 
    {
        error( "Response voiceemail_get_Informations : query to database failed"); 
        return "error_moodleDatabase";
    }

  return $informations;
}


function voiceemail_getEnrolledUsers ($courseId,$typeRecipients) {

    $instuctors="";
    $students="";
    $context =  get_context_instance(CONTEXT_COURSE, $courseId);
    $allusers=get_users_by_capability($context,'moodle/course:view');
    //$allusers also contain the users which have this capabilities at the system level
    $users_key = array_keys($allusers);

  
    for($i=0;$i<count($users_key);$i++)
    {
    	$roleInCourse = get_user_roles_in_context($allusers[$users_key[$i]]->id,$context);
        if(!empty($roleInCourse))
        {//A role is assigned to this user in the course context, this user has to be displayed
            if (has_capability('mod/voiceemail:presenter', $context, $allusers[$users_key[$i]]->id)) 
            {
              $instuctors .= $allusers[$users_key[$i]]->email .";";
            } 
            else 
            {
              $students .= $allusers[$users_key[$i]]->email .";";
            }
    	}
    }
     
    switch($typeRecipients){
        case "students":
            return substr($students,0,strlen($students)-1);
            break;
        case "instructors":
            return substr($instuctors,0,strlen($instuctors)-1);
            break;
        case "all":
            return substr($instuctors.$students,0,strlen($instuctors.$students)-1);
            break;        
    }

    return null;
}

function voiceemail_get_version() {
    $answer = voicetools_api_get_version();
    
    if (!strcmp($answer, "error")) 
    {
        return get_string('voiceemail', "error");
    }
    elseif (!strcmp($answer, 'unknown')) 
    {
        return get_string('voiceemail', 'unknown');
    }
    
    return $answer;
}

function voiceemail_getRole($context)
{
    global $CFG;
    global $USER;
    $role = "";

    if (has_capability('mod/voiceemail:presenter', $context)) {
      $role = 'Instructor';
    } else {
      $role = 'Student';
    }

    return $role;
}

function voiceemail_get_url_params($courseid)
{
    global $USER;
    global $CFG;

    $context = get_context_instance(CONTEXT_COURSE, $courseid) ;

    $role =voiceemail_getRole($context);
    $signature = md5($courseid . $USER->email . $USER->firstname . $USER->lastname . $role);
    
    $url_params = "enc_course_id=" . rawurlencode($courseid) . 
                  "&enc_email=" . rawurlencode($USER->email) . 
                  "&enc_firstname=" . rawurlencode($USER->firstname) . 
                  "&enc_lastname=" . rawurlencode($USER->lastname) . 
                  "&enc_role=" . rawurlencode($role) . 
                  "&signature=" . rawurlencode($signature);
    return $url_params;
}

function voiceemail_addCalendarEvent($activity_informations,$instanceNumber){
    global $CFG;  

    //get some complementary of the resource       
    $resource=get_record('voiceemail_resources','rid',$activity_informations->rid);
    
    $event = new Object();
    $event->name         = $activity_informations->name;
    $event->description  = $activity_informations->description."<br><a href=".$CFG->wwwroot."/mod/voiceemail/view.php?id=".$activity_informations->id."&action=launchCalendar target=_self >Launch the Voice E-Mail ...</a>";   
    $event->format       = 1;           
    $event->userid       = 0;    
    $event->courseid     = $activity_informations->course;  //course event
    $event->groupid      = 0;
    $event->modulename   = 'voiceemail';
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
    
    wimba_add_log(WIMBA_DEBUG,voiceemail_LOGS,"Add calendar event\n".print_r($event, true ));  
    
    $oldEvent=get_record('event','instance',$instanceNumber,'modulename','voiceemail');
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


function voiceemail_deleteCalendarEvent($instanceNumber){
  /// Basic event record for the database.
    global $CFG;        
    
    if(!$event=get_record('event','instance',$instanceNumber,'modulename','voiceemail'))
    {
        wimba_add_log(WIMBA_ERROR,voiceemail_LOGS,"Problem to delete calendar event : ".$instanceNumber); 
        return false;
    }
    $result=delete_records("event", "id", $event->id);               
}  

/**
* get the calendar event which matches the id
* @param $id - the voicetool instance 
* @return the calendar event or false 
*/
function voiceemail_get_event_calendar($id) {

  $event = get_record('event', 'instance', $id,'modulename','voiceemail');
  if($event === false || empty($event)) {
  
    return false;
  }
  return $event;
}


function voiceemail_store_new_element($voicetool) {     
    
  $id=insert_record("voiceemail_resources", $voicetool);  

  return $id;
}

function voiceemail_update_element($voicetool) {
  $oldId = get_record('voiceemail_resources','rid',$voicetool->rid);
 
  $voicetool->id = $oldId->id;  
  $id=update_record("voiceemail_resources", $voicetool);  

  return $id;      
}




/**
* Implementation of the function for printing the form elements that control
* whether the course reset functionality affects the chat.
* @param $mform form passed by reference
*/
 function voiceemail_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'classroomheader', get_string('modulenameplural', 'voiceemail'));   
    $mform->addElement('checkbox', 'reset_content_voiceemail', "Delete history");
   

}

/**
* For Moodle version <1.9
* Implementation of the function for printing the form elements that control
* whether the course reset functionality affects the chat.
* @param $mform form passed by reference
*/
 function voiceemail_reset_course_form($course) {
    $activities = get_record("voiceemail","course",$course->id);
  
    if($activities)
    {
        print_checkbox('reset_content_voiceemail', 1, false, "Delete history", '', "");  echo '<br />';
    }
    else
    {
        echo "There is not Voice E-Mail in this course";    
    }
} 


/**
* Actual implementation of the rest coures functionality, delete all the
* chat messages for course $data->courseid.
* @param $data the data submitted from the reset course.
* @return array status array
*/
function voiceemail_reset_userdata($data) {
   global $CFG;

   $componentstr = get_string('modulenameplural', 'voiceemail');
   $typesstr    = "Delete history";
   $status = array();

   if (!empty($data->reset_content_voiceemail)) {
        $resources = get_records("voiceemail_resources","course",$data->id) ;
        foreach(array_keys($resources) as $id) 
        {
            $rid = $resources[$id]->rid;
            $newResource = voicetools_api_copy_resource($rid,"",2);
            //delete the old one and update the stored record
           // voicetools_api_delete_resource($rid);
            $resources[$id]->rid=$newResource->getRid();
            update_record("voiceemail_resources",$resources[$id]);           
        }
        $status[] = array('component'=>$componentstr, 'item'=>$typesstr, 'error'=>false);
   }
   return $status;
}

/**
* For moodle version < 1.9
* Actual implementation of the rest coures functionality, delete all the
* chat messages for course $data->courseid.
* @param $data the data submitted from the reset course.
* @return array status array
*/
function voiceemail_delete_userdata($data,$showfeedback=true) {
   global $CFG;

   $componentstr = get_string('modulenameplural', 'voiceemail');
   $typesstr    = "Delete history";

   if (!empty($data->reset_content_voiceemail)) {
        $resources = get_records("voiceemail_resources","course",$data->id) ;
        foreach(array_keys($resources) as $id) 
        {
            $rid = $resources[$id]->rid;
            $newResource = voicetools_api_copy_resource($rid,"",2);
            //delete the old one and update the stored record
            //voicetools_api_delete_resource($rid);
            $resources[$id]->rid=$newResource->getRid();
            update_record("voiceemail_resources",$resources[$id]);           
        }
      
       if($showfeedback)
       {
            $strreset = get_string('reset');
            notify($strreset.': '.$typestr, 'notifysuccess');
       }      
   }

}
function voiceemail_createResourceFromResource($rid,$new_rid,$new_course,$options="0")
{
    
    $voicetools = get_record("voiceauthoring_resources","rid",$rid);
    $voicetools->id = null;
    $voicetools->rid = $new_rid;
    $voicetools->course = $new_course;
    $voicetools->fromrid = $rid;
    $voicetools->copyOptions = $options;
     
    return voiceemail_store_new_element($voicetools);
} 
?>