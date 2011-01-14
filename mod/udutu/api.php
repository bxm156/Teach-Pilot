<?php

    require_once("../../config.php");
    require_once('locallib.php');
    
    $id = optional_param('id', '', PARAM_INT);       // Course Module ID, or
    $a = optional_param('a', '', PARAM_INT);         // udutu ID
    $scoid = required_param('scoid', PARAM_INT);     // sco ID
    $mode = optional_param('mode', '', PARAM_ALPHA); // navigation mode
    $attempt = required_param('attempt', PARAM_INT); // new attempt

    if (!empty($id)) {
        if (! $cm = get_coursemodule_from_id('udutu', $id)) {
            error("Course Module ID was incorrect");
        }
        if (! $course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }
        if (! $udutu = get_record("udutu", "id", $cm->instance)) {
            error("Course module is incorrect");
        }
    } else if (!empty($a)) {
        if (! $udutu = get_record("udutu", "id", $a)) {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $udutu->course)) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("udutu", $udutu->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    } else {
        error('A required parameter is missing');
    }

    require_login($course->id, false, $cm);
    
    if ($usertrack=udutu_get_tracks($scoid,$USER->id,$attempt)) {
        if ((isset($usertrack->{'cmi.exit'}) && ($usertrack->{'cmi.exit'} != 'time-out')) || ($udutu->version != "udutu_1.3")) {
            $userdata = $usertrack;
        } else {
            $userdata->status = '';
            $userdata->score_raw = '';
        }
    } else {
        $userdata->status = '';
        $userdata->score_raw = '';
    }
    $userdata->student_id = addslashes($USER->username);
    $userdata->student_name = addslashes($USER->lastname .', '. $USER->firstname);
    $userdata->mode = 'normal';
    if (isset($mode)) {
        $userdata->mode = $mode;
    }
    if ($userdata->mode == 'normal') {
        $userdata->credit = 'credit';
    } else {
        $userdata->credit = 'no-credit';
    }    
    if ($scodatas = udutu_get_sco($scoid, SCO_DATA)) {
        foreach ($scodatas as $key => $value) {
            $userdata->$key = $value;
        }
    } else {
        error('Sco not found');
    }
    $udutu->version = strtolower(clean_param($udutu->version, PARAM_SAFEDIR));   // Just to be safe
    if (file_exists($CFG->dirroot.'/mod/udutu/datamodels/'.$udutu->version.'.js.php')) {
        include_once($CFG->dirroot.'/mod/udutu/datamodels/'.$udutu->version.'.js.php');
    } else {
        include_once($CFG->dirroot.'/mod/udutu/datamodels/scorm_12.js.php');
    }
?>

var errorCode = "0";
function underscore(str) {
    str = str.replace(/.N/g,".");
    return str.replace(/\./g,"__");
}
