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


function xmldb_voiceboard_upgrade($oldversion=0) {
    
    $result = true;
     
    //we create the default table voicetools to match the moodle requirment
    if($oldversion < 2009082500)//have to be done for older version than 3.3.3
    {
        $table = new XMLDBTable('voiceboard_resources');
        
       /// Adding fields to table voicetools
      
        $field = new XMLDBField('gradeid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, -1);      
               
       /// Launch create table for termreview_alis
        $result = $result && add_field($table, $field);    
    }
    
   
    return $result;
}

?>
