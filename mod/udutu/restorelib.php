<?php //$Id: restorelib.php,v 1.27 2006/12/05 18:34:29 tjhunt Exp $
    //This php script contains all the stuff to backup/restore
    //reservation mods

    //This is the "graphical" structure of the udutu mod:
    //
    //                      udutu
    //                   (CL,pk->id)---------------------
    //                        |                         |
    //                        |                         |
    //                        |                         |
    //                   udutu_scoes                    |
    //             (UL,pk->id, fk->udutu)               |
    //                        |                         |
    //                        |                         |
    //                        |                         |
    //                udutu_scoes_track                 |
    //  (UL,pk->id, fk->udutuid, fk->scoid, fk->userid)--
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------

    //This function executes all the restore procedure about this mod
    function udutu_restore_mods($mod,$restore) {

        global $CFG;

        $status = true;

        //Get record from backup_ids
        $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);

        if ($data) {
            //Now get completed xmlized object
            $info = $data->info;
            //traverse_xmlize($info);                                                                     //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //Now, build the udutu record structure
            $udutu->course = $restore->course_id;
            $udutu->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
            $udutu->reference = backup_todb($info['MOD']['#']['REFERENCE']['0']['#']);
            $udutu->version = backup_todb($info['MOD']['#']['VERSION']['0']['#']);
            $udutu->maxgrade = backup_todb($info['MOD']['#']['MAXGRADE']['0']['#']);
            if (!is_int($udutu->maxgrade)) {
                $udutu->maxgrade = 0;
            }
            $udutu->grademethod = backup_todb($info['MOD']['#']['GRADEMETHOD']['0']['#']);
            if (!is_int($udutu->grademethod)) {
                $udutu->grademethod = 0;
            }
            if ($restore->backup_version < 2005041500) {
                $udutu->datadir = substr(backup_todb($info['MOD']['#']['DATADIR']['0']['#']),1);
            } else {
                $udutu->datadir = backup_todb($info['MOD']['#']['ID']['0']['#']);
            }
            $oldlaunch = backup_todb($info['MOD']['#']['LAUNCH']['0']['#']);
            if ($restore->backup_version < 2006102600) {
                $udutu->skipview = 1;
            } else {
                $udutu->skipview = backup_todb($info['MOD']['#']['SKIPVIEW']['0']['#']);
            }
            $udutu->summary = backup_todb($info['MOD']['#']['SUMMARY']['0']['#']);
            $udutu->hidebrowse = backup_todb($info['MOD']['#']['HIDEBROWSE']['0']['#']);
            $udutu->hidetoc = backup_todb($info['MOD']['#']['HIDETOC']['0']['#']);
            $udutu->hidenav = backup_todb($info['MOD']['#']['HIDENAV']['0']['#']);
            $udutu->auto = backup_todb($info['MOD']['#']['AUTO']['0']['#']);
            if ($restore->backup_version < 2005040200) {
                $oldpopup = backup_todb($info['MOD']['#']['POPUP']['0']['#']);
                if (!empty($oldpopup)) {
                    $udutu->popup = 1;
                    // Parse old popup field
                    $options = array();
                    $oldoptions = explode(',',$udutu->popup);
                    foreach ($oldoptions as $oldoption) {
                        list($element,$value) = explode('=',$oldoption);
                        $element = trim($element);
                        $value = trim($value); 
                        switch ($element) {
                            case 'width':
                                $udutu->width = $value;
                            break;
                            case 'height':
                                $udutu->height = $value;
                            break;
                            default:
                                $options[] = $element.'='.$value;
                            break;
                        }
                    }
                    $udutu->options = implode($options,',');
                } else {
                    $udutu->popup = 0;
                    $udutu->options = '';
                    $udutu->width = '100%';
                    $udutu->height = 500;
                }
            } else {
                $udutu->popup = backup_todb($info['MOD']['#']['POPUP']['0']['#']);
                $udutu->width = backup_todb($info['MOD']['#']['WIDTH']['0']['#']);
                if ($udutu->width == 0) {
                    $udutu->width = '100%';
                }
                $udutu->height = backup_todb($info['MOD']['#']['HEIGHT']['0']['#']);
                if ($udutu->height == 0) {
                    $udutu->height = 500;
                }
            }
            $udutu->timemodified = time();

            //The structure is equal to the db, so insert the udutu
            $newid = insert_record ("udutu",$udutu);
            //Do some output
            if (!defined('RESTORE_SILENTLY')) {
                echo "<li>".get_string("modulename","udutu")." \"".format_string(stripslashes($udutu->name),true)."\"</li>";
            }
            backup_flush(300);

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code,$mod->modtype,
                             $mod->id, $newid);
                $udutu->id = $newid;
                //Now copy moddata associated files
                $status = udutu_restore_files ($udutu, $restore);

                if ($status) {
                    $status = udutu_scoes_restore_mods ($newid,$info,$restore,$mod->id);
                    if ($status) {
                        $launchsco = backup_getid($restore->backup_unique_code,"udutu_scoes",$oldlaunch);
                        $udutu->launch = $launchsco->new_id;
                        update_record('udutu',$udutu);
                    }
                } 
                
            } else {
                $status = false;
            }
        } else {
            $status = false;
        }

        return $status;
    }

    //This function restores the udutu_scoes
    function udutu_scoes_restore_mods($udutu_id,$info,$restore,$oldmodid) {
    
        global $CFG;

        $status = true;

        //Get the sco array
        $scoes = $info['MOD']['#']['SCOES']['0']['#']['SCO'];

        //Iterate over scoes
        for($i = 0; $i < sizeof($scoes); $i++) {
            $sub_info = $scoes[$i];

            //We'll need this later!!
            $oldid = backup_todb($sub_info['#']['ID']['0']['#']);

            //Now, build the udutu_scoes record structure
            $sco->udutu = $udutu_id;
            $sco->manifest = backup_todb($sub_info['#']['MANIFEST']['0']['#']);
            $sco->organization = backup_todb($sub_info['#']['ORGANIZATION']['0']['#']);
            $sco->parent = backup_todb($sub_info['#']['PARENT']['0']['#']);
            $sco->identifier = backup_todb($sub_info['#']['IDENTIFIER']['0']['#']);
            $sco->launch = backup_todb($sub_info['#']['LAUNCH']['0']['#']);
            $sco->title = backup_todb($sub_info['#']['TITLE']['0']['#']);
            if ($restore->backup_version < 2005031300) {
                $sco->udututype = backup_todb($sub_info['#']['TYPE']['0']['#']);
            } else {
                $sco->udututype = backup_todb($sub_info['#']['udutuTYPE']['0']['#']);
            }

            //The structure is equal to the db, so insert the udutu_scoes
            $newid = insert_record ("udutu_scoes",$sco);
            
            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code,"udutu_scoes", $oldid, $newid);
            } else {
                $status = false;
            }
        }

        //Now check if want to restore user data and do it.
        if (restore_userdata_selected($restore,'udutu',$oldmodid)) {
            //Restore udutu_scoes
            if ($status) {
                if ($restore->backup_version < 2005031300) {
                    $status = udutu_scoes_tracks_restore_mods_pre15 ($udutu_id,$info,$restore);
                } else {
                    $status = udutu_scoes_tracks_restore_mods ($udutu_id,$info,$restore);
                }
            }
        }    
        
        return $status;
    }

    //This function restores the udutu_scoes_track
    function udutu_scoes_tracks_restore_mods($udutu_id,$info,$restore) {

        global $CFG;

        $status = true;
        $scotracks = NULL;

        //Get the sco array
        if (!empty($info['MOD']['#']['SCO_TRACKS']['0']['#']['SCO_TRACK']))
            $scotracks = $info['MOD']['#']['SCO_TRACKS']['0']['#']['SCO_TRACK'];

        //Iterate over sco_users
        for($i = 0; $i < sizeof($scotracks); $i++) {
            $sub_info = $scotracks[$i];
            unset($scotrack);

            //Now, build the udutu_scoes_track record structure
            $scotrack->udutuid = $udutu_id;
            $scotrack->userid = backup_todb($sub_info['#']['USERID']['0']['#']);
            $scotrack->scoid = backup_todb($sub_info['#']['SCOID']['0']['#']);
            $scotrack->element = backup_todb($sub_info['#']['ELEMENT']['0']['#']);
            $scotrack->value = backup_todb($sub_info['#']['VALUE']['0']['#']);

            //We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code,"user",$scotrack->userid);
            if (!empty($user)) {
                $scotrack->userid = $user->new_id;
            }

            //We have to recode the scoid field
            $sco = backup_getid($restore->backup_unique_code,"udutu_scoes",$scotrack->scoid);
            if ($sco != NULL) {
                $scotrack->scoid = $sco->new_id;
            }

            //The structure is equal to the db, so insert the udutu_scoes_track
            $newid = insert_record ("udutu_scoes_track",$scotrack);

            //Do some output
            if (($i+1) % 50 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 1000 == 0) {
                        echo "<br />";
                    }
                }
                backup_flush(300);
            }

        }

        return $status;
    }
    
    //This function restores the udutu_scoes_track from Moodle 1.4
    function udutu_scoes_tracks_restore_mods_pre15 ($udutu_id,$info,$restore) {

        global $CFG;

        $status = true;
        $scousers = NULL;

        //Get the sco array
        if (!empty($info['MOD']['#']['SCO_USERS']['0']['#']['SCO_USER'])) {
            $scousers = $info['MOD']['#']['SCO_USERS']['0']['#']['SCO_USER'];
        }
        
        $oldelements = array ('CMI_CORE_LESSON_LOCATION',
                              'CMI_CORE_LESSON_STATUS',
                              'CMI_CORE_EXIT',
                              'CMI_CORE_TOTAL_TIME',
                              'CMI_CORE_SCORE_RAW',
                              'CMI_SUSPEND_DATA');
        $newelements = array ('cmi.core.lesson_location',
                              'cmi.core.lesson_status',
                              'cmi.core.exit',
                              'cmi.core.total_time',
                              'cmi.core.score.raw',
                              'cmi.suspend_data');

        //Iterate over sco_users
        for ($i = 0; $i < sizeof($scousers); $i++) {
            $sub_info = $scousers[$i];
            unset($scotrack);

            //We'll need this later!!
            $oldid = backup_todb($sub_info['#']['ID']['0']['#']);
            $oldscoid = backup_todb($sub_info['#']['SCOID']['0']['#']);
            $olduserid = backup_todb($sub_info['#']['USERID']['0']['#']);

            //Now, build the udutu_scoes_track record structure
            $scotrack->udutuid = $udutu_id;
            $scotrack->userid = backup_todb($sub_info['#']['USERID']['0']['#']);
            $scotrack->scoid = backup_todb($sub_info['#']['SCOID']['0']['#']);
            $pos = 0;
            foreach ($oldelements as $oldelement) {
                $elementvalue = backup_todb($sub_info['#'][$oldelement]['0']['#']);
                if (!empty($elementvalue)) {
                    $scotrack->element = $newelements[$pos];
                    $scotrack->value = backup_todb($sub_info['#'][strtoupper($oldelement)]['0']['#']);

                    //We have to recode the userid field
                    $user = backup_getid($restore->backup_unique_code,"user",$scotrack->userid);
                    if (!empty($user)) {
                        $scotrack->userid = $user->new_id;
                    }

                    //We have to recode the scoid field
                    $sco = backup_getid($restore->backup_unique_code,"udutu_scoes",$scotrack->scoid);
                    if (!empty($sco)) {
                        $scotrack->scoid = $sco->new_id;
                    }

                    //The structure is equal to the db, so insert the udutu_scoes_track
                    $newid = insert_record ("udutu_scoes_track",$scotrack);
                }
                $pos++;
            }

            //Do some output
            if (($i+1) % 50 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 1000 == 0) {
                        echo "<br />";
                    }
                }
                backup_flush(300);
            }

        }

        return $status;
    }

    //This function copies the udutu related info from backup temp dir to course moddata folder,
    //creating it if needed
    function udutu_restore_files ($package, $restore) {

        global $CFG;

        $status = true;
        $todo = false;
        $moddata_path = "";
        $udutu_path = "";
        $temp_path = "";

        //First, we check to "course_id" exists and create is as necessary
        //in CFG->dataroot
        $dest_dir = $CFG->dataroot."/".$restore->course_id;
        $status = check_dir_exists($dest_dir,true);

        //First, locate course's moddata directory
        $moddata_path = $CFG->dataroot."/".$restore->course_id."/".$CFG->moddata;

        //Check it exists and create it
        $status = check_dir_exists($moddata_path,true);

        //Now, locate udutu directory
        if ($status) {
            $udutu_path = $moddata_path."/udutu";
            //Check it exists and create it
            $status = check_dir_exists($udutu_path,true);
        }

        //Now locate the temp dir we are restoring from
        if ($status) {
            $temp_path = $CFG->dataroot."/temp/backup/".$restore->backup_unique_code.
                         "/moddata/udutu/".$package->datadir;
            //Check it exists
            if (is_dir($temp_path)) {
                $todo = true;
            }
        }

        //If todo, we create the neccesary dirs in course moddata/udutu
        if ($status and $todo) {
            //Make udutu package directory path
            $this_udutu_path = $udutu_path."/".$package->id;
            $status = backup_copy_file($temp_path, $this_udutu_path);
        }

        return $status;
    }

    //Return a content decoded to support interactivities linking. Every module
    //should have its own. They are called automatically from
    //udutu_decode_content_links_caller() function in each module
    //in the restore process
    function udutu_decode_content_links ($content,$restore) {
            
        global $CFG;
            
        $result = $content;
                
        //Link to the list of udutus
                
        $searchstring='/\$@(udutuINDEX)\*([0-9]+)@\$/';
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
                $searchstring='/\$@(udutuINDEX)\*('.$old_id.')@\$/';
                //If it is a link to this course, update the link to its new location
                if($rec->new_id) {
                    //Now replace it
                    $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/udutu/index.php?id='.$rec->new_id,$result);
                } else { 
                    //It's a foreign link so leave it as original
                    $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/udutu/index.php?id='.$old_id,$result);
                }
            }
        }

        //Link to udutu view by moduleid

        $searchstring='/\$@(udutuVIEWBYID)\*([0-9]+)@\$/';
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
                $searchstring='/\$@(udutuVIEWBYID)\*('.$old_id.')@\$/';
                //If it is a link to this course, update the link to its new location
                if($rec->new_id) {
                    //Now replace it
                    $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/udutu/view.php?id='.$rec->new_id,$result);
                } else {
                    //It's a foreign link so leave it as original
                    $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/udutu/view.php?id='.$old_id,$result);
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
    function udutu_decode_content_links_caller($restore) {
        global $CFG;
        $status = true;

        if ($udutus = get_records_sql ("SELECT s.id, s.summary
                                   FROM {$CFG->prefix}udutu s
                                   WHERE s.course = $restore->course_id")) {

            $i = 0;   //Counter to send some output to the browser to avoid timeouts
            foreach ($udutus as $udutu) {
                //Increment counter
                $i++;
                $content = $udutu->summary;
                $result = restore_decode_content_links_worker($content,$restore);

                if ($result != $content) {
                    //Update record
                    $udutu->summary = addslashes($result);
                    $status = update_record("udutu",$udutu);
                    if (debugging()) {
                        if (!defined('RESTORE_SILENTLY')) {
                            echo '<br /><hr />'.htmlentities($content).'<br />changed to<br />'.htmlentities($result).'<hr /><br />';
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

    //This function returns a log record with all the necessay transformations
    //done. It's used by restore_log_module() to restore modules log.
    function udutu_restore_logs($restore,$log) {

        $status = true;

        return $status;
    }
?>
