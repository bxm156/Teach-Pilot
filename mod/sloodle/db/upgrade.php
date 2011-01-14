<?php

/**
* Database upgrade script for Moodle's db-independent XMLDB.
* @ignore
* @package sloodle
*/


// This file keeps track of upgrades to
// the sloodle module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_sloodle_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;
    $result = true;
    
    // Note: any upgrade to Sloodle 0.3 is a major process, due to the huge change of architecture.
    // As such, the only data worth preserving is the avatar table ('sloodle_users').
    
    // All other tables will be dropped and re-inserted.
    
    // Is this an upgrade from pre-0.3?
    if ($result && $oldversion < 2008052800) {
        // Drop all other tables
        echo "Dropping old tables<br/>";
        // (We can ignore failed drops)
        
    /// Drop 'sloodle' table
        $table = new XMLDBTable('sloodle');
        drop_table($table);
        
    /// Drop 'sloodle_config' table
        $table = new XMLDBTable('sloodle_config');
        drop_table($table);
        
    /// Drop 'sloodle_active_object' table
        $table = new XMLDBTable('sloodle_active_object');
        drop_table($table);
        
    /// Drop 'sloodle_classroom_setup_profile' table
        $table = new XMLDBTable('sloodle_classroom_setup_profile');
        drop_table($table);
        
    /// Drop 'sloodle_classroom_setup_profile_entry' table
        $table = new XMLDBTable('sloodle_classroom_setup_profile_entry');
        drop_table($table);
        
        
        // Insert all the new tables
        echo "Inserting new tables...<br/>";
        
        
    /// Insert 'sloodle' table
        echo " - sloodle<br/>";
        $table = new XMLDBTable('sloodle');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('course', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('type', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('intro', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $table->addIndexInfo('course', XMLDB_INDEX_NOTUNIQUE, array('course'));

        $result = $result && create_table($table);
        if (!$result) echo "error<br/>";
        
        
    /// Insert 'sloodle_controller' table
        echo " - sloodle_controller<br/>";
        $table = new XMLDBTable('sloodle_controller');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('sloodleid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('enabled', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('password', XMLDB_TYPE_CHAR, '9', null, null, null, null, null, null);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $table->addIndexInfo('sloodleid', XMLDB_INDEX_UNIQUE, array('sloodleid'));

        $result = $result && create_table($table);
        if (!$result) echo "error<br/>";
        
        
    /// Insert 'sloodle_distributor' table
        echo " - sloodle_distributor<br/>";
        $table = new XMLDBTable('sloodle_distributor');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('sloodleid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('channel', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('timeupdated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $result = $result && create_table($table);
        if (!$result) echo "error<br/>";
        
        
    /// Insert 'sloodle_distributor_entry' table
        echo " - sloodle_distributor_entry<br/>";
        $table = new XMLDBTable('sloodle_distributor_entry');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('distributorid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $result = $result && create_table($table);
        if (!$result) echo "error<br/>";
        
        
    /// Insert 'sloodle_course' table
        echo " - sloodle_course<br/>";
        $table = new XMLDBTable('sloodle_course');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('course', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('autoreg', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('autoenrol', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('loginzonepos', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('loginzonesize', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('loginzoneregion', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('loginzoneupdated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $table->addIndexInfo('course', XMLDB_INDEX_NOTUNIQUE, array('course'));

        $result = $result && create_table($table);
        if (!$result) echo "error<br/>";
        
        
    /// Insert 'sloodle_pending_avatars' table
        echo " - sloodle_pending_avatar<br/>";
        $table = new XMLDBTable('sloodle_pending_avatars');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('uuid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('avname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('lst', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('timeupdated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $table->addIndexInfo('uuid', XMLDB_INDEX_NOTUNIQUE, array('uuid'));

        $result = $result && create_table($table);
        if (!$result) echo "error<br/>";
        
        
    /// Insert 'sloodle_active_object' table
        echo " - sloodle_active_object<br/>";
        $table = new XMLDBTable('sloodle_active_object');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('controllerid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('uuid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('password', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('type', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('timeupdated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $table->addIndexInfo('uuid', XMLDB_INDEX_UNIQUE, array('uuid'));
        
        $result = $result && create_table($table);
        if (!$result) echo "error<br/>";
        
        
    /// Insert 'sloodle_object_config' table
        echo " - sloodle_object_config<br/>";
        $table = new XMLDBTable('sloodle_object_config');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('object', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('value', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $table->addIndexInfo('object-name', XMLDB_INDEX_UNIQUE, array('object', 'name'));

        $result = $result && create_table($table);
        if (!$result) echo "error<br/>";
        
        
    /// Insert 'sloodle_login_notifications' table
        echo " - sloodle_login_notifications<br/>";
        $table = new XMLDBTable('sloodle_login_notifications');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('destination', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('avatar', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('username', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('password', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $result = $result && create_table($table);
        if (!$result) echo "error<br/>";
        
        
    /// Insert 'sloodle_layout' table
        echo " - sloodle_layout<br/>";
        $table = new XMLDBTable('sloodle_layout');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('course', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('timeupdated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $table->addIndexInfo('course-name', XMLDB_INDEX_UNIQUE, array('course', 'name'));

        $result = $result && create_table($table);
        
        
        if (!$result) echo "error<br/>";
        
        
    /// Insert 'sloodle_layout_entry' table
        echo " - sloodle_layout_entry<br/>";
        $table = new XMLDBTable('sloodle_layout_entry');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('layout', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('position', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('rotation', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $table->addIndexInfo('layout', XMLDB_INDEX_NOTUNIQUE, array('layout'));

        $result = $result && create_table($table);
        if (!$result) echo "error<br/>";
        
        
    /// Insert 'sloodle_loginzone_allocation' table
        echo " - sloodle_loginzone_allocation<br/>";
        $table = new XMLDBTable('sloodle_loginzone_allocation');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('course', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('position', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $table->addIndexInfo('course', XMLDB_INDEX_NOTUNIQUE, array('course'));
        $table->addIndexInfo('userid', XMLDB_INDEX_UNIQUE, array('userid'));

        $result = $result && create_table($table);
        if (!$result) echo "error<br/>";
        
        
    /// Insert 'sloodle_user_object' table
        echo " - sloodle_user_object<br/>";
        $table = new XMLDBTable('sloodle_user_object');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('avuuid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('objuuid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('objname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('password', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('authorised', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('timeupdated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $table->addIndexInfo('objuuid', XMLDB_INDEX_UNIQUE, array('objuuid'));

        $result = $result && create_table($table);
        if (!$result) echo "error<br/>";
                
        
    /// Upgrade sloodle_users table
        echo "Upgrading sloodle_users table...<br/>";
        $table = new XMLDBTable('sloodle_users');
        
        echo " - dropping old fields<br/>";
        // Drop the loginzone fields (we don't care about success or otherwise... not all fields will be present in all versions)
        $field = new XMLDBField('loginposition');
        drop_field($table, $field);
        $field = new XMLDBField('loginpositionexpires');
        drop_field($table, $field);
        $field = new XMLDBField('loginpositionregion');
        drop_field($table, $field);
        $field = new XMLDBField('loginsecuritytoken');
        drop_field($table, $field);
        // Drop the old 'online' field (was going to be a boolean, but was never used)
        $field = new XMLDBField('online');
        drop_field($table, $field);
        
        // Add the new 'lastactive' field
        echo " - adding lastactive field<br/>";
        $field = new XMLDBField('lastactive');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'avname');
        $result = $result && add_field($table, $field);
        if (!$result) echo "error<br/>";
        
        
    /// Purge redundant avatar entries
        echo "Purging redundant avatar entries...<br/>";
        $sql = "    DELETE FROM {$CFG->prefix}sloodle_users
                    WHERE userid = 0 OR uuid = '' OR avname = ''
        ";
        execute_sql($sql);
    }
    
    
    if ($result && $oldversion < 2009020201) {

    /// Define table sloodle_presenter_entry to be created
        $table = new XMLDBTable('sloodle_presenter_entry');

    /// Adding fields to table sloodle_presenter_entry
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('sloodleid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('source', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('type', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, 'web');
        $table->addFieldInfo('ordering', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table sloodle_presenter_entry
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Adding indexes to table sloodle_presenter_entry
        $table->addIndexInfo('mdl_sloopresentr_slo_ix', XMLDB_INDEX_NOTUNIQUE, array('sloodleid'));
        $table->addIndexInfo('mdl_sloopresentr_typ_ix', XMLDB_INDEX_NOTUNIQUE, array('type'));

    /// Launch create table for sloodle_presenter_entry
        $result = $result && create_table($table);
    }

    if ($result && $oldversion < 2009020701) {

    /// Define table sloodle_layout_entry_config to be created
        $table = new XMLDBTable('sloodle_layout_entry_config');

    /// Adding fields to table sloodle_layout_entry_config
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('layout_entry', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('value', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);

    /// Adding keys to table sloodle_layout_entry_config
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Launch create table for sloodle_layout_entry_config
        $result = $result && create_table($table);
    }

    // Add a name field to the Presenter entries.
    if ($result && $oldversion < 2009031002) {
    /// Define field name to be added to sloodle_presenter_entry
        $table = new XMLDBTable('sloodle_presenter_entry');
        $field = new XMLDBField('name');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null, 'sloodleid');

    /// Launch add field name
        $result = $result && add_field($table, $field);
    }

    // Add the SLOODLE Presenter table (we previously only had entries, but no data about the Presenter itself.)
    if ($result && $oldversion < 2009031003) {

    /// Define table sloodle_presenter to be created
        $table = new XMLDBTable('sloodle_presenter');

    /// Adding fields to table sloodle_presenter
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('sloodleid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('framewidth', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, null, null, null, null, '512');
        $table->addFieldInfo('frameheight', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, null, null, null, null, '512');

    /// Adding keys to table sloodle_presenter
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('sloodleid', XMLDB_KEY_UNIQUE, array('sloodleid'));

    /// Launch create table for sloodle_presenter
        $result = $result && create_table($table);
        if (!$result) return $result;

        // For sake of people who installed a test version of SLOODLE 0.4, we need to automatically create secondary Presenter instances.
        // These would normally be created when creating an instance of the module, but this table didn't exist during test versions.
        // Go through all SLOODLE modules with type "Presenter" and add an empty secondary entry on their behalf, with default values.
        $sloodlerecords = get_records('sloodle', 'type', 'presenter');
        if (!$sloodlerecords) $sloodlerecords = array();
        foreach ($sloodlerecords as $sr) {
            // Construct a default presenter instance for it
            $presenterrecord = new stdClass();
            $presenterrecord->sloodleid = $sr->id;
            insert_record('sloodle_presenter', $presenterrecord);
        }

    }

    if ($result && $oldversion < 2009072708) {
        echo "Converting Presenter slide type IDs... ";
        // Standardize any Presenter slides to use type names "image", "web", and "video".
        // The slide plugins for 1.0 initially used class names, like SloodlePluginPresenterSlideImage.
        // That's laborious and necessary, so we're reverting back to the original type names.
        $allslides = get_records('sloodle_presenter_entry');
        $numupdated = 0;
        if ($allslides) {
            foreach ($allslides as $slide) {
                // Update the type name if necessary
                $updated = true;
                switch (strtolower($slide->type)) {
                    // Image slides
                    case 'sloodlepluginpresenterslideimage': case 'presenterslideimage':
                        $slide->type = 'image';
                        break;
                    // Web slides
                    case 'sloodlepluginpresenterslideweb': case 'presenterslideweb':
                        $slide->type = 'web';
                        break;
                    // Video slides
                    case 'sloodlepluginpresenterslidevideo': case 'presenterslidevideo':
                        $slide->type = 'video';
                        break;
                    // Unrecognised type
                    default:
                        $updated = false;
                        break;
                }
                
                // Update the database record
                if ($updated) {
                    update_record('sloodle_presenter_entry', $slide);
                    $numupdated++;
                }
            }
        }
        echo "{$numupdated} slide(s) updated.<br/>";
    }


  return $result; 
}

?>

