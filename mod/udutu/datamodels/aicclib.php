<?php // $Id: aicclib.php,v 1.3.2.1 2007/03/15 21:06:53 bobopinna Exp $
function udutu_add_time($a, $b) {
    $aes = explode(':',$a);
    $bes = explode(':',$b);
    $aseconds = explode('.',$aes[2]);
    $bseconds = explode('.',$bes[2]);
    $change = 0;

    $acents = 0;  //Cents
    if (count($aseconds) > 1) {
        $acents = $aseconds[1];
    }
    $bcents = 0;
    if (count($bseconds) > 1) {
        $bcents = $bseconds[1];
    }
    $cents = $acents + $bcents;
    $change = floor($cents / 100);
    $cents = $cents - ($change * 100);
    if (floor($cents) < 10) {
        $cents = '0'. $cents;
    }

    $secs = $aseconds[0] + $bseconds[0] + $change;  //Seconds
    $change = floor($secs / 60);
    $secs = $secs - ($change * 60);
    if (floor($secs) < 10) {
        $secs = '0'. $secs;
    }

    $mins = $aes[1] + $bes[1] + $change;   //Minutes
    $change = floor($mins / 60);
    $mins = $mins - ($change * 60);
    if ($mins < 10) {
        $mins = '0' .  $mins;
    }

    $hours = $aes[0] + $bes[0] + $change;  //Hours
    if ($hours < 10) {
        $hours = '0' . $hours;
    }

    if ($cents != '0') {
        return $hours . ":" . $mins . ":" . $secs . '.' . $cents;
    } else {
        return $hours . ":" . $mins . ":" . $secs;
    }
}

/**
* Take the header row of an AICC definition file
* and returns sequence of columns and a pointer to
* the sco identifier column.
*
* @param string $row AICC header row
* @param string $mastername AICC sco identifier column
* @return mixed
*/
function udutu_get_aicc_columns($row,$mastername='system_id') {
    $tok = strtok(strtolower($row),"\",\n\r");
    $result->columns = array();
    $i=0;
    while ($tok) {
        if ($tok !='') {
            $result->columns[] = $tok;
            if ($tok == $mastername) {
                $result->mastercol = $i;
            }
            $i++;
        }
        $tok = strtok("\",\n\r");
    }
    return $result;
}

/**
* Given a colums array return a string containing the regular
* expression to match the columns in a text row.
*
* @param array $column The header columns
* @param string $remodule The regular expression module for a single column
* @return string
*/
function udutu_forge_cols_regexp($columns,$remodule='(".*")?,') {
    $regexp = '/^';
    foreach ($columns as $column) {
        $regexp .= $remodule;
    }
    $regexp = substr($regexp,0,-1) . '/';
    return $regexp;
}

function udutu_parse_aicc($pkgdir,$udutuid) {
    echo 'here';
    $version = 'AICC';
    $ids = array();
    $courses = array();
    $extaiccfiles = array('crs','des','au','cst','ort','pre','cmp');
    if ($handle = opendir($pkgdir)) {
        while (($file = readdir($handle)) !== false) {
            if ($file[0] != '.') {
                $ext = substr($file,strrpos($file,'.'));
                $extension = strtolower(substr($ext,1));
                if (in_array($extension,$extaiccfiles)) {
                    $id = strtolower(basename($file,$ext));
                    $ids[$id]->$extension = $file;
                }
            }
        }
        closedir($handle);
    }
    foreach ($ids as $courseid => $id) {
        if (isset($id->crs)) {
            if (is_file($pkgdir.'/'.$id->crs)) {
                $rows = file($pkgdir.'/'.$id->crs);
                foreach ($rows as $row) {
                    if (preg_match("/^(.+)=(.+)$/",$row,$matches)) {
                        switch (strtolower(trim($matches[1]))) {
                            case 'course_id':
                                $courses[$courseid]->id = trim($matches[2]);
                            break;
                            case 'course_title':
                                $courses[$courseid]->title = trim($matches[2]);
                            break;
                            case 'version':
                                $courses[$courseid]->version = 'AICC_'.trim($matches[2]);
                            break;
                        }
                    }
                }
            }
        }
        if (isset($id->des)) {
            $rows = file($pkgdir.'/'.$id->des);
            $columns = udutu_get_aicc_columns($rows[0]);
            $regexp = udutu_forge_cols_regexp($columns->columns);
            for ($i=1;$i<count($rows);$i++) {
                if (preg_match($regexp,$rows[$i],$matches)) {
                    for ($j=0;$j<count($columns->columns);$j++) {
                        $column = $columns->columns[$j];
                        $courses[$courseid]->elements[substr(trim($matches[$columns->mastercol+1]),1,-1)]->$column = substr(trim($matches[$j+1]),1,-1);
                    }
                }
            }
        }
        if (isset($id->au)) {
            $rows = file($pkgdir.'/'.$id->au);
            $columns = udutu_get_aicc_columns($rows[0]);
            $regexp = udutu_forge_cols_regexp($columns->columns);
            for ($i=1;$i<count($rows);$i++) {
                if (preg_match($regexp,$rows[$i],$matches)) {
                    for ($j=0;$j<count($columns->columns);$j++) {
                        $column = $columns->columns[$j];
                        $courses[$courseid]->elements[substr(trim($matches[$columns->mastercol+1]),1,-1)]->$column = substr(trim($matches[$j+1]),1,-1);
                    }
                }
            }
        }
        if (isset($id->cst)) {
            $rows = file($pkgdir.'/'.$id->cst);
            $columns = udutu_get_aicc_columns($rows[0],'block');
            $regexp = udutu_forge_cols_regexp($columns->columns,'(.+)?,');
            for ($i=1;$i<count($rows);$i++) {
                if (preg_match($regexp,$rows[$i],$matches)) {
                    for ($j=0;$j<count($columns->columns);$j++) {
                        if ($j != $columns->mastercol) {
                            $courses[$courseid]->elements[substr(trim($matches[$j+1]),1,-1)]->parent = substr(trim($matches[$columns->mastercol+1]),1,-1);
                        }
                    }
                }
            }
        }
        if (isset($id->ort)) {
            $rows = file($pkgdir.'/'.$id->ort);
        }
        if (isset($id->pre)) {
            $rows = file($pkgdir.'/'.$id->pre);
            $columns = udutu_get_aicc_columns($rows[0],'structure_element');
            $regexp = udutu_forge_cols_regexp($columns->columns,'(.+),');
            for ($i=1;$i<count($rows);$i++) {
                if (preg_match($regexp,$rows[$i],$matches)) {
                    $courses[$courseid]->elements[$columns->mastercol+1]->prerequisites = substr(trim($matches[1-$columns->mastercol+1]),1,-1);
                }
            }
        }
        if (isset($id->cmp)) {
            $rows = file($pkgdir.'/'.$id->cmp);
        }
    }
    //print_r($courses);

    $oldscoes = get_records('udutu_scoes','udutu',$udutuid);
    
    $launch = 0;
    if (isset($courses)) {
        foreach ($courses as $course) {
            unset($sco);
            $sco->identifier = $course->id;
            $sco->udutu = $udutuid;
            $sco->organization = '';
            $sco->title = $course->title;
            $sco->parent = '/';
            $sco->launch = '';
            $sco->udututype = '';

            //print_r($sco);
            if (get_record('udutu_scoes','udutu',$udutuid,'identifier',$sco->identifier)) {
                $id = update_record('udutu_scoes',$sco);
                unset($oldscoes[$id]);
            } else {
                $id = insert_record('udutu_scoes',$sco);
            }

            if ($launch == 0) {
                $launch = $id;
            }
            if (isset($course->elements)) {
                foreach($course->elements as $element) {
                    unset($sco);
                    $sco->identifier = $element->system_id;
                    $sco->udutu = $udutuid;
                    $sco->organization = $course->id;
                    $sco->title = $element->title;
                    if (strtolower($element->parent) == 'root') {
                        $sco->parent = '/';
                    } else {
                        $sco->parent = $element->parent;
                    }
                    if (isset($element->file_name)) {
                        $sco->launch = $element->file_name;
                        $sco->udututype = 'sco';
                    } else {
                        $element->file_name = '';
                        $sco->udututype = '';
                    }
                    if (!isset($element->prerequisites)) {
                        $element->prerequisites = '';
                    }
                    $sco->prerequisites = $element->prerequisites;
                    if (!isset($element->max_time_allowed)) {
                        $element->max_time_allowed = '';
                    }
                    $sco->maxtimeallowed = $element->max_time_allowed;
                    if (!isset($element->time_limit_action)) {
                        $element->time_limit_action = '';
                    }
                    $sco->timelimitaction = $element->time_limit_action;
                    if (!isset($element->mastery_score)) {
                        $element->mastery_score = '';
                    }
                    $sco->masteryscore = $element->mastery_score;
                    $sco->previous = 0;
                    $sco->next = 0;
                    if ($oldscoid = udutu_array_search('identifier',$sco->identifier,$oldscoes)) {
                        $sco->id = $oldscoid;
                        $id = update_record('udutu_scoes',$sco);
                        unset($oldscoes[$oldscoid]);
                    } else {
                        $id = insert_record('udutu_scoes',$sco);
                    }
                    if ($launch==0) {
                        $launch = $id;
                    }
                }
            }
        }
    }
    if (!empty($oldscoes)) {
        foreach($oldscoes as $oldsco) {
            delete_records('udutu_scoes','id',$oldsco->id);
            delete_records('udutu_scoes_track','scoid',$oldsco->id);
        }
    }
    set_field('udutu','version','AICC','id',$udutuid);
    return $launch;
}

function udutu_get_toc($user,$udutu,$liststyle,$currentorg='',$scoid='',$mode='normal',$attempt='',$play=false) {
    global $CFG;
    
    $strexpand = get_string('expcoll','udutu');
    $modestr = '';
    if ($mode == 'browse') {
        $modestr = '&amp;mode='.$mode;
    } 
    $udutupixdir = $CFG->modpixpath.'/udutu/pix';
    
    $result = new stdClass();
    $result->toc = "<ul id='0' class='$liststyle'>\n";
    $tocmenus = array();
    $result->prerequisites = true;
    $incomplete = false;
    
    //
    // Get the current organization infos
    //
    $organizationsql = '';
    if (!empty($currentorg)) {
        if (($organizationtitle = get_field('udutu_scoes','title','udutu',$udutu->id,'identifier',$currentorg)) != '') {
            $result->toc .= "\t<li>$organizationtitle</li>\n";
            $tocmenus[] = $organizationtitle;
        }
        $organizationsql = "AND organization='$currentorg'";
    }
    //
    // If not specified retrieve the last attempt number
    //
    if (empty($attempt)) {
        $attempt = udutu_get_last_attempt($udutu->id, $user->id);
    }

    $result->attemptleft = $udutu->maxattempt - $attempt;
    if ($scoes = get_records_select('udutu_scoes',"udutu='$udutu->id' $organizationsql order by id ASC")){
        //
        // Retrieve user tracking data for each learning object
        // 
        $usertracks = array();
        foreach ($scoes as $sco) {
            if (!empty($sco->launch)) {
                if ($usertrack=udutu_get_tracks($sco->id,$user->id,$attempt)) {
                    if ($usertrack->status == '') {
                        $usertrack->status = 'notattempted';
                    }
                    $usertracks[$sco->identifier] = $usertrack;
                }
            }
        }

        $level=0;
        $sublist=1;
        $previd = 0;
        $nextid = 0;
        $findnext = false;
        $parents[$level]='/';
        
        foreach ($scoes as $sco) {
            if ($parents[$level]!=$sco->parent) {
                if ($newlevel = array_search($sco->parent,$parents)) {
                    for ($i=0; $i<($level-$newlevel); $i++) {
                        $result->toc .= "\t\t</ul></li>\n";
                    }
                    $level = $newlevel;
                } else {
                    $i = $level;
                    $closelist = '';
                    while (($i > 0) && ($parents[$level] != $sco->parent)) {
                        $closelist .= "\t\t</ul></li>\n";
                        $i--;
                    }
                    if (($i == 0) && ($sco->parent != $currentorg)) {
                        $style = '';
                        
                        if (isset($_COOKIE['hide:udutuitem'.$sco->id])) {
                            $style = ' style="display: none;"';
                        }
                        $result->toc .= "\t\t<li><ul id='$sublist' class='$liststyle'$style>\n";
                        $level++;
                    } else {
                        $result->toc .= $closelist;
                        $level = $i;
                    }
                    $parents[$level]=$sco->parent;
                }
            }
            $result->toc .= "\t\t<li>";
            $nextsco = next($scoes);
            if (($nextsco !== false) && ($sco->parent != $nextsco->parent) && (($level==0) || (($level>0) && ($nextsco->parent == $sco->identifier)))) {
                $sublist++;
                $icon = 'minus';
                if (isset($_COOKIE['hide:udutuitem'.$nextsco->id])) {
                    $icon = 'plus';
                }
                $result->toc .= '<a href="javascript:expandCollide(img'.$sublist.','.$sublist.','.$nextsco->id.');"><img id="img'.$sublist.'" src="'.$udutupixdir.'/'.$icon.'.gif" alt="'.$strexpand.'" title="'.$strexpand.'"/></a>';
            } else {
                $result->toc .= '<img src="'.$udutupixdir.'/spacer.gif" />';
            }
            if (empty($sco->title)) {
                $sco->title = $sco->identifier;
            }
            if (!empty($sco->launch)) {
                $startbold = '';
                $endbold = '';
                $score = '';
                if (empty($scoid) && ($mode != 'normal')) {
                    $scoid = $sco->id;
                }
                if (isset($usertracks[$sco->identifier])) {
                    $usertrack = $usertracks[$sco->identifier];
                    $strstatus = get_string($usertrack->status,'udutu');
                    if ($sco->udututype == 'sco') {
                        $statusicon .= '<img src="'.$udutupixdir.'/'.$usertrack->status.'.gif" alt="'.$strstatus.'" title="'.$strstatus.'" />';
                    } else {
                        $statusicon = '<img src="'.$udutupixdir.'/assetc.gif" alt="'.get_string('assetlaunched','udutu').'" title="'.get_string('assetlaunched','udutu').'" />';
                    }
                    
                    if (($usertrack->status == 'notattempted') || ($usertrack->status == 'incomplete') || ($usertrack->status == 'browsed')) {
                        $incomplete = true;
                        if ($play && empty($scoid)) {
                            $scoid = $sco->id;
                        }
                    }
                    if ($usertrack->score_raw != '') {
                        $score = '('.get_string('score','udutu').':&nbsp;'.$usertrack->score_raw.')';
                    }
                    $strsuspended = get_string('suspended','udutu');
                    if ($usertrack->{'cmi.core.exit'} == 'suspend') {
                        $statusicon = '<img src="'.$udutupixdir.'/suspend.gif" alt="'.$strstatus.' - '.$strsuspended.'" title="'.$strstatus.' - '.$strsuspended.'" />';
                    }
                } else {
                    if ($play && empty($scoid)) {
                        $scoid = $sco->id;
                    }
                    $incomplete = true;
                    if ($sco->udututype == 'sco') {
                        $statusicon = '<img src="'.$udutupixdir.'/notattempted.gif" alt="'.get_string('notattempted','udutu').'" title="'.get_string('notattempted','udutu').'" />';
                    } else {
                        $statusicon = '<img src="'.$udutupixdir.'/asset.gif" alt="'.get_string('asset','udutu').'" title="'.get_string('asset','udutu').'" />';
                    }
                }
                if ($sco->id == $scoid) {
                    $startbold = '<b>';
                    $endbold = '</b>';
                    $findnext = true;
                    $shownext = $sco->next;
                    $showprev = $sco->previous;
                }
                
                if (($nextid == 0) && (udutu_count_launchable($udutu->id,$currentorg) > 1) && ($nextsco!==false) && (!$findnext)) {
                    if (!empty($sco->launch)) {
                        $previd = $sco->id;
                    }
                }
                if (empty($sco->prerequisites) || udutu_eval_prerequisites($sco->prerequisites,$usertracks)) {
                    if ($sco->id == $scoid) {
                        $result->prerequisites = true;
                    }
                    $url = $CFG->wwwroot.'/mod/udutu/player.php?a='.$udutu->id.'&amp;currentorg='.$currentorg.$modestr.'&amp;scoid='.$sco->id;
                    $result->toc .= $statusicon.'&nbsp;'.$startbold.'<a href="'.$url.'">'.format_string($sco->title).'</a>'.$score.$endbold."</li>\n";
                    $tocmenus[$sco->id] = udutu_repeater('&minus;',$level) . '&gt;' . format_string($sco->title);
                } else {
                    if ($sco->id == $scoid) {
                        $result->prerequisites = false;
                    }
                    $result->toc .= '&nbsp;'.$sco->title."</li>\n";
                }
            } else {
                $result->toc .= '&nbsp;'.$sco->title."</li>\n";
            }
            if (($nextsco !== false) && ($nextid == 0) && ($findnext)) {
                if (!empty($nextsco->launch)) {
                    $nextid = $nextsco->id;
                }
            }
        }
        for ($i=0;$i<$level;$i++) {
            $result->toc .= "\t\t</ul></li>\n";
        }
        
        if ($play) {
            $sco = get_record('udutu_scoes','id',$scoid);
            $sco->previd = $previd;
            $sco->nextid = $nextid;
            $result->sco = $sco;
            $result->incomplete = $incomplete;
        } else {
            $result->incomplete = $incomplete;
        }
    }
    $result->toc .= "\t</ul>\n";
    if ($udutu->hidetoc == 0) {
        $result->toc .= '
          <script type="text/javascript">
          //<![CDATA[
              function expandCollide(which,list,item) {
                  var nn=document.ids?true:false
                  var w3c=document.getElementById?true:false
                  var beg=nn?"document.ids.":w3c?"document.getElementById(":"document.all.";
                  var mid=w3c?").style":".style";

                  if (eval(beg+list+mid+".display") != "none") {
                      which.src = "'.$udutupixdir.'/plus.gif";
                      eval(beg+list+mid+".display=\'none\';");
                      new cookie("hide:udutuitem" + item, 1, 356, "/").set();
                  } else {
                      which.src = "'.$udutupixdir.'/minus.gif";
                      eval(beg+list+mid+".display=\'block\';");
                      new cookie("hide:udutuitem" + item, 1, -1, "/").set();
                  }
              }
          //]]>
          </script>'."\n";
    }
    
    $url = $CFG->wwwroot.'/mod/udutu/player.php?a='.$udutu->id.'&amp;currentorg='.$currentorg.$modestr.'&amp;scoid=';
    $result->tocmenu = popup_form($url,$tocmenus, "tocmenu", $sco->id, '', '', '', true);

    return $result;
}

?>
