<?php // $Id: index.php,v 1.20 2006/09/26 07:56:07 bobopinna Exp $

    require_once("../../config.php");

    $id = required_param('id', PARAM_INT);   // course id

    if (!empty($id)) {
        if (! $course = get_record("course", "id", $id)) {
            error("Course ID is incorrect");
        }
    } else {
        error('A required parameter is missing');
    }

    require_course_login($course);

    add_to_log($course->id, "udutu", "view all", "index.php?id=$course->id", "");

    $strudutu = get_string("modulename", "udutu");
    $strudutus = get_string("modulenameplural", "udutu");
    $strweek = get_string("week");
    $strtopic = get_string("topic");
    $strname = get_string("name");
    $strsummary = get_string("summary");
    $strreport = get_string("report",'udutu');
    $strlastmodified = get_string("lastmodified");

    print_header_simple("$strudutus", "", "$strudutus",
                 "", "", true, "", navmenu($course));

    if ($course->format == "weeks" or $course->format == "topics") {
        $sortorder = "cw.section ASC";
    } else {
        $sortorder = "m.timemodified DESC";
    }

    if (! $udutus = get_all_instances_in_course("udutu", $course)) {
        notice("There are no udutus", "../../course/view.php?id=$course->id");
        exit;
    }

    if ($course->format == "weeks") {
        $table->head  = array ($strweek, $strname, $strsummary, $strreport);
        $table->align = array ("center", "left", "left", "left");
    } else if ($course->format == "topics") {
        $table->head  = array ($strtopic, $strname, $strsummary, $strreport);
        $table->align = array ("center", "left", "left", "left");
    } else {
        $table->head  = array ($strlastmodified, $strname, $strsummary, $strreport);
        $table->align = array ("left", "left", "left", "left");
    }

    foreach ($udutus as $udutu) {

        $context = get_context_instance(CONTEXT_MODULE,$udutu->coursemodule);
        $tt = "";
        if ($course->format == "weeks" or $course->format == "topics") {
            if ($udutu->section) {
                $tt = "$udutu->section";
            }
        } else {
            $tt = userdate($udutu->timemodified);
        }
        $report = '&nbsp;';
        if (has_capability('mod/udutu:viewreport', $context)) {
            $trackedusers = get_record('udutu_scoes_track', 'udutuid', $udutu->id, '', '', '', '', 'count(distinct(userid)) as c');
            if ($trackedusers->c > 0) {
                $reportshow = '<a href="report.php?id='.$udutu->coursemodule.'">'.get_string('viewallreports','udutu',$trackedusers->c).'</a></div>';
            } else {
                $reportshow = get_string('noreports','udutu');
            }
        } else if (has_capability('mod/udutu:viewscores', $context)) {
            require_once('locallib.php');
            $report = udutu_grade_user($udutu, $USER->id);
            $reportshow = get_string('score','udutu').": ".$report;       
        }
        if (!$udutu->visible) {
           //Show dimmed if the mod is hidden
           $table->data[] = array ($tt, "<a class=\"dimmed\" href=\"view.php?id=$udutu->coursemodule\">".format_string($udutu->name,true)."</a>",
                                   format_text($udutu->summary), $reportshow);
        } else {
           //Show normal if the mod is visible
           $table->data[] = array ($tt, "<a href=\"view.php?id=$udutu->coursemodule\">".format_string($udutu->name,true)."</a>",
                                   format_text($udutu->summary), $reportshow);
        }
    }

    echo "<br />";

    print_table($table);

    print_footer($course);

?>
