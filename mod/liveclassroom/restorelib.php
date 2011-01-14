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

if (!function_exists('getKeysOfGeneralParameters')) {
    require_once('lib/php/common/WimbaLib.php');
}

require_once("lib/php/lc/lcapi.php");
require_once("lib/php/lc/LCAction.php");

    //This function executes all the restore procedure about this mod
    function liveclassroom_restore_mods($mod,$restore) {

        global $CFG;

        $status = true;

        //Get record from backup_ids
        $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);
        $userdata = restore_userdata_selected($restore,"liveclassroom",$mod->id);

        $lcAction = new LCAction(null,$CFG->liveclassroom_servername,
                     $CFG->liveclassroom_adminusername,
                     $CFG->liveclassroom_adminpassword,$CFG->dataroot,$restore->course_id);        
        if ($data) {
            //Now get completed xmlized object   
            $info = $data->info;
            
            //add logs
            //Now, build the liveclassroom record structure
            $copy_content=0; 
            
            if($userdata)
            {
                $copy_content=1;             
            }
            
                
            $sameResource = get_record("liveclassroom", "fromid", $info['MOD']['#']['TYPE']['0']['#'],"course",$restore->course_id,"copy_content",$copy_content);
            $resource = get_record("liveclassroom", "fromid", $info['MOD']['#']['TYPE']['0']['#'],"course",$restore->course_id,"copy_content",($copy_content==1)?0:1);
            if(empty($sameResource))
            {
                
                if( ! $new_lc_id = $lcAction->cloneRoom(  $restore->course_id,
                                                    $info['MOD']['#']['TYPE']['0']['#'], 
                                                    $userdata,$info['MOD']['#']['ISSTUDENTADMIN']['0']['#'],
                                                    $info['MOD']['#']['PREVIEW']['0']['#']) )
                {
                    return false;//there is a problem during the copy of the room
                } 
                
                if(!empty($resource) )
                {
                        
                    if($userdata)
                    {
                        $room=$lcAction->getRoom($new_lc_id);
                        $room->setLongname($room->getLongname()." with user data");
                        $lcAction->api->lcapi_modify_room($new_lc_id, $room->getAttributes());       
                    }
                    
                    else
                    {
                        $room=$lcAction->getRoom($resource->type);
                        $room->setLongname($room->getLongname()." with user data");
                        $lcAction->api->lcapi_modify_room($resource->type, $room->getAttributes()); 
                    }
                }
            }
            else
            {
                $new_lc_id = $resource->type; 
            
            }
            $liveclassroom->course = backup_todb($restore->course_id);
            $liveclassroom->type = backup_todb($new_lc_id);
            $liveclassroom->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
            $liveclassroom->section = backup_todb($info['MOD']['#']['SECTION']['0']['#']);
            $liveclassroom->timemodified = backup_todb($info['MOD']['#']['TIMEMODIFIED']['0']['#']);
            $liveclassroom->timemodified = backup_todb($info['MOD']['#']['TIMEMODIFIED']['0']['#']);
            $liveclassroom->fromid = $info['MOD']['#']['TYPE']['0']['#'];
            $liveclassroom->copy_content = $copy_content;
            $liveclassroom->isfirst = 1;
            //The structure is equal to the db, so insert the liveclassroom
            $newid = insert_record ("liveclassroom",$liveclassroom);

            //Do some output     
            if (!defined('RESTORE_SILENTLY')) {
                echo "<li>".get_string("modulename","liveclassroom")." \"".format_string(stripslashes($liveclassroom->name),true)."\"</li>";
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
    //liveclassroom_decode_content_links_caller() function in each module
    //in the restore process
    function liveclassroom_decode_content_links ($content,$restore) {
            
        global $CFG;
            
        $result = $content;
                
        //Link to the list of liveclassrooms
                
        $searchstring='/\$@(liveclassroomINDEX)\*([0-9]+)@\$/';
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
                $searchstring='/\$@(liveclassroomINDEX)\*('.$old_id.')@\$/';
                //If it is a link to this course, update the link to its new location
                if($rec->new_id) {
                    //Now replace it
                    $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/liveclassroom/index.php?id='.$rec->new_id,$result);
                } else { 
                    //It's a foreign link so leave it as original
                    $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/liveclassroom/index.php?id='.$old_id,$result);
                }
            }
        }

        //Link to liveclassroom view by moduleid

        $searchstring='/\$@(liveclassroomVIEWBYID)\*([0-9]+)@\$/';
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
                $searchstring='/\$@(liveclassroomVIEWBYID)\*('.$old_id.')@\$/';
                //If it is a link to this course, update the link to its new location
                if($rec->new_id) {
                    //Now replace it
                    $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/liveclassroom/view.php?id='.$rec->new_id,$result);
                } else {
                    //It's a foreign link so leave it as original
                    $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/liveclassroom/view.php?id='.$old_id,$result);
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
    function liveclassroom_decode_content_links_caller($restore) {
        global $CFG;
        $status = true;
        
        if ($liveclassrooms = get_records_sql ("SELECT c.id, c.intro
                                   FROM {$CFG->prefix}liveclassroom c
                                   WHERE c.course = $restore->course_id")) {
                                               //Iterate over each liveclassroom->intro
            $i = 0;   //Counter to send some output to the browser to avoid timeouts
            foreach ($liveclassrooms as $liveclassroom) {
                //Increment counter
                $i++;
                $content = $liveclassroom->intro;
                $result = restore_decode_content_links_worker($content,$restore);
                if ($result != $content) {
                    //Update record
                    $liveclassroom->intro = addslashes($result);
                    $status = update_record("liveclassroom",$liveclassroom);
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
