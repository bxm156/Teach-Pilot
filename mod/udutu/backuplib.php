<?php //$Id: backuplib.php,v 1.18.4.1 2007/03/23 10:53:24 csantossaenz Exp $
    //This php script contains all the stuff to backup/restore
    //udutu mods

    //This is the "graphical" structure of the udutu mod:
    //
    //                      udutu                                      
    //                   (CL,pk->id)-------------------------------------
    //                        |                                         |
    //                        |                                         |
    //                        |                                         |
    //                   udutu_scoes               udutu_scoes_data     |
    //             (UL,pk->id, fk->udutu)-------(UL,pk->id, fk->scoid)  |
    //                        |                                         |
    //                        |                                         |
    //                        |                                         |
    //                udutu_scoes_track                                 |
    //  (UL,k->id, fk->udutuid, fk->scoid, k->element)-------------------
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------

    function udutu_backup_mods($bf,$preferences) {
        
        global $CFG;

        $status = true;

        //Iterate over udutu table
        $udutus = get_records ('udutu','course',$preferences->backup_course,'id');
        if ($udutus) {
            foreach ($udutus as $udutu) {
                if (backup_mod_selected($preferences,'udutu',$udutu->id)) {
                    $status = udutu_backup_one_mod($bf,$preferences,$udutu);
                }
            }
        }
        return $status;
    }

    function udutu_backup_one_mod($bf,$preferences,$udutu) {
        $status = true;

        if (is_numeric($udutu)) {
            $udutu = get_record('udutu','id',$udutu);
        }

        //Start mod
        fwrite ($bf,start_tag('MOD',3,true));
        //Print udutu data
        fwrite ($bf,full_tag('ID',4,false,$udutu->id));
        fwrite ($bf,full_tag('MODTYPE',4,false,'udutu'));
        fwrite ($bf,full_tag('NAME',4,false,$udutu->name));
        fwrite ($bf,full_tag('REFERENCE',4,false,$udutu->reference));
        fwrite ($bf,full_tag('VERSION',4,false,$udutu->version));
        fwrite ($bf,full_tag('MAXGRADE',4,false,$udutu->maxgrade));
        fwrite ($bf,full_tag('GRADEMETHOD',4,false,$udutu->grademethod));
        fwrite ($bf,full_tag('LAUNCH',4,false,$udutu->launch));
        fwrite ($bf,full_tag('SKIPVIEW',4,false,$udutu->skipview));
        fwrite ($bf,full_tag('SUMMARY',4,false,$udutu->summary));
        fwrite ($bf,full_tag('HIDEBROWSE',4,false,$udutu->hidebrowse));
        fwrite ($bf,full_tag('HIDETOC',4,false,$udutu->hidetoc));
        fwrite ($bf,full_tag('HIDENAV',4,false,$udutu->hidenav));
        fwrite ($bf,full_tag('AUTO',4,false,$udutu->auto));
        fwrite ($bf,full_tag('POPUP',4,false,$udutu->popup));
        fwrite ($bf,full_tag('OPTIONS',4,false,$udutu->options));
        fwrite ($bf,full_tag('WIDTH',4,false,$udutu->width));
        fwrite ($bf,full_tag('HEIGHT',4,false,$udutu->height));
        fwrite ($bf,full_tag('TIMEMODIFIED',4,false,$udutu->timemodified));
        $status = backup_udutu_scoes($bf,$preferences,$udutu->id);
        
        //if we've selected to backup users info, then execute backup_udutu_scoes_track
        if ($status) {
            if (backup_userdata_selected($preferences,'udutu',$udutu->id)) {
                $status = backup_udutu_scoes_track($bf,$preferences,$udutu->id);
			}
                $status = backup_udutu_files_instance($bf,$preferences,$udutu->id);
            

        }
        //End mod
        $status =fwrite ($bf,end_tag('MOD',3,true));
        return $status;
    }

    //Backup udutu_scoes contents (executed from udutu_backup_mods)
    function backup_udutu_scoes ($bf,$preferences,$udutu) {

        global $CFG;

        $status = true;

        $udutu_scoes = get_records('udutu_scoes','udutu',$udutu,'id');
        //If there is scoes
        if ($udutu_scoes) {
            //Write start tag
            $status =fwrite ($bf,start_tag('SCOES',4,true));
            //Iterate over each sco
            foreach ($udutu_scoes as $sco) {
                //Start sco
                $status =fwrite ($bf,start_tag('SCO',5,true));
                //Print submission contents
                fwrite ($bf,full_tag('ID',6,false,$sco->id));
                fwrite ($bf,full_tag('MANIFEST',6,false,$sco->manifest));
                fwrite ($bf,full_tag('ORGANIZATION',6,false,$sco->organization));
                fwrite ($bf,full_tag('PARENT',6,false,$sco->parent));
                fwrite ($bf,full_tag('IDENTIFIER',6,false,$sco->identifier));
                fwrite ($bf,full_tag('LAUNCH',6,false,$sco->launch));
                fwrite ($bf,full_tag('udutuTYPE',6,false,$sco->udututype));
                fwrite ($bf,full_tag('TITLE',6,false,$sco->title));
                $status = backup_udutu_scoes_data($bf,$preferences,$sco->id);
                //End sco
                $status =fwrite ($bf,end_tag('SCO',5,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag('SCOES',4,true));
        }
        return $status;
    }
  
   //Backup udutu_scoes_data contents (executed from udutu_backup_udutu_scoes)
    function backup_udutu_scoes_data ($bf,$preferences,$sco) {

        global $CFG;

        $status = true;

        $udutu_sco_datas = get_records('udutu_scoes_data','scoid',$sco,'id');
        //If there is data
        if ($udutu_sco_datas) {
            //Write start tag
            $status =fwrite ($bf,start_tag('SCO_DATAS',4,true));
            //Iterate over each sco
            foreach ($udutu_sco_datas as $sco_data) {
                //Start sco track
                $status =fwrite ($bf,start_tag('SCO_DATA',5,true));
                //Print track contents
                fwrite ($bf,full_tag('ID',6,false,$sco_data->id));
                fwrite ($bf,full_tag('NAME',6,false,$sco_data->name));
                fwrite ($bf,full_tag('VALUE',6,false,$sco_data->value));
                //End sco track
                $status =fwrite ($bf,end_tag('SCO_DATA',5,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag('SCO_DATAS',4,true));
        }
        return $status;
    }
   
   //Backup udutu_scoes_track contents (executed from udutu_backup_mods)
    function backup_udutu_scoes_track ($bf,$preferences,$udutu) {
	  Echo 'backup';
        global $CFG;

        $status = true;

        $udutu_scoes_track = get_records('udutu_scoes_track','udutuid',$udutu,'id');
        //If there is track
        if ($udutu_scoes_track) {
            //Write start tag
            $status =fwrite ($bf,start_tag('SCO_TRACKS',4,true));
            //Iterate over each sco
            foreach ($udutu_scoes_track as $sco_track) {
                //Start sco track
                $status =fwrite ($bf,start_tag('SCO_TRACK',5,true));
                //Print track contents
                fwrite ($bf,full_tag('ID',6,false,$sco_track->id));
                fwrite ($bf,full_tag('USERID',6,false,$sco_track->userid));
                fwrite ($bf,full_tag('SCOID',6,false,$sco_track->scoid));
                fwrite ($bf,full_tag('ELEMENT',6,false,$sco_track->element));
                fwrite ($bf,full_tag('VALUE',6,false,$sco_track->value));
                //End sco track
                $status =fwrite ($bf,end_tag('SCO_TRACK',5,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag('SCO_TRACKS',4,true));
        }
        return $status;
    }
   
   ////Return an array of info (name,value)
   function udutu_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {
       if (!empty($instances) && is_array($instances) && count($instances)) {
           $info = array();
           foreach ($instances as $id => $instance) {
               $info += udutu_check_backup_mods_instances($instance,$backup_unique_code);
           }
           return $info;
       }
        //First the course data
        $info[0][0] = get_string('modulenameplural','udutu');
        if ($ids = udutu_ids ($course)) {
            $info[0][1] = count($ids);
        } else {
            $info[0][1] = 0;
        }

        //Now, if requested, the user_data
        if ($user_data) {
            $info[1][0] = get_string('scoes','udutu');
            if ($ids = udutu_scoes_track_ids_by_course ($course)) {
                $info[1][1] = count($ids);
            } else {
                $info[1][1] = 0;
            }
        }
        return $info;
    }

    function udutu_check_backup_mods_instances($instance,$backup_unique_code) {
        $info[$instance->id.'0'][0] = $instance->name;
        $info[$instance->id.'0'][1] = '';
        if (!empty($instance->userdata)) {
            $info[$instance->id.'1'][0] = get_string('scoes','udutu');
            if ($ids = udutu_scoes_track_ids_by_instance ($instance->id)) {
                $info[$instance->id.'1'][1] = count($ids);
            } else {
                $info[$instance->id.'1'][1] = 0;
            }
        }

        return $info;

    }

    function backup_udutu_files_instance($bf,$preferences,$instanceid) {
        global $CFG;

        $status = true;

        //First we check to moddata exists and create it as necessary
        //in temp/backup/$backup_code  dir
        $status = check_and_create_moddata_dir($preferences->backup_unique_code);
        $status = check_dir_exists($CFG->dataroot.'/temp/backup/'.$preferences->backup_unique_code.'/moddata/udutu/',true);
        if ($status) {
            //Only if it exists !! Thanks to Daniel Miksik.
            if (is_dir($CFG->dataroot.'/'.$preferences->backup_course.'/'.$CFG->moddata.'/udutu/'.$instanceid)) {
                $status = backup_copy_file($CFG->dataroot.'/'.$preferences->backup_course.'/'.$CFG->moddata.'/udutu/'.$instanceid,
                                           $CFG->dataroot.'/temp/backup/'.$preferences->backup_unique_code.'/moddata/udutu/'.$instanceid);
            }
        }

        return $status;
    }


    //Backup udutu package files
    function backup_udutu_files($bf,$preferences) {

        global $CFG;

        $status = true;

        //First we check to moddata exists and create it as necessary
        //in temp/backup/$backup_code  dir
        $status = check_and_create_moddata_dir($preferences->backup_unique_code);
        //Now copy the udutu dir
        if ($status) {
            if (is_dir($CFG->dataroot.'/'.$preferences->backup_course.'/'.$CFG->moddata.'/udutu')) {
                $handle = opendir($CFG->dataroot.'/'.$preferences->backup_course.'/'.$CFG->moddata.'/udutu');
                while (false!==($item = readdir($handle))) {
                    if ($item != '.' && $item != '..' && is_dir($CFG->dataroot.'/'.$preferences->backup_course.'/'.$CFG->moddata.'/udutu/'.$item)
                        && array_key_exists($item,$preferences->mods['udutu']->instances)
                        && !empty($preferences->mods['udutu']->instances[$item]->backup)) {
                        $status = backup_copy_file($CFG->dataroot.'/'.$preferences->backup_course.'/'.$CFG->moddata.'/udutu/'.$item,
                                                   $CFG->dataroot.'/temp/backup/'.$preferences->backup_unique_code.'/moddata/udutu/',$item);
                    }
                }
            }
        }

        return $status;

    }

    //Return a content encoded to support interactivities linking. Every module
    //should have its own. They are called automatically from the backup procedure.
    function udutu_encode_content_links ($content,$preferences) {

        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        //Link to the list of udutus
        $buscar="/(".$base."\/mod\/udutu\/index.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@udutuINDEX*$2@$',$content);

        //Link to udutu view by moduleid
        $buscar="/(".$base."\/mod\/udutu\/view.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@udutuVIEWBYID*$2@$',$result);

        return $result;
    }

    // INTERNAL FUNCTIONS. BASED IN THE MOD STRUCTURE

    //Returns an array of udutus id
    function udutu_ids ($course) {

        global $CFG;

        return get_records_sql ("SELECT a.id, a.course
                                 FROM {$CFG->prefix}udutu a
                                 WHERE a.course = '$course'");
    }
   
    //Returns an array of udutu_scoes id
    function udutu_scoes_track_ids_by_course ($course) {

        global $CFG;

        return get_records_sql ("SELECT s.id , s.udutuid
                                 FROM {$CFG->prefix}udutu_scoes_track s,
                                      {$CFG->prefix}udutu a
                                 WHERE a.course = '$course' AND
                                       s.udutuid = a.id");
    }

    //Returns an array of udutu_scoes id
    function udutu_scoes_track_ids_by_instance ($instanceid) {

        global $CFG;

        return get_records_sql ("SELECT s.id , s.udutuid
                                 FROM {$CFG->prefix}udutu_scoes_track s
                                 WHERE s.udutuid = $instanceid");
    }
?>
