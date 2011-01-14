<?php  // $Id: locallib.php,v 1.34.2.4 2007/04/12 09:22:20 csantossaenz Exp $

/// Constants and settings for module udutu
define('UDUTU_UPDATE_NEVER', '0');
define('UDUTU_UPDATE_ONCHANGE', '1');
define('UDUTU_UPDATE_EVERYDAY', '2');
define('UDUTU_UPDATE_EVERYTIME', '3');

define('UDUTU_SCO_ALL', 0);
define('UDUTU_SCO_DATA', 1);
define('UDUTU_SCO_ONLY', 2);

define('UDUTU_GRADESCOES', '0');
define('UDUTU_GRADEHIGHEST', '1');
define('UDUTU_GRADEAVERAGE', '2');
define('UDUTU_GRADESUM', '3');
$udutu_GRADE_METHOD = array (UDUTU_GRADESCOES => get_string('gradescoes', 'udutu'),
                             UDUTU_GRADEHIGHEST => get_string('gradehighest', 'udutu'),
                             UDUTU_GRADEAVERAGE => get_string('gradeaverage', 'udutu'),
                             UDUTU_GRADESUM => get_string('gradesum', 'udutu'));

define('UDUTU_HIGHESTATTEMPT', '0');
define('UDUTU_AVERAGEATTEMPT', '1');
define('UDUTU_FIRSTATTEMPT', '2');
define('UDUTU_LASTATTEMPT', '3');
$udutu_WHAT_GRADE = array (UDUTU_HIGHESTATTEMPT => get_string('highestattempt', 'udutu'),
                           UDUTU_AVERAGEATTEMPT => get_string('averageattempt', 'udutu'),
                           UDUTU_FIRSTATTEMPT => get_string('firstattempt', 'udutu'),
                           UDUTU_LASTATTEMPT => get_string('lastattempt', 'udutu'));

$udutu_POPUP_OPTIONS = array('resizable'=>1, 
                             'scrollbars'=>1, 
                             'directories'=>0, 
                             'location'=>0,
                             'menubar'=>0, 
                             'toolbar'=>0, 
                             'status'=>0);
$stdoptions = '';
foreach ($udutu_POPUP_OPTIONS as $popupopt => $value) {
    $stdoptions .= $popupopt.'='.$value;
    if ($popupopt != 'status') {
        $stdoptions .= ',';
    }
}

if (!isset($CFG->udutu_path)) {
		set_config('udutu_path','http://www.myudutu.com/myudutu/moodlelogin.aspx');
	}

if (!isset($CFG->udutu_maxattempts)) {
    set_config('udutu_maxattempts','6');
}

if (!isset($CFG->udutu_frameheight)) {
    set_config('udutu_frameheight','500');
}

if (!isset($CFG->udutu_framewidth)) {
    set_config('udutu_framewidth','100%');
}

if (!isset($CFG->udutu_updatetime)) {
    set_config('udutu_updatetime','2');
}

if (!isset($CFG->udutu_advancedsettings)) {
    set_config('udutu_advancedsettings','0');
}

if (!isset($CFG->udutu_windowsettings)) {
    set_config('udutu_windowsettings','0');
}



/**
 * Returns an array of the array of attempt options
 *
 * @return array an array of attempt options
 */
function udutu_get_attempts_array(){
    $attempts = array(0 => get_string('nolimit','udutu'),
                      1 => '1' . ' ' .get_string('attempt','udutu'));

    for ($i=2; $i<=6; $i++) {
        $attempts[$i] = $i . ' ' .get_string('attempts','udutu');
    }

    return $attempts;
}
//
// Repository configurations
//
$repositoryconfigfile = $CFG->dirroot.'/mod/resource/type/ims/repository_config.php';
$repositorybrowser = '/mod/resource/type/ims/finder.php';

/// Local Library of functions for module udutu

/**
* This function will permanently delete the given
* directory and all files and subdirectories.
*
* @param string $directory The directory to remove
* @return boolean
*/
function udutu_delete_files($directory) {
    if (is_dir($directory)) {
        $files=udutu_scandir($directory);
        foreach($files as $file) {
            if (($file != '.') && ($file != '..')) {
                if (!is_dir($directory.'/'.$file)) {
                    unlink($directory.'/'.$file);
                } else {
                    udutu_delete_files($directory.'/'.$file);
                }
            }
         //set_time_limit(5);
        }
        rmdir($directory);
        return true;
    }
    return false;
}

/**
* Given a diretory path returns the file list
*
* @param string $directory
* @return array
*/
function udutu_scandir($directory) {
    if (version_compare(phpversion(),'5.0.0','>=')) {
        return scandir($directory);
    } else {
        $files = array();
        if ($dh = opendir($directory)) {
            while (($file = readdir($dh)) !== false) {
               $files[] = $file;
            }
            closedir($dh);
        }
        return $files;
    }
}

/**
* Create a new temporary subdirectory with a random name in the given path
*
* @param string $strpath The udutu data directory
* @return string/boolean
*/
function udutu_tempdir($strPath)
{
    global $CFG;

    if (is_dir($strPath)) {
        do {
            // Create a random string of 8 chars
            $randstring = NULL;
            $lchar = '';
            $len = 8;
            for ($i=0; $i<$len; $i++) {
                $char = chr(rand(48,122));
                while (!ereg('[a-zA-Z0-9]', $char)){
                    if ($char == $lchar) continue;
                        $char = chr(rand(48,90));
                    }
                    $randstring .= $char;
                    $lchar = $char;
            } 
            $datadir='/'.$randstring;
        } while (file_exists($strPath.$datadir));
        mkdir($strPath.$datadir, $CFG->directorypermissions);
        @chmod($strPath.$datadir, $CFG->directorypermissions);  // Just in case mkdir didn't do it
        return $strPath.$datadir;
    } else {
        return false;
    }
}

function udutu_array_search($item, $needle, $haystacks, $strict=false) {
    if (!empty($haystacks)) {
        foreach ($haystacks as $key => $element) {
            if ($strict) {
                if ($element->{$item} === $needle) {
                    return $key;
                }
            } else {
                if ($element->{$item} == $needle) {
                    return $key;
                }
            }
        }
    }
    return false;
}

function udutu_repeater($what, $times) {
    if ($times <= 0) {
        return null;
    }
    $return = '';
    for ($i=0; $i<$times;$i++) {
        $return .= $what;
    }
    return $return;
}

function udutu_external_link($link) {
// check if a link is external
    $result = false;
    $link = strtolower($link);
    if (substr($link,0,7) == 'http://') {
        $result = true;
    } else if (substr($link,0,8) == 'https://') {
        $result = true;
    } else if (substr($link,0,4) == 'www.') {
        $result = true;
    }
    return $result;
}

/**
* Returns an object containing all datas relative to the given sco ID
*
* @param integer $id The sco ID
* @return mixed (false if sco id does not exists)
*/

function udutu_get_sco($id,$what=UDUTU_SCO_ALL) {
    if ($sco = get_record('udutu_scoes','id',$id)) {
        $sco = ($what == UDUTU_SCO_DATA) ? new stdClass() : $sco;
        if (($what != UDUTU_SCO_ONLY) && ($scodatas = get_records('udutu_scoes_data','scoid',$id))) {
            foreach ($scodatas as $scodata) {
                $sco->{$scodata->name} = $scodata->value;		
            }
		}
        elseif (($what != UDUTU_SCO_ONLY) && (!($scodatas = get_records('udutu_scoes_data','scoid',$id)))){		
                $sco->parameters = ''; 
            }
       
        return $sco;
    } else {
        return false;
    }
}
function udutu_insert_track($userid,$udutuid,$scoid,$attempt,$element,$value) {
	Echo "here";
    $id = null;
    if ($track = get_record_select('udutu_scoes_track',"userid='$userid' AND udutuid='$udutuid' AND scoid='$scoid' AND attempt='$attempt' AND element='$element'")) {
        $track->value = $value;
        $track->timemodified = time();
        $id = update_record('udutu_scoes_track',$track);
    } else {
        $track->userid = $userid;
        $track->udutuid = $udutuid;
        $track->scoid = $scoid;
        $track->attempt = $attempt;
        $track->element = $element;
        $track->value = addslashes($value);
        $track->timemodified = time();
        $id = insert_record('udutu_scoes_track',$track);
    }
    return $id;
}

function udutu_get_tracks($scoid,$userid,$attempt='') {
/// Gets all tracks of specified sco and user
    global $CFG;

    if (empty($attempt)) {
        if ($udutuid = get_field('udutu_scoes','udutu','id',$scoid)) {
            $attempt = udutu_get_last_attempt($udutuid,$userid);
        } else {
            $attempt = 1;
        }
    }
    $attemptsql = ' AND attempt=' . $attempt;
    if ($tracks = get_records_select('udutu_scoes_track',"userid=$userid AND scoid=$scoid".$attemptsql,'element ASC')) {
        $usertrack->userid = $userid;
        $usertrack->scoid = $scoid; 
        // Defined in order to unify udutu1.2 and udutu2004
        $usertrack->score_raw = '';
        $usertrack->status = '';
        $usertrack->total_time = '00:00:00';
        $usertrack->session_time = '00:00:00';
        $usertrack->timemodified = 0;
	 $usertrack->comments_from_learner_count = 0;
        $usertrack->comments_from_lms_count = 0;
        $usertrack->interactions_count = 0;
        $usertrack->objetives_count = 0;

        foreach ($tracks as $track) {
            $element = $track->element;
            $usertrack->{$element} = $track->value;
            switch ($element) {
                case 'cmi.core.lesson_status':
                case 'cmi.completion_status':
                    if ($track->value == 'not attempted') {
                        $track->value = 'notattempted';
                    }       
                    $usertrack->status = $track->value;
                break;  
                case 'cmi.core.score.raw':
                case 'cmi.score.raw':
                    $usertrack->score_raw = $track->value;
                break;  
                case 'cmi.core.session_time':
                case 'cmi.session_time':
                    $usertrack->session_time = $track->value;
                break;  
                case 'cmi.core.total_time':
                case 'cmi.total_time':
                    $usertrack->total_time = $track->value;
                break;  
            }       
            if (isset($track->timemodified) && ($track->timemodified > $usertrack->timemodified)) {
                $usertrack->timemodified = $track->timemodified;
            }   
		            if( ereg('cmi\.comments_from_learner\.[0-9]+\.comment', $element) ){
                $usertrack->comments_from_learner_count++;
            } else if(ereg('cmi\.comments_from_lms\.[0-9]+\.comment',$element)){
                $usertrack->comments_from_lms_count++;
            } else if( ereg('cmi\.interactions\.[0-9]+\.id', $element) ) {
                $usertrack->interactions_count++;
            } else if( ereg('cmi\.objectives\.[0-9]+\.id', $element) ) {
                $usertrack->objectives_count++;
            }
    
        }       
        return $usertrack;
    } else {
        return false;
    }
}

function udutu_get_user_data($userid) {
/// Gets user info required to display the table of udutu results
/// for report.php

    return get_record('user','id',$userid,'','','','','firstname, lastname, picture');
}

function udutu_grade_user_attempt($udutu, $userid, $attempt=1, $time=false) {
    $attemptscore = NULL; 
    $attemptscore->scoes = 0;
    $attemptscore->values = 0;
    $attemptscore->max = 0;
    $attemptscore->sum = 0;
    $attemptscore->lastmodify = 0;
    
    if (!$scoes = get_records('udutu_scoes','udutu',$udutu->id)) {
        return NULL;
    }

    $grademethod = $udutu->grademethod % 10;

    foreach ($scoes as $sco) { 
        if ($userdata=udutu_get_tracks($sco->id, $userid,$attempt)) {
            if (($userdata->status == 'completed') || ($userdata->status == 'passed')) {
                $attemptscore->scoes++;
            }       
            if (!empty($userdata->score_raw)) {
                $attemptscore->values++;
                $attemptscore->sum += $userdata->score_raw;
                $attemptscore->max = ($userdata->score_raw > $attemptscore->max)?$userdata->score_raw:$attemptscore->max;
                if (isset($userdata->timemodified) && ($userdata->timemodified > $attemptscore->lastmodify)) {
                    $attemptscore->lastmodify = $userdata->timemodified;
                } else {
                    $attemptscore->lastmodify = 0;
                }
            }       
        }       
    }
    switch ($grademethod) {
        case GRADEHIGHEST:
            $score = $attemptscore->max;
        break;  
        case GRADEAVERAGE:
            if ($attemptscore->values > 0) {
                $score = $attemptscore->sum/$attemptscore->values;
            } else {
                $score = 0;
            }       
        break;  
        case GRADESUM:
            $score = $attemptscore->sum;
        break;  
        case GRADESCOES:
            $score = $attemptscore->scoes;
        break;  
    }

    if ($time) {
        $result = new stdClass();
        $result->score = $score;
        $result->time = $attemptscore->lastmodify;
    } else {
        $result = $score;
    }

    return $result;
}

function udutu_grade_user($udutu, $userid, $time=false) {

    $whatgrade = intval($udutu->grademethod / 10);

    switch ($whatgrade) {
        case FIRSTATTEMPT:
            return udutu_grade_user_attempt($udutu, $userid, 1, $time);
        break;    
        case LASTATTEMPT:
            return udutu_grade_user_attempt($udutu, $userid, udutu_get_last_attempt($udutu->id, $userid), $time);
        break;
        case HIGHESTATTEMPT:
            $lastattempt = udutu_get_last_attempt($udutu->id, $userid);
            $maxscore = 0;
            $attempttime = 0;
            for ($attempt = 1; $attempt <= $lastattempt; $attempt++) {
                $attemptscore = udutu_grade_user_attempt($udutu, $userid, $attempt, $time);
                if ($time) {
                    if ($attemptscore->score > $maxscore) {
                        $maxscore = $attemptscore->score;
                        $attempttime = $attemptscore->time;
                    }
                } else {
                    $maxscore = $attemptscore > $maxscore ? $attemptscore: $maxscore;
                }
            }
            if ($time) {
                $result = new stdClass();
                $result->score = $maxscore;
                $result->time = $attempttime;
                return $result;
            } else {
               return $maxscore;
            }
        break;
        case AVERAGEATTEMPT:
            $lastattempt = udutu_get_last_attempt($udutu->id, $userid);
            $sumscore = 0;
            for ($attempt = 1; $attempt <= $lastattempt; $attempt++) {
                $attemptscore = udutu_grade_user_attempt($udutu, $userid, $attempt, $time);
                if ($time) {
                    $sumscore += $attemptscore->score;
                } else {
                    $sumscore += $attemptscore;
                }
            }

            if ($lastattempt > 0) {
                $score = $sumscore / $lastattempt;
            } else {
                $score = 0;
            }

            if ($time) {
                $result = new stdClass();
                $result->score = $score;
                $result->time = $attemptscore->time;
                return $result;
            } else {
               return $score;
            }
        break;
    }
}

function udutu_count_launchable($udutuid,$organization='') {
    $strorganization = '';
    if (!empty($organization)) {
        $strorganization = " AND organization='$organization'";
    }
    return count_records_select('udutu_scoes',"udutu=$udutuid$strorganization AND launch<>''");
}

function udutu_get_last_attempt($udutuid, $userid) {
/// Find the last attempt number for the given user id and udutu id
    if ($lastattempt = get_record('udutu_scoes_track', 'userid', $userid, 'udutuid', $udutuid, '', '', 'max(attempt) as a')) {
        if (empty($lastattempt->a)) {
            return '1';
        } else {
            return $lastattempt->a;
        }
    }
}

function udutu_course_format_display($user,$course) {
    global $CFG;

    $strupdate = get_string('update');
    $strmodule = get_string('modulename','udutu');
    $context = get_context_instance(CONTEXT_COURSE,$course->id);

    echo '<div class="mod-udutu">';
    if ($udutus = get_all_instances_in_course('udutu', $course)) {
        // The module udutu activity with the least id is the course  
        $udutu = current($udutus);
        if (! $cm = get_coursemodule_from_instance('udutu', $udutu->id, $course->id)) {
            error('Course Module ID was incorrect');
        }
        $colspan = '';
        $headertext = '<table width="100%"><tr><td class="title">'.get_string('name').': <b>'.format_string($udutu->name).'</b>';
        if (has_capability('moodle/course:manageactivities', $context)) {
            if (isediting($course->id)) {
                // Display update icon
                $path = $CFG->wwwroot.'/course';
                $headertext .= '<span class="commands">'.
                        '<a title="'.$strupdate.'" href="'.$path.'/mod.php?update='.$cm->id.'&amp;sesskey='.sesskey().'">'.
                        '<img src="'.$CFG->pixpath.'/t/edit.gif" class="iconsmall" alt="'.$strupdate.'" /></a></span>';
            }
            $headertext .= '</td>';
            // Display report link
            $trackedusers = get_record('udutu_scoes_track', 'udutuid', $udutu->id, '', '', '', '', 'count(distinct(userid)) as c');
            if ($trackedusers->c > 0) {
                $headertext .= '<td class="reportlink">'.
                              '<a '.$CFG->frametarget.'" href="'.$CFG->wwwroot.'/mod/udutu/report.php?id='.$cm->id.'">'.
                               get_string('viewallreports','udutu',$trackedusers->c).'</a>';
            } else {
                $headertext .= '<td class="reportlink">'.get_string('noreports','udutu');
            }
            $colspan = ' colspan="2"';
        } 
        $headertext .= '</td></tr><tr><td'.$colspan.'>'.format_text(get_string('summary').':<br />'.$udutu->summary).'</td></tr></table>';
        print_simple_box($headertext,'','100%');
        udutu_view_display($user, $udutu, 'view.php?id='.$course->id, $cm, '100%');
    } else {
        if (has_capability('moodle/course:update', $context)) {
            // Create a new activity
            redirect($CFG->wwwroot.'/course/mod.php?id='.$course->id.'&amp;section=0&sesskey='.sesskey().'&amp;add=udutu');
        } else {
            notify('Could not find a udutu course here');
        }
    }
    echo '</div>';
}

function udutu_view_display ($user, $udutu, $action, $cm, $boxwidth='') {
    global $CFG;

    if ($udutu->updatefreq == UDUTU_UPDATE_EVERYTIME){
        $udutu->instance = $udutu->id;
        udutu_update_instance($udutu);
    }

    $organization = optional_param('organization', '', PARAM_INT);

    print_simple_box_start('center',$boxwidth);
?>
        <div class="structurehead"><?php print_string('contents','udutu') ?></div>
<?php
    if (empty($organization)) {
        $organization = $udutu->launch;
    }
    if ($orgs = get_records_select_menu('udutu_scoes',"udutu='$udutu->id' AND organization='' AND launch=''",'id','id,title')) {
        if (count($orgs) > 1) {
 ?>
            <div class='center'>
                <?php print_string('organizations','udutu') ?>
                <form id='changeorg' method='post' action='<?php echo $action ?>'>
                    <?php choose_from_menu($orgs, 'organization', "$organization", '','submit()') ?>
                </form>
            </div>
<?php
        }
    }
    $orgidentifier = '';
    if ($sco = udutu_get_sco($organization, UDUTU_SCO_ONLY)) {
        if (($sco->organization == '') && ($sco->launch == '')) {
            $orgidentifier = $sco->identifier;
        } else {
            $orgidentifier = $sco->organization;
        }
    }

/*
 $orgidentifier = '';
    if ($org = get_record('udutu_scoes','id',$organization)) {
        if (($org->organization == '') && ($org->launch == '')) {
            $orgidentifier = $org->identifier;
        } else {
            $orgidentifier = $org->organization;
        }
    }*/

    $udutu->version = strtolower(clean_param($udutu->version, PARAM_SAFEDIR));   // Just to be safe
    if (!file_exists($CFG->dirroot.'/mod/udutu/datamodels/'.$udutu->version.'lib.php')) {
        $udutu->version = 'udutu_12';
    }
    require_once($CFG->dirroot.'/mod/udutu/datamodels/'.$udutu->version.'lib.php');
	

    $result = udutu_get_toc($user,$udutu,'structlist',$orgidentifier);
    $incomplete = $result->incomplete;
    echo $result->toc;
    print_simple_box_end();

?>
            <div class="center">
               <form id="theform" method="post" action="<?php echo $CFG->wwwroot ?>/mod/udutu/player.php?scoid=<?php echo $sco->id ?>&id=<?php echo $cm->id ?>"<?php echo $udutu->popup == 1?' target="newwin"':'' ?>>
              <?php
                  if ($udutu->hidebrowse == 0) {
                      print_string('mode','udutu');
					  echo '<input type="hidden" name="scoid" value="$sco->id" />'."\n";
                      echo ': <input type="radio" id="b" name="mode" value="browse" /><label for="b">'.get_string('browse','udutu').'</label>'."\n";
                      echo '<input type="radio" id="n" name="mode" value="normal" checked="checked" /><label for="n">'.get_string('normal','udutu')."</label>\n";
                  } else {
                      echo '<input type="hidden" name="mode" value="normal" />'."\n";
                  }
                  if (($incomplete === false) && (($result->attemptleft > 0)||($udutu->maxattempt == 0))) {
?>
                  <br />
                  <input type="checkbox" id="a" name="newattempt" />
                  <label for="a"><?php print_string('newattempt','udutu') ?></label>
<?php
                  }
              ?>
              <br />
              <input type="hidden" name="scoid"/>
              <input type="hidden" name="currentorg" value="<?php echo $orgidentifier ?>" />
              <input type="submit" value="<?php print_string('entercourse','udutu') ?>" />
              </form>
          </div>
<?php
}
function udutu_simple_play($udutu,$user) {
   $result = false;
  
   $scoes = get_records_select('udutu_scoes','udutu='.$udutu->id.' AND launch<>\'\'');
   
   if (count($scoes) == 1) {
       if ($udutu->skipview >= 1) {
           $sco = current($scoes);
           if (udutu_get_tracks($sco->id,$user->id) === false) {
				header('Location: player.php?a='.$udutu->id.'&scoid= '.$sco->id);
               $result = true;
           } else if ($udutu->skipview == 2) {
               header('Location: player.php?a='.$udutu->id.'&scoid= '.$sco->id);
               $result = true;
           }
       }
   }
   return $result;
}
/*
function udutu_simple_play($udutu,$user) {
    $result = false;
    if ($scoes = get_records_select('udutu_scoes','udutu='.$udutu->id.' AND launch<>""')) {
        if (count($scoes) == 1) {
            if ($udutu->skipview >= 1) {
                $sco = current($scoes);
                if (udutu_get_tracks($sco->id,$user->id) === false) {
                    header('Location: player.php?a='.$udutu->id.'&scoid='.$sco->id);
                    $result = true;
                } else if ($udutu->skipview == 2) {
                    header('Location: player.php?a='.$udutu->id.'&scoid='.$sco->id);
                    $result = true;
                }
            }
        }
    }
    return $result;
}
*/
function udutu_parse($udutu) {
    global $CFG,$repositoryconfigfile;
    
    if ($udutu->reference[0] == '#') {
        $reference = $CFG->repository.substr($udutu->reference,1);
    } else {
        $reference = $udutu->dir.'/'.$udutu->id;
    }
    // Parse udutu manifest
    if ($udutu->pkgtype == 'AICC') {
        require_once('datamodels/aicclib.php');
        $udutu->launch = udutu_parse_aicc($reference, $udutu->id);
    } else {
        require_once('datamodels/udutulib.php');
        if ($udutu->reference[0] == '#') {
            require_once($repositoryconfigfile);
        }

        $udutu->launch = udutu_parse_udutu($reference,$udutu->id);
    }
    return $udutu->launch;
}

/**
* Given a manifest path, this function will check if the manifest is valid
*
* @param string $manifest The manifest file
* @return object
*/
function udutu_validate_manifest($manifest) {
    $validation = new stdClass();
    if (is_file($manifest)) {
        $validation->result = true;
    } else {
        $validation->result = false;
        $validation->errors['reference'] = get_string('nomanifest','udutu');
    }
    return $validation;
}

/**
* Given a aicc package directory, this function will check if the course structure is valid
*
* @param string $packagedir The aicc package directory path
* @return object
*/
function udutu_validate_aicc($packagedir) {
    $validation = new stdClass();
    $validation->result = false;
    if (is_dir($packagedir)) {
        if ($handle = opendir($packagedir)) {
            while (($file = readdir($handle)) !== false) {
                $ext = substr($file,strrpos($file,'.'));
                if (strtolower($ext) == '.cst') {
                    $validation->result = true;
                    break;
                }
            }
            closedir($handle);
        }
    }
    if ($validation->result == false) {
        $validation->errors['reference'] = get_string('nomanifest','udutu');
    }
    return $validation;
}


function udutu_validate($data) {
    global $CFG;

    $validation = new stdClass();
    $validation->errors = array();

    if (!isset($data['course']) || empty($data['course'])) {
        $validation->errors['reference'] = get_string('missingparam','udutu');
        $validation->result = false;
        return $validation;
    }
    $courseid = $data['course'];                  // Course Module ID

    if (!isset($data['reference']) || empty($data['reference'])) {
        $validation->errors['reference'] = get_string('packagefile','udutu');
        $validation->result = false;
        return $validation;
    }
    $reference = $data['reference'];              // Package/manifest path/location

    $udutuid = $data['instance'];                 // udutu ID 
    $udutu = new stdClass();
    if (!empty($udutuid)) {
        if (!$udutu = get_record('udutu','id',$udutuid)) {
            $validation->errors['reference'] = get_string('missingparam','udutu');
            $validation->result = false;
            return $validation;
        }
    }

    if ($reference[0] == '#') {
        require_once($repositoryconfigfile);
        if ($CFG->repositoryactivate) {
            $reference = $CFG->repository.substr($reference,1).'/imsmanifest.xml';
        } else {
            $validation->errors['reference'] = get_string('badpackage','udutu');
            $validation->result = false;
            return $validation;
        }
    } else if (!udutu_external_link($reference)) {
        $reference = $CFG->dataroot.'/'.$courseid.'/'.$reference;
    }

    // Create a temporary directory to unzip package or copy manifest and validate package
    $tempdir = '';
    $udutudir = '';
    if ($udutudir = make_upload_directory("$courseid/$CFG->moddata/udutu")) {
        if ($tempdir = udutu_tempdir($udutudir)) {
            $localreference = $tempdir.'/'.basename($reference);
            copy ("$reference", $localreference);
            if (!is_file($localreference)) {
                $validation->errors['reference'] = get_string('badpackage','udutu');
                $validation->result = false;
            } else {
                $ext = strtolower(substr(basename($localreference),strrpos(basename($localreference),'.')));
                switch ($ext) {
                    case '.pif':
                    case '.zip':
                        if (!unzip_file($localreference, $tempdir, false)) {
                            $validation->errors['reference'] = get_string('unziperror','udutu');
                            $validation->result = false;
                        } else {
                            unlink ($localreference);
                            if (is_file($tempdir.'/imsmanifest.xml')) {
                                $validation = udutu_validate_manifest($tempdir.'/imsmanifest.xml');
                                $validation->pkgtype = 'udutu';
                            } else {
                                $validation = udutu_validate_aicc($tempdir);
                                if (($validation->result == 'regular') || ($validation->result == 'found')) {
                                    $validation->pkgtype = 'AICC';
                                } else {
                                    $validation->errors['reference'] = get_string('nomanifest','udutu');
                                    $validation->result = false;
                                }
                            }
                        }
                    break;
                    case '.xml':
                        if (basename($localreference) == 'imsmanifest.xml') {
                            $validation = udutu_validate_manifest($localreference);
                        } else {
                            $validation->errors['reference'] = get_string('nomanifest','udutu');
                            $validation->result = false;
                        }
                    break;
                    default: 
                        $validation->errors['reference'] = get_string('badpackage','udutu');
                        $validation->result = false;
                    break;
                }
            }
            if (is_dir($tempdir)) {
            // Delete files and temporary directory
                udutu_delete_files($tempdir);
            }
        } else {
            $validation->errors['reference'] = get_string('packagedir','udutu');
            $validation->result = false;
        }
    } else {
        $validation->errors['reference'] = get_string('datadir','udutu');
        $validation->result = false;
    }
    return $validation;
}

function udutu_check_package($data) {
    global $CFG, $COURSE;

    $courseid = $data->course;                  // Course Module ID
    $reference = $data->reference;              // Package path
    $udutuid = $data->instance;                 // udutu ID 

    $validation = new stdClass();

    if (!empty($courseid) && !empty($reference)) {
        $externalpackage = udutu_external_link($reference);

        $validation->launch = 0;
        $referencefield = $reference;
        if (empty($reference)) {
            $validation = null;
        } else if ($reference[0] == '#') {
            require_once($repositoryconfigfile);
            if ($CFG->repositoryactivate) {
                $referencefield = $reference.'/imsmanfest.xml';
                $reference = $CFG->repository.substr($reference,1).'/imsmanifest.xml';
            } else {
                $validation = null;
            }
        } else if (!$externalpackage) {
            $reference = $CFG->dataroot.'/'.$courseid.'/'.$reference;
        }

        if (!empty($udutuid)) {  
        //
        // udutu Update
        //
            if ((!empty($validation)) && (is_file($reference) || $externalpackage)){
                
                if (!$externalpackage) {
                    $mdcheck = md5_file($reference);
                } else if ($externalpackage){
                    if ($udutudir = make_upload_directory("$courseid/$CFG->moddata/udutu")) {
                        if ($tempdir = udutu_tempdir($udutudir)) {
                            copy ("$reference", $tempdir.'/'.basename($reference));
                            $mdcheck = md5_file($tempdir.'/'.basename($reference));
                            udutu_delete_files($tempdir);
                        }
                    }
                }
                
                if ($udutu = get_record('udutu','id',$udutuid)) {
                    if ($udutu->reference[0] == '#') {
                        require_once($repositoryconfigfile);
                        if ($CFG->repositoryactivate) {
                            $oldreference = $CFG->repository.substr($udutu->reference,1).'/imsmanifest.xml';
                        } else {
                            $oldreference = $udutu->reference;
                        }
                    } else if (!udutu_external_link($udutu->reference)) {
                        $oldreference = $CFG->dataroot.'/'.$courseid.'/'.$udutu->reference;
                    } else {
                        $oldreference = $udutu->reference;
                    }
                    $validation->launch = $udutu->launch;
                    if ((($oldreference == $reference) && ($mdcheck != $udutu->md5hash)) || ($oldreference != $reference)) {
                        // This is a new or a modified package
                        $validation->launch = 0;
                    } else {
                    // Old package already validated
                        if (strpos($udutu->version,'AICC') !== false) {
                            $validation->pkgtype = 'AICC';
                        } else {
                            $validation->pkgtype = 'udutu';
                        }
                    }
                } else {
                    $validation = null;
                }
            } else {
                $validation = null;
            }
        }
        //$validation->launch = 0;
        if (($validation != null) && ($validation->launch == 0)) {
        //
        // Package must be validated
        //
            $ext = strtolower(substr(basename($reference),strrpos(basename($reference),'.')));
            $tempdir = '';
            switch ($ext) {
                case '.pif':
                case '.zip':
                // Create a temporary directory to unzip package and validate package
                    $udutudir = '';
                    if ($udutudir = make_upload_directory("$courseid/$CFG->moddata/udutu")) {
                        if ($tempdir = udutu_tempdir($udutudir)) {
                            copy ("$reference", $tempdir.'/'.basename($reference));
                            unzip_file($tempdir.'/'.basename($reference), $tempdir, false);
                            if (!$externalpackage) {
                                unlink ($tempdir.'/'.basename($reference));
                            }
                            if (is_file($tempdir.'/imsmanifest.xml')) {
                                $validation = udutu_validate_manifest($tempdir.'/imsmanifest.xml');
                                $validation->pkgtype = 'udutu';
                            } else {
                                $validation = udutu_validate_aicc($tempdir);
                                $validation->pkgtype = 'AICC';
                            }
                        } else {
                            $validation = null;
                        }
                    } else {
                        $validation = null;
                    }
                break;
                case '.xml':
                    if (basename($reference) == 'imsmanifest.xml') {
                        if ($externalpackage) {
                            if ($udutudir = make_upload_directory("$courseid/$CFG->moddata/udutu")) {
                                if ($tempdir = udutu_tempdir($udutudir)) {
                                    copy ("$reference", $tempdir.'/'.basename($reference));
                                    if (is_file($tempdir.'/'.basename($reference))) {
                                        $validation = udutu_validate_manifest($tempdir.'/'.basename($reference));
                                    } else {
                                        $validation = null;
                                    }
                                }
                            }
                        } else {
                            $validation = udutu_validate_manifest($CFG->dataroot.'/'.$COURSE->id.'/'.$reference);
                        }
                        $validation->pkgtype = 'udutu';
                    } else {
                        $validation = null;
                    }
                break;
                default: 
                    $validation = null;
                break;
            }
            if ($validation == null) {
                if (is_dir($tempdir)) {
                // Delete files and temporary directory
                    udutu_delete_files($tempdir);
                }
            } else {
                if (($ext == '.xml') && (!$externalpackage)) {
                    $validation->datadir = dirname($referencefield);
                } else {
                    $validation->datadir = substr($tempdir,strlen($udutudir));
                }
                $validation->launch = 0;
            }
        }
    } else {
        $validation = null;
    }
    return $validation;
}

function udutu_get_count_users($udutuid, $groupingid=null) {

    global $CFG;

    if (!empty($CFG->enablegroupings) && !empty($groupingid)) {
        $sql = "SELECT COUNT(DISTINCT st.userid)
                FROM {$CFG->prefix}udutu_scoes_track st
                    INNER JOIN {$CFG->prefix}groups_members gm ON st.userid = gm.userid
                    INNER JOIN {$CFG->prefix}groupings_groups gg ON gm.groupid = gg.groupid
                WHERE st.udutuid = $udutuid AND gg.groupingid = $groupingid
                ";
    } else {
        $sql = "SELECT COUNT(DISTINCT st.userid)
                FROM {$CFG->prefix}udutu_scoes_track st
                WHERE st.udutuid = $udutuid
                ";
    }

    return(count_records_sql($sql));
}
?>
