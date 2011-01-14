<?PHP  // $Id: player.php,v 1.22.2.4 2007/03/13 08:28:47 moodler Exp $

/// This page prints a particular instance of aicc/udutu package

    require_once('../../config.php');
    require_once('locallib.php');

    //
    // Checkin' script parameters
    //
    $id = optional_param('id', '', PARAM_INT);       // Course Module ID, or
    $a = optional_param('a', '', PARAM_INT);         // udutu ID
    $scoid = required_param('scoid', PARAM_INT);  // sco ID
    $mode = optional_param('mode', 'normal', PARAM_ALPHA); // navigation mode
    $currentorg = optional_param('currentorg', '', PARAM_RAW); // selected organization
    $newattempt = optional_param('newattempt', 'off', PARAM_ALPHA); // the user request to start a new attempt
	
	$scoid=$_GET['scoid'];
	
	
	//$scoid=$_POST['scoid'];
	//echo 'SCOID'.$scoid;
	if ($sco1 = get_record("udutu_scoes", "id", $scoid,"parent",'/')) {
           $scoid++;
    }

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

    $strudutus = get_string('modulenameplural', 'udutu');
    $strudutu  = get_string('modulename', 'udutu');
    $strpopup = get_string('popup','udutu');

    if ($course->id != SITEID) {
        $navigation = "<a $CFG->frametarget href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
        if ($udutus = get_all_instances_in_course('udutu', $course)) {
            // The module udutu/AICC activity with the first id is the course  
            $firstudutu = current($udutus);
            if (!(($course->format == 'udutu') && ($firstudutu->id == $udutu->id))) {
                $navigation .= "<a $CFG->frametarget href=\"index.php?id=$course->id\">$strudutus</a> ->";
            }
        }
    } else {
        $navigation = "<a $CFG->frametarget href=\"index.php?id=$course->id\">$strudutus</a> ->";
    }

    $pagetitle = strip_tags("$course->shortname: ".format_string($udutu->name));

    if (!$cm->visible and !has_capability('moodle/course:viewhiddenactivities', get_context_instance(CONTEXT_COURSE,$course->id))) {
        print_header($pagetitle, $course->fullname,
                 "$navigation <a $CFG->frametarget href=\"view.php?id=$cm->id\">".format_string($udutu->name,true)."</a>",
                 '', '', true, update_module_button($cm->id, $course->id, $strudutu), '', false);
        notice(get_string("activityiscurrentlyhidden"));
    }

    //
    // TOC processing
    //
    $udutu->version = strtolower(clean_param($udutu->version, PARAM_SAFEDIR));   // Just to be safe
    if (!file_exists($CFG->dirroot.'/mod/udutu/datamodels/'.$udutu->version.'lib.php')) {
        $udutu->version = 'udutu_12';
    }
    require_once($CFG->dirroot.'/mod/udutu/datamodels/'.$udutu->version.'lib.php');
    $attempt = udutu_get_last_attempt($udutu->id, $USER->id);
    if (($newattempt=='on') && (($attempt < $udutu->maxattempt) || ($udutu->maxattempt == 0))) {
        $attempt++;
        $mode = 'normal';
    }
    $attemptstr = '&amp;attempt=' . $attempt;

    $result = udutu_get_toc($USER,$udutu,'structurelist',$currentorg,$scoid,$mode,$attempt,true);
    $sco = $result->sco;

    if (($mode == 'browse') && ($udutu->hidebrowse == 1)) {
       $mode = 'normal';
    }
    if ($mode != 'browse') {
        if ($trackdata = udutu_get_tracks($sco->id,$USER->id,$attempt)) {
            if (($trackdata->status == 'completed') || ($trackdata->status == 'passed') || ($trackdata->status == 'failed')) {
                $mode = 'review';
            } else {
                $mode = 'normal';
            }
        } else {
            $mode = 'normal';
        }
    }

    add_to_log($course->id, 'udutu', 'view', "player.php?id=$cm->id&scoid=$sco->id", "$udutu->id");

    $scoidstr = '&amp;scoid='.$sco->id;
    $scoidpop = '&scoid='.$sco->id;
    $modestr = '&amp;mode='.$mode;
    if ($mode == 'browse') {
        $modepop = '&mode='.$mode;
    } else {
        $modepop = '';
    }
    $orgstr = '&currentorg='.$currentorg;

    $SESSION->udutu_scoid = $sco->id;
    $SESSION->udutu_status = 'Not Initialized';
    $SESSION->udutu_mode = $mode;
    $SESSION->udutu_attempt = $attempt;

    //
    // Print the page header
    //
    $bodyscript = '';
    if ($udutu->popup == 1) {
        $bodyscript = 'onunload="main.close();"';
    }

    print_header($pagetitle, $course->fullname,
                 "$navigation <a $CFG->frametarget href=\"view.php?id=$cm->id\">".format_string($udutu->name,true)."</a>",
                 '', '', true, update_module_button($cm->id, $course->id, $strudutu), '', false, $bodyscript);
    if ($sco->udututype == 'sco') {
?>
    <script type="text/javascript" src="request.js"></script>
    <script type="text/javascript" src="api.php?id=<?php echo $cm->id.$scoidstr.$modestr.$attemptstr ?>"></script>
<?php
    }
    if (($sco->previd != 0) && ((!isset($sco->previous)) || ($sco->previous == 0))) {
        $scostr = '&scoid='.$sco->previd;
        echo '    <script type="text/javascript">'."\n//<![CDATA[\n".'var prev="'.$CFG->wwwroot.'/mod/udutu/player.php?id='.$cm->id.$orgstr.$modepop.$scostr."\";\n//]]>\n</script>\n";
    } else {
        echo '    <script type="text/javascript">'."\n//<![CDATA[\n".'var prev="'.$CFG->wwwroot.'/mod/udutu/view.php?id='.$cm->id."\";\n//]]>\n</script>\n";
    }
    if (($sco->nextid != 0) && ((!isset($sco->next)) || ($sco->next == 0))) {
        $scostr = '&scoid='.$sco->nextid;
        echo '    <script type="text/javascript">'."\n//<![CDATA[\n".'var next="'.$CFG->wwwroot.'/mod/udutu/player.php?id='.$cm->id.$orgstr.$modepop.$scostr."\";\n//]]>\n</script>\n";
    } else {
        echo '    <script type="text/javascript">'."\n//<![CDATA[\n".'var next="'.$CFG->wwwroot.'/mod/udutu/view.php?id='.$cm->id."\";\n//]]>\n</script>\n";
    }
?>
    <div id="udutupage">
<?php  
    if ($udutu->hidetoc == 0) {
?>
        <div id="tocbox" class="generalbox">
            <div id="tochead" class="header"><?php print_string('contents','udutu') ?></div>
            <div id="toctree">
            <?php echo $result->toc; ?>
            </div>
        </div>
<?php
        $class = ' class="toc"';
    } else {
        $class = ' class="no-toc"';
    }
?>
        <div id="udutubox"<?php echo $class ?>>
<?php
    // This very big test check if is necessary the "udututop" div
    if (
           ($mode != 'normal') ||  // We are not in normal mode so review or browse text will displayed
           (
               ($udutu->hidenav == 0) &&  // Teacher want to display navigation links
               (
                   (
                       ($sco->previd != 0) &&  // This is not the first learning object of the package
                       ((!isset($sco->previous)) || ($sco->previous == 0))   // Moodle must manage the previous link
                   ) || 
                   (
                       ($sco->nextid != 0) &&  // This is not the last learning object of the package
                       ((!isset($sco->next)) || ($sco->next == 0))       // Moodle must manage the next link
                   ) 
               )
           ) || ($udutu->hidetoc == 2)      // Teacher want to display toc in a small dropdown menu 
       ) {
?>
            <div id="udututop">
        <?php echo $mode == 'browse' ? '<div id="udutumode" class="left">'.get_string('browsemode','udutu')."</div>\n" : ''; ?>
        <?php echo $mode == 'review' ? '<div id="udutumode" class="left">'.get_string('reviewmode','udutu')."</div>\n" : ''; ?>
<?php
        if (($udutu->hidenav == 0) || ($udutu->hidetoc == 2)) {
?>
                <div id="udutunav" class="right">
        <?php
            $orgstr = '&amp;currentorg='.$currentorg;
            if (($udutu->hidenav == 0) && ($sco->previd != 0) && ((!isset($sco->previous)) || ($sco->previous == 0))) {
                /// Print the prev LO link
                $scostr = '&amp;scoid='.$sco->previd;
                $url = $CFG->wwwroot.'/mod/udutu/player.php?id='.$cm->id.$orgstr.$modestr.$scostr;
                echo '<a href="'.$url.'">&lt; '.get_string('prev','udutu').'</a>';
            }
            if ($udutu->hidetoc == 2) {
                echo $result->tocmenu;
            }
            if (($udutu->hidenav == 0) && ($sco->nextid != 0) && ((!isset($sco->next)) || ($sco->next == 0))) {
                /// Print the next LO link
                $scostr = '&amp;scoid='.$sco->nextid;
                $url = $CFG->wwwroot.'/mod/udutu/player.php?id='.$cm->id.$orgstr.$modestr.$scostr;
                echo '            &nbsp;<a href="'.$url.'">'.get_string('next','udutu').' &gt;</a>';
            }
        ?>

                </div>
<?php
        } 
?>
            </div>
<?php
    } // The end of the very big test
?>
            <div id="udutuobject" class="right">
                <noscript>
                    <div id="noscript">
                        <?php print_string('noscriptnoudutu','udutu'); // No Martin(i), No Party ;-) ?>

                    </div>
                </noscript>
<?php
    if ($result->prerequisites) {
        if ($udutu->popup == 0) {
?>
                <iframe id="main"
                        class="scoframe"
                        width="<?php echo $udutu->width<=100 ? $udutu->width.'%' : $udutu->width ?>" 
                        height="<?php echo $udutu->height<=100 ? $udutu->height.'%' : $udutu->height ?>" 
                        src="loadSCO.php?id=<?php echo $cm->id.$scoidstr.$modestr ?>">
                </iframe>
<?php
        } else {
?>
                    <script type="text/javascript">
                    //<![CDATA[
                        function openpopup(url,name,options,width,height) {
                            fullurl = "<?php echo $CFG->wwwroot.'/mod/udutu/' ?>" + url;
                            windowobj = window.open(fullurl,name,options);
                            if ((width==100) && (height==100)) {
                                // Fullscreen
                                windowobj.moveTo(0,0);
                            } 
                            if (width<=100) {
                                width = Math.round(screen.availWidth * width / 100);
                            }
                            if (height<=100) {
                                height = Math.round(screen.availHeight * height / 100);
                            }
                            windowobj.resizeTo(width,height);
                            windowobj.focus();
                            return windowobj;
                        }

                        url = "loadSCO.php?id=<?php echo $cm->id.$scoidpop ?>";
                        width = <?php p($udutu->width) ?>;
                        height = <?php p($udutu->height) ?>;
                        var main = openpopup(url, "udutupopup", "<?php p($udutu->options) ?>", width, height);
                    //]]>
                    </script>
                    <noscript>
                    <iframe id="main"
                            class="scoframe"
                            width="<?php echo $udutu->width<=100 ? $udutu->width.'%' : $udutu->width ?>" 
                            height="<?php echo $udutu->height<=100 ? $udutu->height.'%' : $udutu->height ?>" 
                            src="loadSCO.php?id=<?php echo $cm->id.$scoidstr.$modestr ?>">
                    </iframe>
                    </noscript>
<?php            
        }
    } else {
        print_simple_box(get_string('noprerequisites','udutu'),'center');
    }
?>
            </div> <!-- udutu object -->
        </div> <!-- udutu box  -->
    </div> <!-- udutu content -->
    </div> <!-- Content -->
    </div> <!-- Page -->
</body>
</html>
