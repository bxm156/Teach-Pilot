<?php //$Id: mod_form.php,v 1.2.2.3 2009/03/19 12:23:11 mudrd8mz Exp $

/**
 * This file defines the main vimoodle configuration form
 * It uses the standard core Moodle (>1.8) formslib. For
 * more info about them, please visit:
 *
 * http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * The form must provide support for, at least these fields:
 *   - name: text element of 64cc max
 *
 * Also, it's usual to use these fields:
 *   - intro: one htmlarea element to describe the activity
 *            (will be showed in the list of activities of
 *             vimoodle type (index.php) and in the header
 *             of the vimoodle main page (view.php).
 *   - introformat: The format used to write the contents
 *             of the intro field. It automatically defaults
 *             to HTML when the htmleditor is used and can be
 *             manually selected if the htmleditor is not used
 *             (standard formats are: MOODLE, HTML, PLAIN, MARKDOWN)
 *             See lib/weblib.php Constants and the format_text()
 *             function for more info
 */

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_vimoodle_mod_form extends moodleform_mod {

    function definition() {

        global $COURSE;
        $mform =& $this->_form;

//-------------------------------------------------------------------------------
    /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

    /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('vimoodlename', 'vimoodle'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
		
	

    /// Adding the required "intro" field to hold the description of the instance
        $mform->addElement('htmleditor', 'intro', get_string('vimoodleintro', 'vimoodle'));
        $mform->setType('intro', PARAM_RAW);
        $mform->addRule('intro', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton('intro', array('writing', 'richtext'), false, 'editorhelpbutton');

    /// Adding "introformat" field
        $mform->addElement('format', 'introformat', get_string('format'));

//-------------------------------------------------------------------------------
    /// Adding the rest of vimoodle settings, spreeading all them into this fieldset
    /// or adding more fieldsets ('header' elements) if needed for better logic
		$mform->addElement('header', 'vimoodleRequiredFieldset', get_string('vimoodleRequiredFieldset', 'vimoodle'));
        
		$mform->addElement('text', 'videoid', get_string('vimoodleid', 'vimoodle'), array('size'=>'64'));
		$mform->setType('videoid', PARAM_TEXT);
		$mform->addRule('videoid', null, 'required', null, 'client');
		$mform->addElement('static','example1','Example:','123456');
        
        $mform->addElement('text', 'caseid',get_string('vimoodlecaseid','vimoodle'), array('size'=>'10'));
		$mform->setType('caseid', PARAM_TEXT);
		$mform->addRule('caseid', null, 'required', null, 'client');
		$mform->addElement('static','example2','Example:','bxm156');
		
		$mform->addElement('text', 'pin',get_string('vimoodlepin','vimoodle'), array('size'=>'10'));
		$mform->setType('pin', PARAM_TEXT);
		$mform->addRule('pin', null, 'required', null, 'client');
		$mform->addElement('static','example3','Example:','1234');
		

//-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();

    }
}

?>
