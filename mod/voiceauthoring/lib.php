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

global $CFG,$ALLOWED_TAGS;
//make sure that we can display html in the activity name

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

define("VOICEAUTHORING_MODULE_VERSION", "3.3.0");
define("voiceauthoring_LOGS", "voiceauthoring");

/**
* Validate the data in passed in the configuration page
* @param $config - the information from the form mod.html
*/
function voiceauthoring_process_options ($config) {
}

function voiceauthoring_add_instance($voicetool) {
  /// Given an object containing all the necessary data, 
  /// (defined by the form in mod.html) this function 
  /// will create a new instance and return the id number 
  /// of the new instance.
   
    global $CFG;
    $voicetool->timemodified = time();  
    $imgPath = $CFG->wwwroot."/mod/voiceauthoring/lib/web/pictures/items/speaker-18.gif";
    $objectPath = $CFG->wwwroot."/mod/voiceauthoring/enableVA.php";
    $iFramePath = $CFG->wwwroot."/mod/voiceauthoring/displayPlayer.php?rid=".$voicetool->rid."&mid="."va-".$voicetool->mid."&title=".urlencode($voicetool->name);
    $iFramePath1 = $CFG->wwwroot."/mod/voiceauthoring/displayActivityName.php?rid=".$voicetool->rid."&mid="."va-".$voicetool->mid."&title=".urlencode($voicetool->name);
    $voicetool->activityname=htmlspecialchars($voicetool->name, ENT_QUOTES);
    $voicetool->name="<iframe id=\'".$voicetool->rid."_"."va-".$voicetool->mid."\'  width=\'0px\' height=\'0px\' style=\'float:left;display:none;position:absolute;overflow-x:hidden;overflow-y:hidden\' frameborder=\'0\' scrolling=\'no\' allowTransparency=\'true\' >
       </iframe><span id=\'".$voicetool->rid."_"."va-".$voicetool->mid."_span\'>".$voicetool->name."</span><iframe id=\'".$voicetool->rid."_"."va-".$voicetool->mid."_name\' src=\'".$iFramePath1."\' frameborder=\'0\' scrolling=\'no\'  style=\'overflow-x:hidden;overflow-y:hidden;\' width=\'20px\' height=\'20px\' allowTransparency=\'true\'></iframe>";
 
    
    $mid_number = $voicetool->mid;
    $voicetool->mid = "va-".$voicetool->mid; //we add va- to avoid conflict with block resource
    if (!$voicetool->id = insert_record('voiceauthoring', $voicetool)) 
    {
        wimba_add_log(WIMBA_ERROR,voiceauthoring_LOGS,"Problem to add a new instance");  
        return false;
    }
  
    if(isset($voicetool->calendar_event) &&  $voicetool->calendar_event == true) 
    { 
      voiceauthoring_addCalendarEvent($voicetool,$voicetool->id); 
    }

  
    if ($resource = get_record("voiceauthoring_resources", "rid", $voicetool->rid))
    {
        if($mid_number != $resource->mid)
        {    
            updateRecorderResource($voicetool->rid,$voicetool->course,$mid_number);//update the mid for the next message
        }
    }
   
    wimba_add_log(WIMBA_INFO,voiceauthoring_LOGS,"Add Instance".$voicetool->id);  
    //for the debug
    wimba_add_log(WIMBA_DEBUG,voiceauthoring_LOGS,print_r($voicetool, true )); 
    
    set_config("allowobjectembed",1);
    set_config("formatstringstriptags",0);
    
    return $voicetool->id;  

}    

function voiceauthoring_update_instance($voicetool) {
  /// Given an object containing all the necessary data, 
  /// (defined by the form in mod.html) this function
    global $USER;
    global $CFG;
    //get the course_module instance linked to the liveclassroom instance
    if (! $cm = get_coursemodule_from_instance("voiceauthoring", $voicetool->instance, $voicetool->course)) 
    {
        wimba_add_log(WIMBA_ERROR,voiceauthoring_LOGS,"Problem to update the instance : ".$voicetool->instance); 
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
            wimba_add_log(WIMBA_ERROR,voiceauthoring_LOGS,"Problem to update the instance : ".$voicetool->instance); 
            error("Could not add the new course module to that section");
        }
        //update the course modules  
        if (! set_field("course_modules", "section", $sectionid, "id", $cm->id)) 
        {
            wimba_add_log(WIMBA_ERROR,voiceauthoring_LOGS,"Problem to update the instance : ".$voicetool->instance); 
            error("Could not update the course module with the correct section");
        }
    }
   
   
    $voicetool->timemodified = time();  
    $objectPath = $CFG->wwwroot."/mod/voiceauthoring/enableVA.php";
    $imgPath = $CFG->wwwroot."/mod/voiceauthoring/lib/web/pictures/items/speaker-18.gif";
    $iFramePath = $CFG->wwwroot."/mod/voiceauthoring/displayPlayer.php?rid=".$voicetool->rid."&mid=".$voicetool->mid;
    $iFramePath1 = $CFG->wwwroot."/mod/voiceauthoring/displayActivityName.php?rid=".$voicetool->rid."&mid=".$voicetool->mid."&title=".urlencode($voicetool->name);
    $voicetool->activityname=htmlspecialchars($voicetool->name, ENT_QUOTES);
    $voicetool->name="<iframe id=\'".$voicetool->rid."_".$voicetool->mid."\'  width=\'0px\' height=\'0px\' style=\'float:left;display:none;position:absolute;overflow-x:hidden;overflow-y:hidden\' frameborder=\'0\' scrolling=\'no\' allowTransparency=\'true\' >
       </iframe><span id=\'".$voicetool->rid."_".$voicetool->mid."_span\'>".$voicetool->name."</span><iframe id=\'".$voicetool->rid."_".$voicetool->mid."_name\' src=\'".$iFramePath1."\' frameborder=\'0\' scrolling=\'no\'  style=\'overflow-x:hidden;overflow-y:hidden;\' width=\'20px\' height=\'20px\' allowTransparency=\'true\'></iframe>";
 
    $voicetool->id = $voicetool->instance;
    $voicetool->isfirst = 0;  
    if (!$voicetool->id = update_record('voiceauthoring', $voicetool)) 
    {
      return false;
    }
    
    if(isset($voicetool->calendar_event) && $voicetool->calendar_event) 
    {//no problem
        voiceauthoring_addCalendarEvent($voicetool,$voicetool->instance); 
    } 
    else 
    {
        voiceauthoring_deleteCalendarEvent($voicetool->instance );          
    }
    
    set_config("allowobjectembed",1);
    set_config("formatstringstriptags",0);
    wimba_add_log(WIMBA_INFO,voiceauthoring_LOGS,"Update of the instance : ".$voicetool->id); 
    return $voicetool->id ;
}


function voiceauthoring_delete_instance($id) {
    /// Given an ID of an instance of this module, 
    /// this function will permanently delete the instance 
    /// and any data that depends on it.
    $result = true;  
    if (! $voicetool = get_record("voiceauthoring", "id", $id)) 
    {
        return false;
    }
    # Delete any dependent records here #
    if (! $instanceNumber=delete_records("voiceauthoring", "id", $voicetool->id)) 
    {
        wimba_add_log(WIMBA_ERROR,voiceauthoring_LOGS,"Problem to delete the instance : ".$voicetool->id); 
        $result = false;
    } 
    
    voiceauthoring_deleteCalendarEvent($voicetool->id);
    // delete the related calendar event
       
    return $result;  
}
  

function voiceauthoring_user_outline($course, $user, $mod, $voicetool) {
  /// Return a small object with summary information about what a 
  /// user has done with a given particular instance of this module
  /// Used for user activity reports.
  /// $return->time = the time they did it
  /// $return->info = a short text description
    return $return;
}

function voiceauthoring_user_complete($course, $user, $mod, $voicetool) {
  /// Print a detailed representation of what a  user has done with 
  /// a given particular instance of this module, for user activity reports.

  return true;
}

function voiceauthoring_print_recent_activity($course, $isteacher, $timestart) {
  /// Given a course and a time, this module should find recent activity 
  /// that has occurred in voicetool activities and print it out. 
  /// Return true if there was output, or false is there was none.

  global $CFG;
 
  return false;  //  True if anything was printed, otherwise false 
}

function voiceauthoring_cron () {
  /// Function to be run periodically according to the moodle cron
  /// This function searches for things that need to be done, such 
  /// as sending out mail, toggling flags etc ... 

  global $CFG;

  return true;
}

function voiceauthoring_grades($voicetoolid) {
  /// Must return an array of grades for a given instance of this module, 
  /// indexed by user.  It also returns a maximum allowed grade.
  ///
  ///    $return->grades = array of grades;
  ///    $return->maxgrade = maximum allowed grade;
  ///
  ///    return $return;

  return NULL;
}

function voiceauthoring_get_participants($voicetoolid) {
  //Must return an array of user records (all data) who are participants
  //for a given instance of voicetool. Must include every user involved
  //in the instance, independient of his role (student, teacher, admin...)
  //See other modules as example.

  return false;
}

function voiceauthoring_scale_used ($voicetoolid,$scaleid) {
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

function voiceauthoring_getRole($context)
{
    global $CFG;
    global $USER;
    $role = "";

    if (has_capability('mod/voiceauthoring:presenter', $context)) {
      $role = 'Instructor';
    } else {
      $role = 'Student';
    }

    return $role;
}

function voiceauthoring_get_url_params($courseid)
{
    global $USER;
    global $CFG;

    $context = get_context_instance(CONTEXT_COURSE, $courseid) ;

    $role =voiceauthoring_getRole($context);
    $signature = md5($courseid . $USER->email . $USER->firstname . $USER->lastname . $role);
    
    $url_params = "enc_course_id=" . rawurlencode($courseid) . 
                  "&enc_email=" . rawurlencode($USER->email) . 
                  "&enc_firstname=" . rawurlencode($USER->firstname) . 
                  "&enc_lastname=" . rawurlencode($USER->lastname) . 
                  "&enc_role=" . rawurlencode($role) . 
                  "&signature=" . rawurlencode($signature);
    return $url_params;
}

function voiceauthoring_addCalendarEvent($activity_informations,$instanceNumber){
  global $CFG;  

    //get some complementary of the resource       
    $resource=get_record('voiceauthoring_resources','rid',$activity_informations->rid);
    
    $event = new Object();
    $event->name         = $activity_informations->activityname;
    $event->description  = $activity_informations->description."<br>"."<a
                    href=".$CFG->wwwroot."/mod/voiceauthoring/view.php?id=".$instanceNumber."&launchCal=".$activity_informations->rid."_".$activity_informations->mid
                   .">Listen the Voice Authoring message.</a>";   
    $event->format       = 1;           
    $event->userid       = 0;    
    $event->courseid     = $activity_informations->course;  //course event
    $event->groupid      = 0;
    $event->modulename   = 'voiceauthoring';
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
    
    wimba_add_log(WIMBA_DEBUG,voiceauthoring_LOGS,"Add calendar event\n".print_r($event, true ));  
    
    $oldEvent=get_record('event','instance',$instanceNumber,'modulename',"voiceauthoring");
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


function voiceauthoring_deleteCalendarEvent($instanceNumber){
  /// Basic event record for the database.
    global $CFG;        
    
    if(!$event=get_record('event','instance',$instanceNumber,'modulename','voiceauthoring'))
    {
        wimba_add_log(WIMBA_ERROR,voiceauthoring_LOGS,"Problem to delete calendar event : ".$instanceNumber); 
        return false;
    }
    $result=delete_records("event", "id", $event->id);               
}  

/**
* get the calendar event which matches the id
* @param $id - the voicetool instance 
* @return the calendar event or false 
*/
function voiceauthoring_get_event_calendar($id) {

  $event = get_record('event', 'instance', $id,'modulename','voiceauthoring');
  if($event==false || empty($event)) {
  
    return false;
  }
  return $event;
}



function  voiceauthoring_store_new_element($voicetool) {

  $id = insert_record("voiceauthoring_resources", $voicetool);  

  return $id;
}

function voiceauthoring_update_element($voicetool) {
  $oldId = get_record('voiceauthoring_resources','rid',$voicetool->rid);
 
  $voicetool->id = $oldId->id;  
  $id=update_record("voiceauthoring_resources", $voicetool);  

  return $id;      
}

/**
* Implementation of the function for printing the form elements that control
* whether the course reset functionality affects the chat.
* @param $mform form passed by reference
*/
 function voiceauthoring_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'classroomheader', get_string('modulenameplural', 'voiceauthoring'));   
    $mform->addElement('checkbox', 'reset_content_voiceauthoring', "Delete all messages");
}

/**
 * For version < 1.9
* Implementation of the function for printing the form elements that control
* whether the course reset functionality affects the chat.
* @param $mform form passed by reference
*/
 function voiceauthoring_reset_course_form($course) {
    $activities = get_record("voiceauthoring","course",$course->id);
  
    if($activities)
    {
        print_checkbox('reset_content_voiceauthoring', 1, false, "Delete all messages", '', "");  echo '<br />';
    }
    else
    {
         echo "There is not Voice Authoring in this course";  
    }
} 


   /**
    * Actual implementation of the rest coures functionality, delete all the
    * chat messages for course $data->courseid.
    * @param $data the data submitted from the reset course.
    * @return array status array
    */
function voiceauthoring_reset_userdata($data) {
   global $CFG;

   $componentstr = get_string('modulenameplural', 'voiceauthoring');
   $typesstr="Delete all message";
   $status = array();


   if (!empty($data->reset_content_voiceauthoring)) {
        $resource = get_record("voiceauthoring_resources","course",$data->id) ;
        $rid = $resource->rid;
        $newResource = voicetools_api_copy_resource($rid,"",2);
        //delete the old one and update the stored record
        //voicetools_api_delete_resource($rid);
        $resource->rid = $newResource->getRid();
        update_record("voiceauthoring_resources",$resource);
        //update all the activities which use this resource
        $activities = get_records("voiceauthoring","rid",$rid);
        
        foreach(array_keys($activities) as $activity_id)
        {
            $new_rid = $newResource->getRid();
            $activities[$activity_id]->name = str_replace($rid,$new_rid, $activities[$activity_id]->name);
            $activities[$activity_id]->rid = $new_rid;
            $activities[$activity_id]->name = addslashes($activities[$activity_id]->name);
            update_record("voiceauthoring", $activities[$activity_id]);     
        }
        $status[] = array('component'=>$componentstr, 'item'=>$typesstr, 'error'=>false);
   }
   rebuild_course_cache($data->id);
   return $status;
}

   /**
    * For moodle version < 1.9
    * Actual implementation of the rest coures functionality, delete all the
    * chat messages for course $data->courseid.
    * @param $data the data submitted from the reset course.
    * @return array status array
    */
function voiceauthoring_delete_userdata($data) {
   global $CFG;

   $componentstr = get_string('modulenameplural', 'voiceauthoring');
   $typesstr="Delete all message";
   $status = array();


   if (!empty($data->reset_content_voiceauthoring)) {
        $resource = get_record("voiceauthoring_resources","course",$data->id) ;
        $rid = $resource->rid;
        $newResource = voicetools_api_copy_resource($rid,"",2);
        //delete the old one and update the stored record
        //voicetools_api_delete_resource($rid);
        $resource->rid = $newResource->getRid();
        update_record("voiceauthoring_resources",$resource);
        //update all the activities which use this resource
        $activities = get_records("voiceauthoring","rid",$rid);
        
        foreach(array_keys($activities) as $activity_id)
        {
            $new_rid = $newResource->getRid();
            $activities[$activity_id]->name = str_replace($rid,$new_rid, $activities[$activity_id]->name);
            $activities[$activity_id]->rid = $new_rid;
            $activities[$activity_id]->name = addslashes($activities[$activity_id]->name);
            update_record("voiceauthoring", $activities[$activity_id]);     
        }
        $status[] = array('component'=>$componentstr, 'item'=>$typesstr, 'error'=>false);
   }
   rebuild_course_cache($data->id);
   return $status;
}

   
function voiceauthoring_get_resource_rid($courseId) {
    $voice_authoring = get_record('voiceauthoring_resources','course',$courseId);
    
    if($voice_authoring === false)
    {
        wimba_add_log(WIMBA_INFO,voiceauthoring_LOGS,"No resources have been created yet"); 
        return false;
    }
   
    return $voice_authoring->rid;
 }
       


function voiceauthoring_update_block_informations($informations) {
          
    $oldInformations = get_record('voiceauthoring_block','bid',$informations->bid);
    if($oldInformations === false)
    { 
        wimba_add_log(WIMBA_ERROR,voiceauthoring_LOGS,"Problem to get the recorder information"); 
        return false;
    }
    else
    { 
        $informations->id = $oldInformations->id;
        $id=update_record("voiceauthoring_block", $informations);  
    }
    return $id;      
}

 function voiceauthoring_get_block_informations($blockId) {
        
    $recorder = get_record('voiceauthoring_block','bid',$blockId);
    if ($recorder === false ) 
    {     
        $recorder->bid = $blockId;  
        $recorder->comment = "Configure this block to add a description";
        $recorder->title = "Voice Authoring";      
        $id=insert_record("voiceauthoring_block", $recorder);   
    } 
    wimba_add_log(WIMBA_DEBUG,voiceauthoring_LOGS,"Recorder informations : \n".print_r($recorder, true ));
    
    return $recorder;      
 }

function voiceauthoring_createResourceFromResource($rid,$new_rid,$new_course,$options="0")
{
    
    $voicetools = get_record("voiceauthoring_resources","rid",$rid);
    $voicetools->id = null;
    $voicetools->rid = $new_rid;
    $voicetools->course = $new_course;
    $voicetools->fromrid = $rid;
    $voicetools->copyOptions = $options;
     
    return voiceauthoring_store_new_element($voicetools);
} 

?>
