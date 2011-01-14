<?php  // $Id: lib.php,v 1.83.2.2 2007/04/10 14:21:29 csantossaenz Exp $

/**
* Given an object containing all the necessary data,
* (defined by the form in mod.html) this function
* will create a new instance and return the id number
* of the new instance.
*
* @param mixed $udutu Form data
* @return int
*/
require_once('locallib.php');
function udutu_add_instance($udutu) {
    global $CFG;

    require_once('locallib.php');

    if (($packagedata = udutu_check_package($udutu)) != null) {
        $udutu->pkgtype = $packagedata->pkgtype;
        $udutu->datadir = $packagedata->datadir;
        $udutu->launch = $packagedata->launch;
        $udutu->parse = 1;

        $udutu->timemodified = time();
        if (!udutu_external_link($udutu->reference)) {
            $udutu->md5hash = md5_file($CFG->dataroot.'/'.$udutu->course.'/'.$udutu->reference);
        } else {
            $udutu->dir = $CFG->dataroot.'/'.$udutu->course.'/moddata/udutu';
            $udutu->md5hash = md5_file($udutu->dir.$udutu->datadir.'/'.basename($udutu->reference));
        }

        $udutu = udutu_option2text($udutu);
        $udutu->width = str_replace('%','',$udutu->width);
        $udutu->height = str_replace('%','',$udutu->height);

        //sanitize submitted values a bit
        $udutu->width = clean_param($udutu->width, PARAM_INT);
        $udutu->height = clean_param($udutu->height, PARAM_INT);

        if (!isset($udutu->whatgrade)) {
            $udutu->whatgrade = 0;
        }
        $udutu->grademethod = ($udutu->whatgrade * 10) + $udutu->grademethod;

        $id = insert_record('udutu', $udutu);

        if (udutu_external_link($udutu->reference) || ((basename($udutu->reference) != 'imsmanifest.xml') && ($udutu->reference[0] != '#'))) {
            // Rename temp udutu dir to udutu id
            $udutu->dir = $CFG->dataroot.'/'.$udutu->course.'/moddata/udutu';
            rename($udutu->dir.$udutu->datadir,$udutu->dir.'/'.$id);
        }

        // Parse udutu manifest
        if ($udutu->parse == 1) {
            $udutu->id = $id;
            $udutu->launch = udutu_parse($udutu);
            set_field('udutu','launch',$udutu->launch,'id',$udutu->id);
        }

        return $id;
    } else {
        error(get_string('badpackage','udutu'));
    }
}

/**
* Given an object containing all the necessary data,
* (defined by the form in mod.html) this function
* will update an existing instance with new data.
*
* @param mixed $udutu Form data
* @return int
*/
function udutu_update_instance($udutu) {
    global $CFG;

    require_once('locallib.php');

    if (($packagedata = udutu_check_package($udutu)) != null) {
        $udutu->pkgtype = $packagedata->pkgtype;
        if ($packagedata->launch == 0) {
            $udutu->launch = $packagedata->launch;
            $udutu->datadir = $packagedata->datadir;
            $udutu->parse = 1;
            if (!udutu_external_link($udutu->reference)) {
                $udutu->md5hash = md5_file($CFG->dataroot.'/'.$udutu->course.'/'.$udutu->reference);
            } else {
                $udutu->dir = $CFG->dataroot.'/'.$udutu->course.'/moddata/udutu';
                $udutu->md5hash = md5_file($udutu->dir.$udutu->datadir.'/'.basename($udutu->reference));
            }
        } else {
            $udutu->parse = 0;
        }
    }

    $udutu->timemodified = time();
    $udutu->id = $udutu->instance;

    $udutu = udutu_option2text($udutu);
    $udutu->width = str_replace('%','',$udutu->width);
    $udutu->height = str_replace('%','',$udutu->height);

    if (!isset($udutu->whatgrade)) {
        $udutu->whatgrade = 0;
    }
    $udutu->grademethod = ($udutu->whatgrade * 10) + $udutu->grademethod;

    // Check if udutu manifest needs to be reparsed
    if ($udutu->parse == 1) {
        $udutu->dir = $CFG->dataroot.'/'.$udutu->course.'/moddata/udutu';
        if (is_dir($udutu->dir.'/'.$udutu->id)) {
            udutu_delete_files($udutu->dir.'/'.$udutu->id);
        }
        if (isset($udutu->datadir) && ($udutu->datadir != $udutu->id) && 
           (udutu_external_link($udutu->reference) || ((basename($udutu->reference) != 'imsmanifest.xml') && ($udutu->reference[0] != '#')))) {
            rename($udutu->dir.$udutu->datadir,$udutu->dir.'/'.$udutu->id);
        }

        $udutu->launch = udutu_parse($udutu);
    } else {
        $oldudutu = get_record('udutu','id',$udutu->id);
        $udutu->reference = $oldudutu->reference; // This fix a problem with Firefox when the teacher choose Cancel on overwrite question
    }
    
    return update_record('udutu', $udutu);
}

/**
* Given an ID of an instance of this module,
* this function will permanently delete the instance
* and any data that depends on it.
*
* @param int $id udutu instance id
* @return boolean
*/
function udutu_delete_instance($id) {

    global $CFG;

    if (! $udutu = get_record('udutu', 'id', $id)) {
        return false;
    }

    $result = true;

    $udutu->dir = $CFG->dataroot.'/'.$udutu->course.'/moddata/udutu';
    if (is_dir($udutu->dir.'/'.$udutu->id)) {
        // Delete any dependent files
        require_once('locallib.php');
        udutu_delete_files($udutu->dir.'/'.$udutu->id);
    }

    // Delete any dependent records
    if (! delete_records('udutu_scoes_track', 'udutuid', $udutu->id)) {
        $result = false;
    }
    if ($scoes = get_records('udutu_scoes','udutu',$udutu->id)) {
        foreach ($scoes as $sco) {
            if (! delete_records('udutu_scoes_data', 'scoid', $sco->id)) {
                $result = false;
            }
        } 
        delete_records('udutu_scoes', 'udutu', $udutu->id);
    } else {
        $result = false;
    }
    if (! delete_records('udutu', 'id', $udutu->id)) {
        $result = false;
    }

    /*if (! delete_records('udutu_sequencing_controlmode', 'udutuid', $udutu->id)) {
        $result = false;
    }
    if (! delete_records('udutu_sequencing_rolluprules', 'udutuid', $udutu->id)) {
        $result = false;
    }
    if (! delete_records('udutu_sequencing_rolluprule', 'udutuid', $udutu->id)) {
        $result = false;
    }
    if (! delete_records('udutu_sequencing_rollupruleconditions', 'udutuid', $udutu->id)) {
        $result = false;
    }
    if (! delete_records('udutu_sequencing_rolluprulecondition', 'udutuid', $udutu->id)) {
        $result = false;
    }
    if (! delete_records('udutu_sequencing_rulecondition', 'udutuid', $udutu->id)) {
        $result = false;
    }
    if (! delete_records('udutu_sequencing_ruleconditions', 'udutuid', $udutu->id)) {
        $result = false;
    }*/       
    return $result;
}

/**
* Return a small object with summary information about what a
* user has done with a given particular instance of this module
* Used for user activity reports.
*
* @param int $course Course id
* @param int $user User id
* @param int $mod  
* @param int $udutu The udutu id
* @return mixed
*/
function udutu_user_outline($course, $user, $mod, $udutu) { 

    $return = NULL;

    require_once('locallib.php');

    $return = udutu_grade_user($udutu, $user->id, true);

    return $return;
}

/**
* Print a detailed representation of what a user has done with
* a given particular instance of this module, for user activity reports.
*
* @param int $course Course id
* @param int $user User id
* @param int $mod  
* @param int $udutu The udutu id
* @return boolean
*/
function udutu_user_complete($course, $user, $mod, $udutu) {
    global $CFG;

    $liststyle = 'structlist';
    $udutupixdir = $CFG->modpixpath.'/udutu/pix';
    $now = time();
    $firstmodify = $now;
    $lastmodify = 0;
    $sometoreport = false;
    $report = '';
    
    if ($orgs = get_records_select('udutu_scoes',"udutu='$udutu->id' AND organization='' AND launch=''",'id','id,identifier,title')) {
        if (count($orgs) <= 1) {
            unset($orgs);
            $orgs[]->identifier = '';
        }
        $report .= '<div class="mod-udutu">'."\n";
        foreach ($orgs as $org) {
            $organizationsql = '';
            $currentorg = '';
            if (!empty($org->identifier)) {
                $report .= '<div class="orgtitle">'.$org->title.'</div>';
                $currentorg = $org->identifier;
                $organizationsql = "AND organization='$currentorg'";
            }
            $report .= "<ul id='0' class='$liststyle'>";
            if ($scoes = get_records_select('udutu_scoes',"udutu='$udutu->id' $organizationsql order by id ASC")){
                $level=0;
                $sublist=1;
                $parents[$level]='/';
                foreach ($scoes as $sco) {
                    if ($parents[$level]!=$sco->parent) {
                        if ($level>0 && $parents[$level-1]==$sco->parent) {
                            $report .= "\t\t</ul></li>\n";
                            $level--;
                        } else {
                            $i = $level;
                            $closelist = '';
                            while (($i > 0) && ($parents[$level] != $sco->parent)) {
                                $closelist .= "\t\t</ul></li>\n";
                                $i--;
                            }
                            if (($i == 0) && ($sco->parent != $currentorg)) {
                                $report .= "\t\t<li><ul id='$sublist' class='$liststyle'>\n";
                                $level++;
                            } else {
                                $report .= $closelist;
                                $level = $i;
                            }
                            $parents[$level]=$sco->parent;
                        }
                    }
                    $report .= "\t\t<li>";
                    $nextsco = next($scoes);
                    if (($nextsco !== false) && ($sco->parent != $nextsco->parent) && (($level==0) || (($level>0) && ($nextsco->parent == $sco->identifier)))) {
                        $sublist++;
                    } else {
                        $report .= '<img src="'.$udutupixdir.'/spacer.gif" alt="" />';
                    }

                    if ($sco->launch) {
                        require_once('locallib.php');
                        $score = '';
                        $totaltime = '';
                        if ($usertrack=udutu_get_tracks($sco->id,$user->id)) {
                            if ($usertrack->status == '') {
                                $usertrack->status = 'notattempted';
                            }
                            $strstatus = get_string($usertrack->status,'udutu');
                            $report .= "<img src='".$udutupixdir.'/'.$usertrack->status.".gif' alt='$strstatus' title='$strstatus' />";
                            if ($usertrack->timemodified != 0) {
                                if ($usertrack->timemodified > $lastmodify) {
                                    $lastmodify = $usertrack->timemodified;
                                }
                                if ($usertrack->timemodified < $firstmodify) {
                                    $firstmodify = $usertrack->timemodified;
                                }
                            }
                        } else {
                            if ($sco->udututype == 'sco') {
                                $report .= '<img src="'.$udutupixdir.'/'.'notattempted.gif" alt="'.get_string('notattempted','udutu').'" title="'.get_string('notattempted','udutu').'" />';
                            } else {
                                $report .= '<img src="'.$udutupixdir.'/'.'asset.gif" alt="'.get_string('asset','udutu').'" title="'.get_string('asset','udutu').'" />';
                            }
                        }
                        $report .= "&nbsp;$sco->title $score$totaltime</li>\n";
                        if ($usertrack !== false) {
                            $sometoreport = true;
                            $report .= "\t\t\t<li><ul class='$liststyle'>\n";
                            foreach($usertrack as $element => $value) {
                                if (substr($element,0,3) == 'cmi') {
                                    $report .= '<li>'.$element.' => '.$value.'</li>';
                                }
                            }
                            $report .= "\t\t\t</ul></li>\n";
                        } 
                    } else {
                        $report .= "&nbsp;$sco->title</li>\n";
                    }
                }
                for ($i=0;$i<$level;$i++) {
                    $report .= "\t\t</ul></li>\n";
                }
            }
            $report .= "\t</ul><br />\n";
        }
        $report .= "</div>\n";
    }
    if ($sometoreport) {
        if ($firstmodify < $now) {
            $timeago = format_time($now - $firstmodify);
            echo get_string('firstaccess','udutu').': '.userdate($firstmodify).' ('.$timeago.")<br />\n";
        }
        if ($lastmodify > 0) {
            $timeago = format_time($now - $lastmodify);
            echo get_string('lastaccess','udutu').': '.userdate($lastmodify).' ('.$timeago.")<br />\n";
        }
        echo get_string('report','udutu').":<br />\n";
        echo $report;
    } else {
        print_string('noactivity','udutu');
    }

    return true;
}

/**
* Given a list of logs, assumed to be those since the last login
* this function prints a short list of changes related to this module
* If isteacher is true then perhaps additional information is printed.
* This function is called from course/lib.php: print_recent_activity()
*
* @param reference $logs Logs reference
* @param boolean $isteacher
* @return boolean
*/
function udutu_print_recent_activity(&$logs, $isteacher=false) {
    return false;  // True if anything was printed, otherwise false
}

/**
* Function to be run periodically according to the moodle cron
* This function searches for things that need to be done, such
* as sending out mail, toggling flags etc ...
*
* @return boolean
*/
function udutu_cron () {

    global $CFG;

    require_once('locallib.php');

    $sitetimezone = $CFG->timezone;
    /// Now see if there are any digest mails waiting to be sent, and if we should send them
    if (!isset($CFG->udutu_updatetimelast)) {    // To catch the first time
        set_config('udutu_updatetimelast', 0);
    }

    $timenow = time();
    $updatetime = usergetmidnight($timenow, $sitetimezone) + ($CFG->udutu_updatetime * 3600);

    if ($CFG->udutu_updatetimelast < $updatetime and $timenow > $updatetime) {

        set_config('udutu_updatetimelast', $timenow);

        mtrace('Updating udutu packages which require daily update');//We are updating

        $udutusupdate = get_records('udutu','updatefreq',UPDATE_EVERYDAY);
        if (!empty($udutusupdate)) {
            foreach($udutusupdate as $udutuupdate) {
                $udutuupdate->instance = $udutuupdate->id;
                $id = udutu_update_instance($udutuupdate);
            }
        }
    }

    return true;
}

/**
* Given a udutu id return all the grades of that activity
*
* @param int $udutuid udutu instance id
* @return mixed
*/
function udutu_grades($udutuid) {

    global $CFG;

    if (!$udutu = get_record('udutu', 'id', $udutuid)) {
        return NULL;
    }

    if (($udutu->grademethod % 10) == 0) { // GRADESCOES
        if (!$return->maxgrade = count_records_select('udutu_scoes',"udutu='$udutuid' AND launch<>''")) {
            return NULL;
        }
    } else {
        $return->maxgrade = $udutu->maxgrade;
    }

    $return->grades = NULL;
    if ($scousers=get_records_select('udutu_scoes_track', "udutuid='$udutuid' GROUP BY userid", "", "userid,null")) {
        require_once('locallib.php');
        foreach ($scousers as $scouser) {
            $return->grades[$scouser->userid] = udutu_grade_user($udutu, $scouser->userid);
        }
    }
    return $return;
}

function udutu_get_view_actions() {
    return array('pre-view','view','view all','report');
}

function udutu_get_post_actions() {
    return array();
}

function udutu_option2text($udutu) {
    global $udutu_POPUP_OPTIONS;
    if (isset($udutu->popup)) {
        if ($udutu->popup == 1) {
            $optionlist = array();
            foreach ($udutu_POPUP_OPTIONS as $name => $option) {
                if (isset($udutu->$name)) {
                    $optionlist[] = $name.'='.$udutu->$name;
                } else {
                    $optionlist[] = $name.'=0';
                }
            }       
            $udutu->options = implode(',', $optionlist);
        } else {
            $udutu->options = '';
        } 
    } else {
        $udutu->popup = 0;
        $udutu->options = '';
    }
    return $udutu;
}

?>
