<?php  //$Id: upgrade.php,v 1.9.2.1 2008/01/27 15:34:29 stronk7 Exp $

// This file keeps track of upgrades to 
// the lesson module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php


function xmldb_voicetools_upgrade($oldversion=0) {
    
    $result = true;
   
    
    //we create the default table voicetools to match the moodle requirment
    if($oldversion < 2009060100)//have to be done for older version than 3.3.3
    {
        $table = new XMLDBTable('voicetools');
        
       /// Adding fields to table voicetools
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('course', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '255', false, XMLDB_NOTNULL, null, null, null, '');
        
       /// Adding primary key to table voicetools
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
               
       /// Launch create table for termreview_alis
        $result = $result && create_table($table);
    }
    
    //upgrade for older version than 3.3
    if ($oldversion < 2009010500) 
    {
        $product=array("Voice Board" => "board",
                       "Voice Presentation" => "presentation",
                       "Podcaster" => "pc");
        $table_name=array(
                        "Voice Board" => "voiceboard",
                        "Voice Presentation" => "voicepresentation",
                        "Podcaster" => "voicepodcaster");
        
        $table_resources_name=array(
                        "board" => "voiceboard_resources",
                        "presentation" => "voicepresentation_resources",
                        "pc" => "voicepodcaster_resources",
                        "recorder" => "voiceauthoring_resources");
        
        $modules_info=array(
                        "Voice Board" => get_record("modules","name","voiceboard"),
                        "Voice Presentation" => get_record("modules","name","voicepresentation"),
                        "Podcaster" => get_record("modules","name","voicepodcaster"));
        
        $voicetools_module_info=get_record("modules","name","voicetools"); 
        $courseToRebuild=array();
        $voicetools=get_records("voicetools");//get all the activity "voicetools" created
   
        
        foreach(array_keys($voicetools) as $i) 
        {
            $id = $voicetools[$i]->id;
            $name = explode(" - ",$voicetools[$i]->name);  
            
            if(isset($table_name[$name[0]]))
            {
                $voicetools[$i]->isfirst = 1;
                $voicetools[$i]->name = addslashes($voicetools[$i]->name);
                $newInstanceNumber=insert_record($table_name[$name[0]], $voicetools[$i]);
               
            }
            //the course structure has to be updated, the module id has to be the new one
            $old_voicetools_instance=get_coursemodule_from_instance("voicetools",$id);
               
            if(isset($old_voicetools_instance))
            {
                $old_voicetools_instance->module = $modules_info[$name[0]]->id; 
                $old_voicetools_instance->instance = $newInstanceNumber;   
       
                update_record("course_modules",$old_voicetools_instance);
                $courseToRebuild[]= $voicetools[$i]->course;
            }
        }
              
        for($i=0;$i<count($courseToRebuild);$i++) 
        {
            rebuild_course_cache($courseToRebuild[$i]);//the course need to be rebuild due to the name changes
        }

        //now we copy, the @MODULE_NAME@ resource to the good table
        $voicetools_resources=get_records("voicetools_resources");
        
        foreach(array_keys($voicetools_resources) as $j) 
        {
            if(isset($table_resources_name[$voicetools_resources[$j]->type]))
            {
                $result = insert_record($table_resources_name[$voicetools_resources[$j]->type] , $voicetools_resources[$j]);  
            }
        }
        
        $voicetools_resources=get_records("voicetools_recorder");
        
        foreach(array_keys($voicetools_resources) as $j) 
        {
                insert_record("voiceauthoring_block" , $voicetools_resources[$j]);
        }
    
    
        
    }
    
    if (empty($CFG->voicetools_initialdisable)) {
         set_field('modules', 'visible', 0, 'name', 'voicetools');  // Disable it by default               
         set_config('voicetools_initialdisable', 1);
    }
    else if ($CFG->voicetools_initialdisable == 1) {//we make sure that the voicetools module is still disabled
         set_field('modules', 'visible', 0, 'name', 'voicetools');  // Disable it by default
    }
    return $result;
}

?>
