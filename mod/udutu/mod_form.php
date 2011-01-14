<?php
require_once ('moodleform_mod.php');
require_once($CFG->dirroot.'/mod/udutu/locallib.php');

class mod_udutu_mod_form extends moodleform_mod {

    function definition() {

        global $CFG, $COURSE, $udutu_GRADE_METHOD, $udutu_WHAT_GRADE, $UDUTU_GRADESCOES;
        $mform    =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

// Name
        $mform->addElement('text', 'name', get_string('name'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

// Summary
        $mform->addElement('htmleditor', 'summary', get_string('summary'));
        $mform->setType('summary', PARAM_RAW);
        $mform->addRule('summary', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton('summary', array('writing', 'questions', 'richtext'), false, 'editorhelpbutton');

//myUdutu create/edit course
		require_once 'HTML/QuickForm.php';
		require_once 'HTML/QuickForm/DHTMLRulesTableless.php';
		require_once 'HTML/QuickForm/Renderer/Tableless.php';
		
        $createbutton =& MoodleQuickForm::createElement('button', 'createcourse', get_string('udutucreatebutton','udutu'));
        $createbuttonattributes = array('title'=>'myUdutuCreateCourse',
         'onclick'=>"window.open('http://www.myudutu.com','myUdutu','menubar=1,location=0,scrollbars=1,resizable=1,width=1024,height=768');");
		 

		$createbutton->updateAttributes($createbuttonattributes);
         
        $createhelpPath = '/help.php?module=udutu&file=createmyudutu.html';
		$createbuttonHelp =& MoodleQuickForm::createElement('image', 'getcourseHelp', $CFG->wwwroot.'/mod/udutu/help.gif');
		$createbuttonhelpattributes = array('title'=>'myUdutuCourseHelp','onclick'=>"return openpopup('".$createhelpPath."', '".$createbuttonHelp->getName()."', 'menubar=0,location=0,scrollbars,resizable,width=750,height=500', 0);");
		$createbuttonHelp->updateAttributes($createbuttonhelpattributes);
		 
		 $createbuttons = array();
		 $createbuttons[] = $createbutton;
		 $createbuttons[] = $createbuttonHelp;
		 
		 $mform->addGroup($createbuttons, 'createbuttons', get_string('myUdutuCreate','udutu'), ' ', false);	

//myUdutu import
		
		//require_once 'HTML/QuickForm.php';
		//require_once 'HTML/QuickForm/DHTMLRulesTableless.php';
		//require_once 'HTML/QuickForm/Renderer/Tableless.php';
		
        $button =& MoodleQuickForm::createElement('button', 'getcourse', get_string('udutubutton','udutu'));
        $buttonattributes = array('title'=>'myUdutuCourse',
                   'onclick'=>"return openpopup('/mod/udutu/importcourse.php?id=$COURSE->id', '".$button->getName()."', 'menubar=0,location=0,scrollbars,resizable,width=750,height=500', 0);");
		$button->updateAttributes($buttonattributes);
        $helpPath = '/help.php?module=udutu&file=getmyudutu.html';
		$buttonHelp =& MoodleQuickForm::createElement('image', 'getcourseHelp', $CFG->wwwroot.'/mod/udutu/help.gif');
		$buttonhelpattributes = array('title'=>'myUdutuCourseHelp','onclick'=>"return openpopup('".$helpPath."', '".$buttonHelp->getName()."', 'menubar=0,location=0,scrollbars,resizable,width=750,height=500', 0);");
		$buttonHelp->updateAttributes($buttonhelpattributes);
         
         $image =& MoodleQuickForm::createElement('image', 'success', $CFG->wwwroot.'/mod/udutu/success.gif');
		 $imageattributes = array('title'=>'successImage','style'=>'visibility:hidden');
		 $image->updateAttributes($imageattributes);
		 
		 $buttons = array();
		 $buttons[] = $button;
		 $buttons[] = $buttonHelp;
		 $buttons[] = $image;
		
		 $mform->addGroup($buttons, 'buttons', get_string('myUdutuExp','udutu'), ' ', false);	
	

// Reference
        $mform->addElement('choosecoursefile', 'reference', get_string('coursepacket','udutu'));
        $mform->setType('reference', PARAM_RAW);  // We need to find a better PARAM
        $mform->addRule('reference', get_string('required'), 'required', null, 'client');
        //$mform->setHelpButton('reference',array('package', get_string('coursepacket', 'udutu')), 'udutu');
				        
	
		 
		 

		
//-------------------------------------------------------------------------------
// Other Settings
        $mform->addElement('header', 'advanced', get_string('othersettings', 'udutu'));

// Grading
        $mform->addElement('static', 'grade', get_string('grades'));
        
// Grade Method
        $helpGPath = '/help.php?module=udutu&file=grading.html';
		$buttonGHelp =& MoodleQuickForm::createElement('image', 'getcourseGHelp', $CFG->wwwroot.'/mod/udutu/help.gif');
		$buttonGhelpattributes = array('title'=>'HelpOnGrading','onclick'=>"return openpopup('".$helpGPath."', '".$buttonGHelp->getName()."', 'menubar=0,location=0,scrollbars,resizable,width=750,height=500', 0);");
		$buttonGHelp->updateAttributes($buttonGhelpattributes);
		 
		$buttonsG = array();
		$buttonsG[] = &$mform->createElement('select', 'grademethod', get_string('grademethod', 'udutu'), $udutu_GRADE_METHOD);
		$buttonsG[] = $buttonGHelp;
		 
		$mform->addGroup($buttonsG, 'buttonsG', get_string('grademethod', 'udutu'), ' ', false);
        $mform->setDefault('grademethod', 0);
        
// Maximum Grade
        for ($i=0; $i<=100; $i++) {
          $grades[$i] = "$i";
        }
        $mform->addElement('select', 'maxgrade', get_string('maximumgrade'), $grades);
        $mform->setDefault('maxgrade', 0);
        $mform->disabledIf('maxgrade', 'grademethod','eq',UDUTU_GRADESCOES);

// Attempts
        $mform->addElement('static', '', '' ,'<hr />');
        $mform->addElement('static', 'attempts', get_string('attempts','udutu'));

// Max Attempts
        for ($i=1; $i<=$CFG->udutu_maxattempts; $i++) {
            if ($i == 1) {
                $attempts[$i] = $i . ' ' . get_string('attempt','udutu');
            } else {
                $attempts[$i] = $i . ' ' . get_string('attempts','udutu');
            }
        }

	  	$mform->addElement('select', 'maxattempt', get_string('maximumattempts', 'udutu'), udutu_get_attempts_array());
        $mform->setDefault('maxattempt', 1);

// What Grade
        $helpAPath = '/help.php?module=udutu&file=whatgrade.html';
		$buttonAHelp =& MoodleQuickForm::createElement('image', 'getcourseAHelp', $CFG->wwwroot.'/mod/udutu/help.gif');
		$buttonAhelpattributes = array('title'=>'HelpOnWhatGrade','onclick'=>"return openpopup('".$helpAPath."', '".$buttonAHelp->getName()."', 'menubar=0,location=0,scrollbars,resizable,width=750,height=500', 0);");
		$buttonAHelp->updateAttributes($buttonAhelpattributes);
		$buttonsA = array();
		$buttonsA[] = &$mform->createElement('select', 'whatgrade', get_string('whatgrade', 'udutu'), $udutu_WHAT_GRADE);
		$buttonsA[] = $buttonAHelp;
		 
		$mform->addGroup($buttonsA, 'buttonsA', get_string('whatgrade', 'udutu'), ' ', false);
		
        
        $mform->disabledIf('buttonsA', 'maxattempt','eq',1);
        $mform->setDefault('buttonsA', 0);
        $mform->setAdvanced('buttonsA');
		
		

// Activation period
        $mform->addElement('static', '', '' ,'<hr />');
        $mform->addElement('static', 'activation', get_string('activation','udutu'));
        $datestartgrp = array();
        $datestartgrp[] = &$mform->createElement('date_time_selector', 'startdate');
        $datestartgrp[] = &$mform->createElement('checkbox', 'startdisabled', null, get_string('disable'));
        $mform->addGroup($datestartgrp, 'startdategrp', get_string('from'), ' ', false);
        $mform->setDefault('startdate', 0);
        $mform->setDefault('startdisabled', 1);
        $mform->disabledIf('startdategrp', 'startdisabled', 'checked');

        $dateendgrp = array();
        $dateendgrp[] = &$mform->createElement('date_time_selector', 'enddate');
        $dateendgrp[] = &$mform->createElement('checkbox', 'enddisabled', null, get_string('disable'));
        $mform->addGroup($dateendgrp, 'dateendgrp', get_string('to'), ' ', false);
        $mform->setDefault('enddate', 0);
        $mform->setDefault('enddisabled', 1);
        $mform->disabledIf('dateendgrp', 'enddisabled', 'checked');


// Width
        $mform->addElement('hidden', 'width', get_string('width','udutu'),'maxlength="5" size="5"');
        $mform->setDefault('width', '100%');
        $mform->setType('width', PARAM_INT);
        
// Height
        $mform->addElement('hidden', 'height', get_string('height','udutu'),'maxlength="5" size="5"');
        $mform->setDefault('height', '100%');
        $mform->setType('height', PARAM_INT);

// Framed / Popup Window
        $options = array();
        $options[0] = get_string('iframe', 'udutu');
        $options[1] = get_string('popup', 'udutu');
        $mform->addElement('hidden', 'popup', get_string('display','udutu'), $options);
        $mform->setDefault('popup', 1);


// Window Options
        $winoptgrp = array();
        $winoptgrp[] = &$mform->createElement('hidden', 'resizable', '', get_string('resizable', 'udutu'));
        $winoptgrp[] = &$mform->createElement('hidden', 'scrollbars', '', get_string('scrollbars', 'udutu'));
        $winoptgrp[] = &$mform->createElement('hidden', 'directories', '', get_string('directories', 'udutu'));
        $winoptgrp[] = &$mform->createElement('hidden', 'location', '', get_string('location', 'udutu'));
        $winoptgrp[] = &$mform->createElement('hidden', 'menubar', '', get_string('menubar', 'udutu'));
        $winoptgrp[] = &$mform->createElement('hidden', 'toolbar', '', get_string('toolbar', 'udutu'));
        $winoptgrp[] = &$mform->createElement('hidden', 'status', '', get_string('status', 'udutu'));
        $mform->addGroup($winoptgrp, 'winoptgrp', '', '<br />', false);
        $mform->setDefault('resizable', 0);
        $mform->setDefault('scrollbars', 0);
        $mform->setDefault('directories', 0);
        $mform->setDefault('location', 0);
        $mform->setDefault('menubar', 0);
        $mform->setDefault('toolbar', 0);
        $mform->setDefault('status', 0);
        $mform->disabledIf('winoptgrp', 'popup', 'eq', 0);

// Skip view page
        $options = array();
        $options[0]=get_string('never');
        $options[1]=get_string('firstaccess','udutu');
        $options[2]=get_string('always');
        $mform->addElement('select', 'skipview', get_string('skipview', 'udutu'), $options);
        $mform->setDefault('skipview', 1);
        $mform->setAdvanced('skipview');

// Hide Browse
		$helpHPath = '/help.php?module=udutu&file=hidebrowse.html';
		$buttonHHelp =& MoodleQuickForm::createElement('image', 'getcourseHPath', $CFG->wwwroot.'/mod/udutu/help.gif');
		$buttonHhelpattributes = array('title'=>'HelpOnHideBrowse','onclick'=>"return openpopup('".$helpHPath."', '".$buttonHHelp->getName()."', 'menubar=0,location=0,scrollbars,resizable,width=750,height=500', 0);");
		$buttonHHelp->updateAttributes($buttonHhelpattributes);
		$buttonsH = array();
		$buttonsH[] = &$mform->createElement('selectyesno', 'hidebrowse', get_string('hidebrowse', 'udutu'));
		$buttonsH[] = $buttonHHelp;
		 
		$mform->addGroup($buttonsH, 'buttonsH', get_string('hidebrowse', 'udutu'), ' ', false);
        //$mform->addElement();
        
        $mform->setDefault('hidebrowse', 0);
        $mform->setAdvanced('buttonsH');

// Toc display
        $options = array();
        $options[1]=get_string('hidden','udutu');
        $options[0]=get_string('sided','udutu');
        $options[2]=get_string('popupmenu','udutu');
        $mform->addElement('select', 'hidetoc', get_string('hidetoc', 'udutu'), $options);
        $mform->setDefault('hidetoc', 0);
        $mform->setAdvanced('hidetoc');

// Hide Navigation panel
        $mform->addElement('selectyesno', 'hidenav', get_string('hidenav', 'udutu'));
        $mform->setDefault('hidenav', 0);
        $mform->setAdvanced('hidenav');

// Autocontinue
        
        $helpACPath = '/help.php?module=udutu&file=autocontinue.html';
		$buttonACHelp =& MoodleQuickForm::createElement('image', 'getcourseACHelp', $CFG->wwwroot.'/mod/udutu/help.gif');
		$buttonAChelpattributes = array('title'=>'HelpOnAutoContinue','onclick'=>"return openpopup('".$helpACPath."', '".$buttonACHelp->getName()."', 'menubar=0,location=0,scrollbars,resizable,width=750,height=500', 0);");
		$buttonACHelp->updateAttributes($buttonAChelpattributes);
		$buttonsAC = array();
		$buttonsAC[] = &$mform->createElement('selectyesno', 'auto', get_string('autocontinue', 'udutu'));
		$buttonsAC[] = $buttonACHelp;
		 
		$mform->addGroup($buttonsAC, 'buttonsAC', get_string('autocontinue', 'udutu'), ' ', false);
		
        $mform->setDefault('auto', 0);
        $mform->setAdvanced('buttonsAC');

// Update packages timing
        $options = array();
        $options[0]=get_string('never','udutu');
        $options[1]=get_string('onchanges','udutu');
        $options[2]=get_string('everyday','udutu');
        $options[3]=get_string('everytime','udutu');
        $mform->addElement('select', 'updatefreq', get_string('updatefreq', 'udutu'), $options);
        $mform->setDefault('updatefreq', 0);
        $mform->setAdvanced('updatefreq');

//-------------------------------------------------------------------------------
// Hidden Settings
        $mform->addElement('hidden', 'datadir', null);
        $mform->addElement('hidden', 'pkgtype', null);
        $mform->addElement('hidden', 'launch', null);
        $mform->addElement('hidden', 'redirect', null);
        $mform->addElement('hidden', 'redirecturl', null);


//-------------------------------------------------------------------------------
        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();

    }

    function defaults_preprocessing(&$default_values) {
        global $COURSE;

        if (isset($default_values['popup']) && ($default_values['popup'] == 1) && isset($default_values['options'])) {
            $options = explode(',',$default_values['options']);
            foreach ($options as $option) {
                list($element,$value) = explode('=',$option);
                $element = trim($element);
                $default_values[$element] = trim($value); 
            }
        }
        if (isset($default_values['grademethod'])) {
            $default_values['whatgrade'] = intval($default_values['grademethod'] / 10);
            $default_values['grademethod'] = $default_values['grademethod'] % 10;
        }
        if (isset($default_value['width']) && (strpos($default_value['width'],'%') === false) && ($default_value['width'] <= 100)) {
            $default_value['width'] .= '%';
        }
        if (isset($default_value['width']) && (strpos($default_value['height'],'%') === false) && ($default_value['height'] <= 100)) {
            $default_value['height'] .= '%';
        }
        $udutus = get_all_instances_in_course('udutu', $COURSE);
        $courseudutu = current($udutus);
        if (($COURSE->format == 'udutu') && ((count($udutus) == 0) || ($default_values['instance'] == $courseudutu->id))) {
            $default_values['redirect'] = 'yes';
            $default_values['redirecturl'] = '../course/view.php?id='.$default_values['course'];    
        } else {
            $default_values['redirect'] = 'no';
            $default_values['redirecturl'] = '../mod/udutu/view.php?id='.$default_values['coursemodule'];
        }
        if (isset($default_values['version'])) {
            $default_values['pkgtype'] = (substr($default_values['version'],0,5) == 'udutu') ? 'udutu':'aicc';
        }
        if (isset($default_values['instance'])) {
            $default_values['datadir'] = $default_values['instance'];
        }
    }

    function validation($data) {
        $validate = udutu_validate($data);

        if ($validate->result) {
            return true;
        } else {
            return $validate->errors;
        }
    }

}
?>
