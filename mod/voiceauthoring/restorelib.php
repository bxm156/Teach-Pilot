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
    
require_once($CFG->dirroot.'/mod/voiceauthoring/lib.php');
    //This function executes all the restore procedure about this mod
function voiceauthoring_restore_mods($mod,$restore) {

    global $CFG;

    $status = true;

    //Get record from backup_ids
    $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);
    $userdata = restore_userdata_selected($restore,"voiceauthoring",$mod->id);

    if ($data) {
        //Now get completed xmlized object   
        $info = $data->info;
        
        //add logs
        //Now, build the voiceauthoring record structure
        $resource = get_record("voiceauthoring_resources", "fromrid", $info['MOD']['#']['RID']['0']['#'],"course",$restore->course_id);
        if(empty($resource))
        {
            $resourceCopy = voicetools_api_copy_resource($info['MOD']['#']['RID']['0']['#'],null,"0");
            if($resourceCopy === false){
              return false;//error to copy the resource
            }
            $resourceId=$resourceCopy->getRid();
            voiceauthoring_createResourceFromResource($info['MOD']['#']['RID']['0']['#'],$resourceId,$restore->course_id);
        }
        
        if($userdata === false)
        {
            
           $mid = $resource->mid + 1;
           $resource->mid = $resource->mid + 1;
           update_record("voiceauthoring_resources",$resource);
           $name = str_replace(backup_todb($info['MOD']['#']['MID']['0']['#']),$mid,backup_todb($info['MOD']['#']['NAME']['0']['#']));
        }
        else
        {
            $mid=backup_todb(backup_todb($info['MOD']['#']['MID']['0']['#']));
            $name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
            
        }
        
        $voiceauthoring->course = backup_todb($restore->course_id);
        $voiceauthoring->rid = backup_todb($resourceId);
        $voiceauthoring->mid = $mid ;
        $voiceauthoring->name = str_replace(backup_todb($info['MOD']['#']['RID']['0']['#']),$resourceId,$name);
        $voiceauthoring->activityname = backup_todb($info['MOD']['#']['ACTIVITY_NAME']['0']['#']);
        $voiceauthoring->section = backup_todb($info['MOD']['#']['SECTION']['0']['#']);
        $voiceauthoring->timemodified = backup_todb($info['MOD']['#']['TIMEMODIFIED']['0']['#']);
        $voiceauthoring->isfirst = 1;
        //The structure is equal to the db, so insert the voiceauthoring
        $newid = insert_record ("voiceauthoring",$voiceauthoring);

        //Do some output     
        if (!defined('RESTORE_SILENTLY')) {
            echo "<li>".get_string("modulename","voiceauthoring")." \"".format_string(stripslashes(stripslashes($voiceauthoring->name)),true)."\"</li>";
        }
        backup_flush(300);

        if ($newid) {
            //We have the newid, update backup_ids
            backup_putid($restore->backup_unique_code,$mod->modtype,
                         $mod->id, $newid);
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
//voiceauthoring_decode_content_links_caller() function in each module
//in the restore process
function voiceauthoring_decode_content_links ($content,$restore) {
        
    global $CFG;
        
    $result = $content;
            
    //Link to the list of voiceauthorings
            
    $searchstring='/\$@(voiceauthoringINDEX)\*([0-9]+)@\$/';
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
            $searchstring='/\$@(voiceauthoringINDEX)\*('.$old_id.')@\$/';
            //If it is a link to this course, update the link to its new location
            if($rec->new_id) {
                //Now replace it
                $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/voiceauthoring/index.php?id='.$rec->new_id,$result);
            } else { 
                //It's a foreign link so leave it as original
                $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/voiceauthoring/index.php?id='.$old_id,$result);
            }
        }
    }

    //Link to voiceauthoring view by moduleid

    $searchstring='/\$@(voiceauthoringVIEWBYID)\*([0-9]+)@\$/';
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
            $searchstring='/\$@(voiceauthoringVIEWBYID)\*('.$old_id.')@\$/';
            //If it is a link to this course, update the link to its new location
            if($rec->new_id) {
                //Now replace it
                $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/voiceauthoring/view.php?id='.$rec->new_id,$result);
            } else {
                //It's a foreign link so leave it as original
                $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/voiceauthoring/view.php?id='.$old_id,$result);
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
function voiceauthoring_decode_content_links_caller($restore) {
    global $CFG;
    $status = true;
    
    if ($voiceauthorings = get_records_sql ("SELECT c.id
                               FROM {$CFG->prefix}voiceauthoring c
                               WHERE c.course = $restore->course_id")) {
                                           //Iterate over each voiceauthoring->intro
        $i = 0;   //Counter to send some output to the browser to avoid timeouts
        foreach ($voiceauthorings as $voiceauthoring) {
            //Increment counter
            $i++;
            $content = $voiceauthoring->intro;
            $result = restore_decode_content_links_worker($content,$restore);
            if ($result != $content) {
                //Update record
                $voiceauthoring->intro = addslashes($result);
                $status = update_record("voiceauthoring",$voiceauthoring);
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
