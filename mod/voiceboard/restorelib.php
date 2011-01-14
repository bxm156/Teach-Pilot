<?php //$Id: restorelib.php,v 1.22.6.1 2008/01/24 19:05:01 nicolasconnault Exp $
    //This php script contains all the stuff to backup/restore
    //chat mods

    //This is the "graphical" structure of the chat mod:
    //
    //                       chat
    //                    (CL,pk->id)             
    //                        |
    //                        |
    //                        |
    //                    chat_messages 
    //                (UL,pk->id, fk->chatid)
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------


require_once($CFG->dirroot.'/mod/voiceboard/lib.php');

    //This function executes all the restore procedure about this mod
    function voiceboard_restore_mods($mod,$restore) {

        global $CFG;

        $status = true;

        //Get record from backup_ids
        $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);
        $userdata = restore_userdata_selected($restore,"voiceboard",$mod->id);

        if ($data) {
            //Now get completed xmlized object   
            $info = $data->info;
            $old_rid=$info['MOD']['#']['RID']['0']['#'];
            //add logs
            //Now, build the voiceboard record structure
            if("voiceboard" == "voiceboard")
            {
              $typeCopy = "0";  //we copy all the content
            }
            else
            {
              $typeCopy = "1";  //we copy top messages
            }
            
            if($userdata === true)
            {
                $copyOptions = $typeCopy;    
                $resourceDbMatched= get_record("voiceboard_resources", "fromrid", $old_rid,"course",$restore->course_id,"copyoptions",$typeCopy);//resource which match the current copy options
                $resourceDbOther = get_record("voiceboard_resources", "fromrid",$old_rid ,"course",$restore->course_id,"copyoptions","2");
            }
            else
            {
                $copyOptions="2";//delete all  
                $resourceDbMatched = get_record("voiceboard_resources", "fromrid", $old_rid,"course",$restore->course_id,"copyoptions","2");
                $resourceDbOther = get_record("voiceboard_resources", "fromrid", $old_rid,"course",$restore->course_id,"copyoptions",$typeCopy);
               
            }
  
            
            if(empty($resourceDbMatched))
            { // the resource of the type needed was not created before
                
                 $newResource = voicetools_api_copy_resource($old_rid,null,$copyOptions); 
                 if($newResource === false){
                   return false;//error during the copy
                 }
                 $newResource =  voicetools_api_get_resource($newResource->getRid()) ; // get all the informations 
                 if($newResource === false){
                   return false;//error to get the resouce
                 }
                 $newResourceOptions = $newResource->getOptions();
                 $isGradable =  $newResourceOptions->getGrade();
                 $resourceId = $newResource->getRid();
                 
                if(!empty($resourceDbOther))
                {//the other type was created, need to update one name 

                    if($copyOptions == $typeCopy) //user data is checked
                    {//we have to update the name of the new one
                        $newResource->setTitle($newResource->getTitle()." with user data");
                        if(voicetools_api_modify_resource($newResource->getResource()) === false){
                          return false;//error to get the resouce
                        }
                        //save some parameters that we will used to manage the grade column
                        $title = $newResource->getTitle();
                        $ridForGrade = $newResource->getRid();
                        $pointsPossible =  $newResourceOptions->getPointsPossible();
                        $actionGradebook = "create";//we will only need to create the grade column with grades for the second resource.
                        
                   }
                   else
                   {    //we have to update the other which was the one with user data
                        $otherResource =  voicetools_api_get_resource($resourceDbOther->rid) ;
                        if($otherResource === false){
                          return false;//error to get the resouce
                        }
                        $otherResource->setTitle($otherResource->getTitle()." with user data");
                        
                        if(voicetools_api_modify_resource($otherResource->getResource()) === false){
                          return false;//error to get the resouce
                        }
                        $title = $otherResource->getTitle();
                        $ridForGrade = $otherResource->getRid();
                        $otherResourceOptions = $otherResource->getOptions();
                        $pointsPossible = $otherResourceOptions->getPointsPossible();
                        $actionGradebook = "update";//we will only have to update the name of the grade column and create a new one
                   }
                    
                    
                        //we store the new resource in the database; 
                } 
                //update the moodle database
                voiceboard_createResourceFromResource($old_rid,$resourceId,$restore->course_id,$copyOptions); 
               
            }           
            else
            {
              //the resource already exist
              $resourceId = $resourceDbMatched->rid;
              $isGradable = false;
              
            }
            
            $voicefeature->course = backup_todb($restore->course_id);
            $voicefeature->rid = backup_todb($resourceId);
            $voicefeature->name = str_replace(backup_todb($old_rid),$resourceId,backup_todb($info['MOD']['#']['NAME']['0']['#']));
            $voicefeature->section = backup_todb($info['MOD']['#']['SECTION']['0']['#']);
            $voicefeature->timemodified = backup_todb($info['MOD']['#']['TIMEMODIFIED']['0']['#']);
            $voicefeature->isfirst = 1;
            //The structure is equal to the db, so insert the voiceboard
            $newActivityId = insert_record ("voiceboard",$voicefeature);
           
            if($isGradable == 'true'){//the old vb was gradable
              
              //the activity linked has changed due to the copy, we need to update it to be able to match the good grade column
              $vb = get_record("voiceboard_resources", "rid",$resourceId);
              $vb->gradeid = $newActivityId;
              update_record("voiceboard_resources",$vb);
                   
              $oldResourceDb = get_record("voiceboard_resources", "rid",$old_rid);
              $students=getStudentsEnrolled($oldResourceDb->course);
              $users_key = array_keys($students);
              
              //get the grade of the initial resource
              $gradesfromInitialResource = grade_get_grades($oldResourceDb->course, "mod", "voiceboard", $oldResourceDb->gradeid,$users_key);
              $grades = null;
              if(isset($gradesfromInitialResource->items[0]))
              {
                $grades=voiceboard_build_gradeObject_From_ArrayOfGradeInfoObjects($gradesfromInitialResource->items[0]->grades);
              }
                   
              if(isset($actionGradebook) && $actionGradebook == "update"){                 
                //we update the name of the column (add "with user data")
                 voiceboard_delete_grade_column($ridForGrade, $restore->course_id, $newActivityId);//delete the one automatically created by moodle
                 voiceboard_add_grade_column($ridForGrade, $restore->course_id, $title, $pointsPossible, $grades);
                 //we need to create the grade column with contains no grade( user data was unchecked);
                 voiceboard_add_grade_column($newResource->getRid(), $restore->course_id, $newResource->getTitle(), $newResourceCopyOptions->getPointsPossible());
              }   
              else if(isset($actionGradebook) && $actionGradebook =="create")
              {
                 voiceboard_add_grade_column($ridForGrade, $restore->course_id, $title, $pointsPossible, $grades);
              
              } 
            }
           
                 
            //Do some output     
            if (!defined('RESTORE_SILENTLY')) {
                echo "<li>".get_string("modulename","voiceboard")." \"".format_string(stripslashes($voicefeature->name),true)."\"</li>";
            }
            backup_flush(300);

            if ($newActivityId) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code,$mod->modtype,
                             $mod->id, $newActivityId);
            } else {
                $status = false;
            }
        } else {
            $status = false;
        }

        return $status;
    }


    //Return a content decoded to support interactivities linking. Every module
    //should have its own. They are called automatically from
    //voiceboard_decode_content_links_caller() function in each module
    //in the restore process
    function voiceboard_decode_content_links ($content,$restore) {
            
        global $CFG;
            
        $result = $content;
                
        //Link to the list of voiceboards
                
        $searchstring='/\$@(voiceboardINDEX)\*([0-9]+)@\$/';
        //We look for it
        preg_match_all($searchstring,$content,$foundset);
        //If found, then we are going to look for its new id (in backup tables)
        if ($foundset[0]) {
            //print_object($foundset);                                     //Debug
            //Iterate over foundset[2]. They are the old_ids
            foreach($foundset[2] as $old_id) {
                //We get the needed variables here (course id)
                $rec = backup_getid($restore->backup_unique_code,"course",$old_id);
                //Personalize the searchstring
                $searchstring='/\$@(voiceboardINDEX)\*('.$old_id.')@\$/';
                //If it is a link to this course, update the link to its new location
                if($rec->new_id) {
                    //Now replace it
                    $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/voiceboard/index.php?id='.$rec->new_id,$result);
                } else { 
                    //It's a foreign link so leave it as original
                    $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/voiceboard/index.php?id='.$old_id,$result);
                }
            }
        }

        //Link to voiceboard view by moduleid

        $searchstring='/\$@(voiceboardVIEWBYID)\*([0-9]+)@\$/';
        //We look for it
        preg_match_all($searchstring,$result,$foundset);
        //If found, then we are going to look for its new id (in backup tables)
        if ($foundset[0]) {
            //print_object($foundset);                                     //Debug
            //Iterate over foundset[2]. They are the old_ids
            foreach($foundset[2] as $old_id) {
                //We get the needed variables here (course_modules id)
                $rec = backup_getid($restore->backup_unique_code,"course_modules",$old_id);
                //Personalize the searchstring
                $searchstring='/\$@(voiceboardVIEWBYID)\*('.$old_id.')@\$/';
                //If it is a link to this course, update the link to its new location
                if($rec->new_id) {
                    //Now replace it
                    $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/voiceboard/view.php?id='.$rec->new_id,$result);
                } else {
                    //It's a foreign link so leave it as original
                    $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/voiceboard/view.php?id='.$old_id,$result);
                }
            }
        }

        return $result;
    }

    //This function makes all the necessary calls to xxxx_decode_content_links()
    //function in each module, passing them the desired contents to be decoded
    //from backup format to destination site/course in order to mantain inter-activities
    //working in the backup/restore process. It's called from restore_decode_content_links()
    //function in restore process
    function voiceboard_decode_content_links_caller($restore) {
        global $CFG;
        $status = true;
        $content = "";
        if ($voicefeatures = get_records_sql ("SELECT c.id
                                   FROM {$CFG->prefix}voiceboard c
                                   WHERE c.course = $restore->course_id")) {
                                               //Iterate over each voiceboard->intro
            $i = 0;   //Counter to send some output to the browser to avoid timeouts
            foreach ($voicefeatures as $voicefeature) {
                //Increment counter
                $i++;
              
                $result = restore_decode_content_links_worker($content,$restore);
                if ($result != $content) {
                    //Update record
                    $voicefeature->intro = addslashes($result);
                    $status = update_record("voiceboard",$voicefeature);
                    if (debugging()) {
                        if (!defined('RESTORE_SILENTLY')) {
                            echo '<br /><hr />'.s($content).'<br />changed to<br />'.s($result).'<hr /><br />';
                        }
                    }
                }
                //Do some output
                if (($i+1) % 5 == 0) {
                    if (!defined('RESTORE_SILENTLY')) {
                        echo ".";
                        if (($i+1) % 100 == 0) {
                            echo "<br />";
                        }
                    }
                    backup_flush(300);
                }
            }
        }

        return $status;
    }

?>
