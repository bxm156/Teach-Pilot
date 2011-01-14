<?php  // $Id: report.php,v 1.40.2.1 2007/02/28 05:36:24 nicolasconnault Exp $

// This script uses installed report plugins to print quiz reports

    require_once("../../config.php");
    require_once('locallib.php');

    $id = optional_param('id', '', PARAM_INT);    // Course Module ID, or
    $a = optional_param('a', '', PARAM_INT);     // udutu ID
    $b = optional_param('b', '', PARAM_INT);     // SCO ID
    $user = optional_param('user', '', PARAM_INT);  // User ID
    $attempt = optional_param('attempt', '1', PARAM_INT);  // attempt number

    if (!empty($id)) {
        if (! $cm = get_coursemodule_from_id('udutu', $id)) {
            error('Course Module ID was incorrect');
        }
        if (! $course = get_record('course', 'id', $cm->course)) {
            error('Course is misconfigured');
        }
        if (! $udutu = get_record('udutu', 'id', $cm->instance)) {
            error('Course module is incorrect');
        }
    } else {
        if (!empty($b)) {
            if (! $sco = get_record('udutu_scoes', 'id', $b)) {
                error('udutu activity is incorrect');
            }
            $a = $sco->udutu;
        }
        if (!empty($a)) {
            if (! $udutu = get_record('udutu', 'id', $a)) {
                error('Course module is incorrect');
            }
            if (! $course = get_record('course', 'id', $udutu->course)) {
                error('Course is misconfigured');
            }
            if (! $cm = get_coursemodule_from_instance('udutu', $udutu->id, $course->id)) {
                error('Course Module ID was incorrect');
            }
        }
    }

    require_login($course->id, false, $cm);

    if (!has_capability('mod/udutu:viewreport', get_context_instance(CONTEXT_MODULE,$cm->id))) {
        error('You are not allowed to use this script');
    }

    add_to_log($course->id, 'udutu', 'report', 'report.php?id='.$cm->id, $udutu->id);

    if (!empty($user)) {
        $userdata = udutu_get_user_data($user);
    } else {
        $userdata = null;
    }

/// Print the page header
    if (empty($noheader)) {
        if ($course->id != SITEID) {
            $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
        } else {
            $navigation = '';
        }

        $strudutus = get_string('modulenameplural', 'udutu');
        $strudutu  = get_string('modulename', 'udutu');
        $strreport  = get_string('report', 'udutu');
        $strattempt  = get_string('attempt', 'udutu');
        $strname  = get_string('name');
        if (empty($b)) {
            if (empty($a)) {
                print_header("$course->shortname: ".format_string($udutu->name), $course->fullname,
                             "$navigation <a href=\"index.php?id=$course->id\">$strudutus</a>
                              -> <a href=\"view.php?id=$cm->id\">".format_string($udutu->name,true)."</a> -> $strreport",
                             '', '', true);
            } else {
                print_header("$course->shortname: ".format_string($udutu->name), $course->fullname,
                             "$navigation <a href=\"index.php?id=$course->id\">$strudutus</a>
                              -> <a href=\"view.php?id=$cm->id\">".format_string($udutu->name,true)."</a>
                              -> <a href=\"report.php?id=$cm->id\">$strreport</a> -> $strattempt $attempt - ".fullname($userdata),
                             '', '', true);
            }
        } else {
            print_header("$course->shortname: ".format_string($udutu->name), $course->fullname,
                     "$navigation <a href=\"index.php?id=$course->id\">$strudutus</a>
                      -> <a href=\"view.php?id=$cm->id\">".format_string($udutu->name,true)."</a>
                      -> <a href=\"report.php?id=$cm->id\">$strreport</a>
                      -> <a href=\"report.php?a=$a&user=$user&attempt=$attempt\">$strattempt $attempt - ".fullname($userdata)."</a> -> $sco->title",
                     '', '', true);
        }
        print_heading(format_string($udutu->name));
    }

    $udutupixdir = $CFG->modpixpath.'/udutu/pix';

    if (empty($b)) {
        if (empty($a)) {
            // No options, show the global udutu report
            if ($scousers=get_records_select('udutu_scoes_track', "udutuid='$udutu->id' GROUP BY userid,udutuid", "", "userid,udutuid")) {
                $table = new stdClass();
                $table->head = array('&nbsp;', get_string('name'));
                $table->align = array('center', 'left');
                $table->wrap = array('nowrap', 'nowrap');
                $table->width = '100%';
                $table->size = array(10, '*');

                $table->head[]= get_string('attempt','udutu');
                $table->align[] = 'center';
                $table->wrap[] = 'nowrap';
                $table->size[] = '*';

                $table->head[]= get_string('started','udutu');
                $table->align[] = 'center';
                $table->wrap[] = 'nowrap';
                $table->size[] = '*';

                $table->head[]= get_string('last','udutu');
                $table->align[] = 'center';
                $table->wrap[] = 'nowrap';
                $table->size[] = '*';

                $table->head[]= get_string('score','udutu');
                $table->align[] = 'center';
                $table->wrap[] = 'nowrap';
                $table->size[] = '*';

                foreach($scousers as $scouser){
                    $userdata = udutu_get_user_data($scouser->userid);
                    $attempt = udutu_get_last_attempt($udutu->id,$scouser->userid);    
                    for ($a = 1; $a<=$attempt; $a++) {
                        $row = array();
                        $row[] = print_user_picture($scouser->userid, $course->id, $userdata->picture, false, true);
                        $row[] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$scouser->userid.'&course='.$course->id.'">'.
                                 fullname($userdata).'</a>';
                        $row[] = '<a href="report.php?a='.$udutu->id.'&user='.$scouser->userid.'&attempt='.$a.'">'.$a.'</a>';
                        $select = 'udutuid = '.$udutu->id.' and userid = '.$scouser->userid.' and attempt = '.$a;
                        $timetracks = get_record_select('udutu_scoes_track', $select,'min(timemodified) as started, max(timemodified) as last');      
                        $row[] = userdate($timetracks->started, get_string('strftimedaydatetime'));
                        $row[] = userdate($timetracks->last, get_string('strftimedaydatetime'));
 
                        $row[] = udutu_grade_user_attempt($udutu, $scouser->userid, $a);
                        $table->data[] = $row;
                    }
                }
            }
            print_table($table);
        } else {
            if (!empty($user)) {
                // User udutu report
                if ($scoes = get_records_select('udutu_scoes',"udutu='$udutu->id' ORDER BY id")) {
                    if (!empty($userdata)) {
                        print_simple_box_start('center');
                        echo '<div align="center">'."\n";
                        print_user_picture($user, $course->id, $userdata->picture, false, false);
                        echo "<a href=\"$CFG->wwwroot/user/view.php?id=$user&course=$course->id\">".
                             "$userdata->firstname $userdata->lastname</a><br />";
                        echo get_string('attempt','udutu').': '.$attempt;
                        echo '</div>'."\n";
                        print_simple_box_end();

                        // Print general score data
                        $table = new stdClass();
                        $table->head = array(get_string('title','udutu'),
                                             get_string('status','udutu'),
                                             get_string('time','udutu'),
                                             get_string('score','udutu'),
                                             '');
                        $table->align = array('left', 'center','center','right','left');
                        $table->wrap = array('nowrap', 'nowrap','nowrap','nowrap','nowrap');
                        $table->width = '80%';
                        $table->size = array('*', '*', '*', '*', '*');
                        foreach ($scoes as $sco) {
                            if ($sco->launch!='') {
                                $row = array();
                                $score = '&nbsp;';
                                if ($trackdata = udutu_get_tracks($sco->id,$user,$attempt)) {
                                    if ($trackdata->score_raw != '') {
                                        $score = $trackdata->score_raw;
                                    }
                                    if ($trackdata->status == '') {
                                        $trackdata->status = 'notattempted';
                                    }
                                    $detailslink = '<a href="report.php?b='.$sco->id.'&user='.$user.'&attempt='.$attempt.'" title="'.
                                                    get_string('details','udutu').'">'.get_string('details','udutu').'</a>';
                                } else {
                                    $trackdata->status = 'notattempted';
                                    $trackdata->total_time = '&nbsp;';
                                    $detailslink = '&nbsp;';
                                }
                                $strstatus = get_string($trackdata->status,'udutu');
                                $row[] = '<img src="'.$udutupixdir.'/'.$trackdata->status.'.gif" alt="'.$strstatus.'" title="'.
                                         $strstatus.'">&nbsp;'.format_string($sco->title);
                                $row[] = get_string($trackdata->status,'udutu');
                                $row[] = $trackdata->total_time;
                                $row[] = $score;
                                $row[] = $detailslink;
                            } else {
                                $row = array(format_string($sco->title), '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
                            }
                            $table->data[] = $row;
                        }
                        print_table($table);
                    }
                }
            } else {
                notice('No users to report');
            }
        }
    } else {
        // User SCO report
        if (!empty($userdata)) {
            print_simple_box_start('center');
            //print_heading(format_string($sco->title));
            print_heading('<a href="'.$CFG->wwwroot.'/mod/udutu/player.php?a='.$udutu->id.'&mode=browse&scoid='.$sco->id.'" target="_new">'.format_string($sco->title).'</a>'); 
            echo '<div align="center">'."\n";
            print_user_picture($user, $course->id, $userdata->picture, false, false);
            echo "<a href=\"$CFG->wwwroot/user/view.php?id=$user&course=$course->id\">".
                 "$userdata->firstname $userdata->lastname</a><br />";
            $scoreview = '';
            if ($trackdata = udutu_get_tracks($sco->id,$user,$attempt)) {
                if ($trackdata->score_raw != '') {
                    $scoreview = get_string('score','udutu').':&nbsp;'.$trackdata->score_raw;
                }
                if ($trackdata->status == '') {
                    $trackdata->status = 'notattempted';
                }
            } else {
                $trackdata->status = 'notattempted';
                $trackdata->total_time = '';
            }
            $strstatus = get_string($trackdata->status,'udutu');
            echo '<img src="'.$udutupixdir.'/'.$trackdata->status.'.gif" alt="'.$strstatus.'" title="'.
            $strstatus.'">&nbsp;'.$trackdata->total_time.'<br />'.$scoreview.'<br />';
            echo '</div>'."\n";
            echo '<hr /><h2>'.get_string('details','udutu').'</h2>';
            
            // Print general score data
            $table = new stdClass();
            $table->head = array(get_string('element','udutu'), get_string('value','udutu'));
            $table->align = array('left', 'left');
            $table->wrap = array('nowrap', 'nowrap');
            $table->width = '100%';
            $table->size = array('*', '*');
            
            $existelements = false;
            if ($udutu->version == 'scorm_1.3') {
                $elements = array('raw' => 'cmi.score.raw',
                                  'min' => 'cmi.score.min',
                                  'max' => 'cmi.score.max',
                                  'status' => 'cmi.completition_status',
                                  'time' => 'cmi.total_time');
            } else {
                $elements = array('raw' => 'cmi.core.score.raw',
                                  'min' => 'cmi.core.score.min',
                                  'max' => 'cmi.core.score.max',
                                  'status' => 'cmi.core.lesson_status',
                                  'time' => 'cmi.core.total_time');
            }
            $printedelements = array();
            foreach ($elements as $key => $element) {
                if (isset($trackdata->$element)) {
                    $existelements = true;
                    $printedelements[]=$element;
                    $row = array();
                    $row[] = get_string($key,'udutu');
                    $row[] = s($trackdata->$element);
                    $table->data[] = $row;
                }
            }
            if ($existelements) {
                echo '<h3>'.get_string('general','udutu').'</h3>';
                print_table($table);
            }                
            
            // Print Interactions data
            $table = new stdClass();
            $table->head = array(get_string('identifier','udutu'),
                                 get_string('type','udutu'),
                                 get_string('result','udutu'),
                                 get_string('student_response','udutu'));
            $table->align = array('center', 'center', 'center', 'center');
            $table->wrap = array('nowrap', 'nowrap', 'nowrap', 'nowrap');
            $table->width = '100%';
            $table->size = array('*', '*', '*', '*', '*');
            
            $existinteraction = false;
            
            $i = 0;
            $interactionid = 'cmi.interactions.'.$i.'.id';
            
            while (isset($trackdata->$interactionid)) {
                $existinteraction = true;
                $printedelements[]=$interactionid;
                $elements = array($interactionid,
                                  'cmi.interactions.'.$i.'.type',
                                  'cmi.interactions.'.$i.'.result',
                                  'cmi.interactions.'.$i.'.student_response');
                $row = array();
                foreach ($elements as $element) {
                    if (isset($trackdata->$element)) {
                        $row[] = s($trackdata->$element);
                        $printedelements[]=$element;
                    } else {
                        $row[] = '&nbsp;';
                    }
                }
                $table->data[] = $row;
                
                $i++;
                $interactionid = 'cmi.interactions.'.$i.'.id';
            }
            if ($existinteraction) {
                echo '<h3>'.get_string('interactions','udutu').'</h3>';
                echo '<h3>'.get_string('interactions','udutu').'</h3>';
                print_table($table);
            }
            
            // Print Objectives data
            $table = new stdClass();
            $table->head = array(get_string('identifier','udutu'),
                                 get_string('status','udutu'),
                                 get_string('raw','udutu'),
                                 get_string('min','udutu'),
                                 get_string('max','udutu'));
            $table->align = array('center', 'center', 'center', 'center', 'center');
            $table->wrap = array('nowrap', 'nowrap', 'nowrap', 'nowrap', 'nowrap');
            $table->width = '100%';
            $table->size = array('*', '*', '*', '*', '*');
            
            $existobjective = false;
            
            $i = 0;
            $objectiveid = 'cmi.objectives.'.$i.'.id';
            
            while (isset($trackdata->$objectiveid)) {
                $existobjective = true;
                $printedelements[]=$objectiveid;
                $elements = array($objectiveid,
                                  'cmi.objectives.'.$i.'.status',
                                  'cmi.objectives.'.$i.'.score.raw',
                                  'cmi.objectives.'.$i.'.score.min',
                                  'cmi.objectives.'.$i.'.score.max');
                $row = array();
                foreach ($elements as $element) {
                    if (isset($trackdata->$element)) {
                        $row[] = s($trackdata->$element);
                        $printedelements[]=$element;
                    } else {
                        $row[] = '&nbsp;';
                    }
                }
                $table->data[] = $row;
                
                $i++;
                $objectiveid = 'cmi.objectives.'.$i.'.id';
            }
            if ($existobjective) {
                echo '<h3>'.get_string('objectives','udutu').'</h3>';
                print_table($table);
            }
            $table = new stdClass();
            $table->head = array(get_string('element','udutu'), get_string('value','udutu'));
            $table->align = array('left', 'left');
            $table->wrap = array('nowrap', 'wrap');
            $table->width = '100%';
            $table->size = array('*', '*');
            
            $existelements = false;
            
            foreach($trackdata as $element => $value) {
                if (substr($element,0,3) == 'cmi') { 
                    if (!(in_array ($element, $printedelements))) {
                        $existelements = true;
                        $row = array();
                        $row[] = get_string($element,'udutu') != '[['.$element.']]' ? get_string($element,'udutu') : $element;
                        $row[] = s($value);
                        $table->data[] = $row;
                    }
                }
            }
            if ($existelements) {
                echo '<h3>'.get_string('othertracks','udutu').'</h3>';
                print_table($table);
            }                
            print_simple_box_end();
        } else {
            error('Missing script parameter');
        }
    }


    if (empty($noheader)) {
        print_footer($course);
    }
?>
