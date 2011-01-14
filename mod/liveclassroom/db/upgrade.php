<?php  

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


function xmldb_liveclassroom_upgrade($oldversion=0) {
    
    $result = true;
    if($oldversion < 20080011001)
    {
        $table = new XMLDBTable('liveclassroom');
        
        $field = new XMLDBField('isfirst');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 1);
        
        $result = $result && add_field($table, $field);    
        
        $field = new XMLDBField('fromid');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', false, XMLDB_NOTNULL, null, null, null, '');
        

        $result = $result && add_field($table, $field);

        $field = new XMLDBField('copy_content');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0);
        
        $result = $result && add_field($table, $field);    
        
    
    }
    return $result;
}

?>
