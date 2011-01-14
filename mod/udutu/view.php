<?php  // $Id: view.php,v 1.50.2.1 2007/02/28 05:36:23 nicolasconnault Exp $

    require_once("../../config.php");
    require_once('locallib.php');
    
    $id = optional_param('id', '', PARAM_INT);       // Course Module ID, or
    $a = optional_param('a', '', PARAM_INT);         // udutu ID
    $organization = optional_param('organization', '', PARAM_INT); // organization ID

    

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

    $context = get_context_instance(CONTEXT_COURSE, $course->id);

    if (isset($SESSION->udutu_scoid)) {
        unset($SESSION->udutu_scoid);
    }

    $strudutus = get_string("modulenameplural", "udutu");
    $strudutu  = get_string("modulename", "udutu");

    if ($course->id != SITEID) { 
        $navigation = "<a $CFG->frametarget href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
        if ($udutus = get_all_instances_in_course('udutu', $course)) {
            // The module udutu activity with the least id is the course  
            $firstudutu = current($udutus);
            if (!(($course->format == 'udutu') && ($firstudutu->id == $udutu->id))) {
                $navigation .= "<a $CFG->frametarget href=\"index.php?id=$course->id\">$strudutus</a> ->";
            }       
        }
    } else {
        $navigation = "<a $CFG->frametarget href=\"index.php?id=$course->id\">$strudutus</a> ->";
	
    }

	

    $pagetitle = strip_tags($course->shortname.': '.format_string($udutu->name));

    add_to_log($course->id, 'udutu', 'pre-view', 'view.php?id='.$cm->id, "$udutu->id");

    //if ((has_capability('mod/udutu:skipview', get_context_instance(CONTEXT_MODULE,$cm->id))) && udutu_simple_play($udutu,$USER)) {
        
	//	exit;
    //}

    //
    // Print the page header
    //
    print_header($pagetitle, $course->fullname,
                 "$navigation <a $CFG->frametarget href=\"view.php?id=$cm->id\">".format_string($udutu->name,true)."</a>",
                 '', '', true, update_module_button($cm->id, $course->id, $strudutu), navmenu($course, $cm));

    if (empty($cm->visible) and !has_capability('moodle/course:manageactivities', $context)) {
            notice(get_string("activityiscurrentlyhidden"));
    }

      if (has_capability('mod/udutu:viewreport', $context)) {
        
        $trackedusers = udutu_get_count_users($udutu->id, $cm->groupingid);
        if ($trackedusers > 0) {
            echo "<div class=\"reportlink\"><a $CFG->frametarget href=\"report.php?id=$cm->id\"> ".get_string('viewalluserreports','udutu',$trackedusers).'</a></div>';
        } else {
            echo '<div class="reportlink">'.get_string('noreports','udutu').'</div>';
        }
    }

    // Print the main part of the page
    print_heading(format_string($udutu->name));
    print_box(format_text($udutu->summary), 'generalbox', 'intro');
    udutu_view_display($USER, $udutu, 'view.php?id='.$cm->id, $cm);
    print_footer($course);
?>
