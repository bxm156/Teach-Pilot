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


require_once($CFG->dirroot.'/mod/voiceemail/lib.php');
    //This function executes all the restore procedure about this mod
    function voiceemail_restore_mods($mod,$restore) {

        global $CFG;

        $status = true;

        //Get record from backup_ids
        $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);
        $userdata = restore_userdata_selected($restore,"voiceemail",$mod->id);

        if ($data) {
            //Now get completed xmlized object   
            $info = $data->info;
            
            //add logs
            //Now, build the voiceemail record structure
            if($userdata === true)
            {
                $copyOptions="0";//top message    
            }
            else
            {
                $copyOptions="2";//top message   
            }
        
            $oldResource=get_record("voiceemail_resources","id",$info['MOD']['#']['RID']['0']['#']);
            $resourceId=$oldResource->rid;
            $resource = voicetools_api_copy_resource($resourceId,null,$copyOptions);
            if($resource === false){
              return false;//error to copy the resource
            }
            $bdId = voiceemail_createResourceFromResource($resourceId,$resource->getRid(),$restore->course_id);
            
            $voiceemail->course = backup_todb($restore->course_id);
            $voiceemail->rid = backup_todb($bdId);
            $voiceemail->recipients_email = backup_todb(backup_todb($info['MOD']['#']['RECIPIENTS_EMAIL']['0']['#']));
            $voiceemail->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
            $voiceemail->section = backup_todb($info['MOD']['#']['SECTION']['0']['#']);
            $voiceemail->timemodified = backup_todb($info['MOD']['#']['TIMEMODIFIED']['0']['#']);
            $voiceemail->isfirst = 1;
            
            //The structure is equal to the db, so insert the voiceemail
            $newid = insert_record ("voiceemail",$voiceemail);

            //Do some output     
            if (!defined('RESTORE_SILENTLY')) {
                echo "<li>".get_string("modulename","voiceemail")." \"".format_string(stripslashes($voiceemail->name),true)."\"</li>";
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
    //voiceemail_decode_content_links_caller() function in each module
    //in the restore process
    function voiceemail_decode_content_links ($content,$restore) {
            
        global $CFG;
            
        $result = $content;
                
        //Link to the list of voiceemails
                
        $searchstring='/\$@(voiceemailINDEX)\*([0-9]+)@\$/';
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
                $searchstring='/\$@(voiceemailINDEX)\*('.$old_id.')@\$/';
                //If it is a link to this course, update the link to its new location
                if($rec->new_id) {
                    //Now replace it
                    $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/voiceemail/index.php?id='.$rec->new_id,$result);
                } else { 
                    //It's a foreign link so leave it as original
                    $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/voiceemail/index.php?id='.$old_id,$result);
                }
            }
        }

        //Link to voiceemail view by moduleid

        $searchstring='/\$@(voiceemailVIEWBYID)\*([0-9]+)@\$/';
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
                $searchstring='/\$@(voiceemailVIEWBYID)\*('.$old_id.')@\$/';
                //If it is a link to this course, update the link to its new location
                if($rec->new_id) {
                    //Now replace it
                    $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/voiceemail/view.php?id='.$rec->new_id,$result);
                } else {
                    //It's a foreign link so leave it as original
                    $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/voiceemail/view.php?id='.$old_id,$result);
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
    function voiceemail_decode_content_links_caller($restore) {
        global $CFG;
        $status = true;
        
        if ($voiceemails = get_records_sql ("SELECT c.id, c.intro
                                   FROM {$CFG->prefix}voiceemail c
                                   WHERE c.course = $restore->course_id")) {
                                               //Iterate over each voiceemail->intro
            $i = 0;   //Counter to send some output to the browser to avoid timeouts
            foreach ($voiceemails as $voiceemail) {
                //Increment counter
                $i++;
                $content = $voiceemail->intro;
                $result = restore_decode_content_links_worker($content,$restore);
                if ($result != $content) {
                    //Update record
                    $voiceemail->intro = addslashes($result);
                    $status = update_record("voiceemail",$voiceemail);
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
