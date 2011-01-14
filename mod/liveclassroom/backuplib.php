<?php //$Id: backuplib.php 67406 2008-09-09 11:56:39Z thomasr $
    //This php script contains all the stuff to backup/restore
    //chat mods

    //This is the "graphical" structure of the chat mod:
    //
    //                       liveclassroom
    //                    (CL,pk->id)             
    //                        |
    //                        |
    //                        |
    //                   liveclassroom_messages 
    //               (UL,pk->id, fk->liveclassroomid)
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------

require_once($CFG->dirroot.'/mod/liveclassroom/lib.php');
    //This function executes all the backup procedure about this mod
    function liveclassroom_backup_mods($bf,$preferences) {

        global $CFG;

        $status = true;

        //Iterate over liveclassroom table
        $liveclassrooms = get_records ("liveclassroom","course",$preferences->backup_course,"id");
        if ($liveclassrooms) {
            foreach ($liveclassrooms as $liveclassroom) {
                if (backup_mod_selected($preferences,'liveclassroom',$liveclassroom->id)) {
                    $status = liveclassroom_backup_one_mod($bf,$preferences,$liveclassroom);
                }
            }
        }
        return $status;  
    }

    function liveclassroom_backup_one_mod($bf,$preferences,$liveclassroom) {

        global $CFG;
    
        if (is_numeric($liveclassroom)) {
            $liveclassroom = get_record('liveclassroom','id',$liveclassroom);
        }
    
        $status = true;
        $lcAction = new LCAction(null,$CFG->liveclassroom_servername,
                     $CFG->liveclassroom_adminusername,
                     $CFG->liveclassroom_adminpassword,null,$liveclassroom->course); 
        $roomPreview = $lcAction->getRoomPreview($liveclassroom->type) ;
        
            
        //Start mod
        fwrite ($bf,start_tag("MOD",3,true));
        //Print liveclassroom data
        fwrite ($bf,full_tag("ID",4,false,$liveclassroom->id));
        fwrite ($bf,full_tag("MODTYPE",4,false,"liveclassroom"));
        fwrite ($bf,full_tag("COURSE",4,false,$liveclassroom->course));
        fwrite ($bf,full_tag("NAME",4,false,$liveclassroom->name));
        
        if($lcAction->isStudentAdmin($liveclassroom->course, $liveclassroom->course.'_S') == "true")
        {
             fwrite ($bf,full_tag("ISSTUDENTADMIN",4,false,"true"));
        }
        else
        {  
             fwrite ($bf,full_tag("ISSTUDENTADMIN",4,false,"false"));
        }
        fwrite ($bf,full_tag("PREVIEW",4,false, $roomPreview));
        fwrite ($bf,full_tag("TYPE",4,false,$liveclassroom->type));
        fwrite ($bf,full_tag("SECTION",4,false,$liveclassroom->section));
        fwrite ($bf,full_tag("TIMEMODIFIED",4,false,$liveclassroom->timemodified));
        $status =fwrite ($bf,end_tag("MOD",3,true));

        return $status;
    }

    //Backup liveclassroom_messages contents (executed from liveclassroom_backup_mods)


    //Return an array of info (name,value)
    function liveclassroom_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {

        if (!empty($instances) && is_array($instances) && count($instances)) {
            $info = array();
            foreach ($instances as $id => $instance) {
                $info += liveclassroom_check_backup_mods_instances($instance,$backup_unique_code);
            }
            return $info;
        }
        //First the course data
        $info[0][0] = get_string("modulenameplural","liveclassroom");
        if ($ids = liveclassroom_ids ($course)) {
            $info[0][1] = count($ids);
        } else {
            $info[0][1] = 0;
        }

        return $info;
    }

    //Return an array of info (name,value)
    function liveclassroom_check_backup_mods_instances($instance,$backup_unique_code) {
        //First the course data
        $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
        $info[$instance->id.'0'][1] = '';


        return $info;
    }

    //Return a content encoded to support interactivities linking. Every module
    //should have its own. They are called automatically from the backup procedure.
    function liveclassroom_encode_content_links ($content,$preferences) {

        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        //Link to the list of liveclassrooms
        $buscar="/(".$base."\/mod\/liveclassroom\/index.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@liveclassroomINDEX*$2@$',$content);

        //Link to liveclassroom view by moduleid
        $buscar="/(".$base."\/mod\/liveclassroom\/view.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@liveclassroomVIEWBYID*$2@$',$result);

        return $result;
    }

    // INTERNAL FUNCTIONS. BASED IN THE MOD STRUCTURE

    //Returns an array of liveclassrooms id 
    function liveclassroom_ids ($course) {

        global $CFG;

        return get_records_sql ("SELECT c.id, c.course
                                 FROM {$CFG->prefix}liveclassroom c
                                 WHERE c.course = '$course'");
    }
    


                     
   
?>
