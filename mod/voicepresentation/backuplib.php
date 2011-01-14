<?php //$Id: backuplib.php 66850 2008-08-19 23:30:41Z thomasr $
    //This php script contains all the stuff to backup/restore
    //chat mods

    //This is the "graphical" structure of the chat mod:
    //
    //                       voiceauthoring
    //                    (CL,pk->id)             
    //                        |
    //                        |
    //                        |
    //                   voiceauthoring_messages 
    //               (UL,pk->id, fk->voiceauthoringid)
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------

require_once($CFG->dirroot.'/mod/voicepresentation/lib.php');
  //This function executes all the backup procedure about this mod
    function voicepresentation_backup_mods($bf,$preferences) {

        global $CFG;

        $status = true;

        //Iterate over voicepresentation table
        $voicefeatures = get_records ("voicepresentation","course",$preferences->backup_course,"id");
        if ($voicefeatures) {
            foreach ($voicefeatures as $voicefeature) {
                if (backup_mod_selected($preferences,'voicepresentation',$voicefeature->id)) {
                    $status = voicepresentation_backup_one_mod($bf,$preferences,$voicefeature);
                }
            }
        }
        return $status;  
    }

    function voicepresentation_backup_one_mod($bf,$preferences,$voicefeature) {

        global $CFG;
    
        if (is_numeric($voicefeature)) {
            $voicefeature = get_record('voicepresentation','id',$voicefeature);
        }
    
        $status = true;
        
            
        //Start mod
        fwrite ($bf,start_tag("MOD",3,true));
        //Print voicepresentation data
        fwrite ($bf,full_tag("ID",4,false,$voicefeature->id));
        fwrite ($bf,full_tag("RID",4,false, $voicefeature->rid));
       
        fwrite ($bf,full_tag("MODTYPE",4,false,"voicepresentation"));
        fwrite ($bf,full_tag("COURSE",4,false,$voicefeature->course));
        fwrite ($bf,full_tag("NAME",4,false,$voicefeature->name));
   
        fwrite ($bf,full_tag("TYPE",4,false,$voicefeature->type));
        fwrite ($bf,full_tag("SECTION",4,false,$voicefeature->section));
        fwrite ($bf,full_tag("TIMEMODIFIED",4,false,$voicefeature->timemodified));
        $status =fwrite ($bf,end_tag("MOD",3,true));

        return $status;
    }

    //Backup voicepresentation_messages contents (executed from voicepresentation_backup_mods)


    //Return an array of info (name,value)
    function voicepresentation_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {

        if (!empty($instances) && is_array($instances) && count($instances)) {
            $info = array();
            foreach ($instances as $id => $instance) {
                $info += voicepresentation_check_backup_mods_instances($instance,$backup_unique_code);
            }
            return $info;
        }
        //First the course data
        $info[0][0] = get_string("modulenameplural","voicepresentation");
        if ($ids = voicepresentation_ids ($course)) {
            $info[0][1] = count($ids);
        } else {
            $info[0][1] = 0;
        }

        //Now, if requested, the user_data
      //  if ($user_data) {
        //    $info[1][0] = get_string("messages","voicepresentation");
         //   if ($ids = voicepresentation_message_ids_by_course ($course)) { 
           //     $info[1][1] = count($ids);
           // } else {
             //   $info[1][1] = 0;
            //}
        //}
        return $info;
    }

    //Return an array of info (name,value)
    function voicepresentation_check_backup_mods_instances($instance,$backup_unique_code) {
        //First the course data
        $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
        $info[$instance->id.'0'][1] = '';

        //Now, if requested, the user_data
   //     if (!empty($instance->userdata)) {
     //       $info[$instance->id.'1'][0] = get_string("messages","voicepresentation");
       ///     if ($ids = voicepresentation_message_ids_by_instance ($instance->id)) { 
          //      $info[$instance->id.'1'][1] = count($ids);
          //  } else {
            //    $info[$instance->id.'1'][1] = 0;
           // }
      //  }
        return $info;
    }

    //Return a content encoded to support interactivities linking. Every module
    //should have its own. They are called automatically from the backup procedure.
    function voicepresentation_encode_content_links ($content,$preferences) {

        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        //Link to the list of voicepresentations
        $buscar="/(".$base."\/mod\/voicepresentation\/index.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@voicepresentationINDEX*$2@$',$content);

        //Link to voicepresentation view by moduleid
        $buscar="/(".$base."\/mod\/voicepresentation\/view.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@voicepresentationVIEWBYID*$2@$',$result);

        return $result;
    }

    // INTERNAL FUNCTIONS. BASED IN THE MOD STRUCTURE

    //Returns an array of voicepresentations id 
    function voicepresentation_ids ($course) {

        global $CFG;

        return get_records_sql ("SELECT c.id, c.course
                                 FROM {$CFG->prefix}voicepresentation c
                                 WHERE c.course = '$course'");
    }
    
   
?>
