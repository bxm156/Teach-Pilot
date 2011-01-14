<?php // $Id: scorm_13lib.php,v 1.6 2007/01/16 14:03:15 bobopinna Exp $

function udutu_get_toc($user,$udutu,$liststyle,$currentorg='',$scoid='',$mode='normal',$attempt='',$play=false) {
    global $CFG;
	Echo 'Udutu_lib";

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
        $optionaldatas = array();
        foreach ($scoes as $sco) {
            if (!empty($sco->launch)) {
                if ($usertrack=udutu_get_tracks($sco->id,$user->id,$attempt)) {
                    if ($usertrack->status == '') {
                        $usertrack->status = 'notattempted';
                    }
                    $usertracks[$sco->identifier] = $usertrack;
                }
                if ($optionaldata = udutu_get_sco($sco->id, SCO_DATA)) {
                    $optionaldatas[$sco->identifier] = $optionaldata;
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
            $isvisible = false;
            if (isset($optionaldatas[$sco->identifier])) {
                if (!isset($optionaldatas[$sco->identifier]->isvisible) || 
                   (isset($optionaldatas[$sco->identifier]->isvisible) && ($optionaldatas[$sco->identifier]->isvisible == 'true'))) {
                    $isvisible = true;
                }
            }
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
            if ($isvisible) {
                $result->toc .= "\t\t<li>";
            }
            $nextsco = next($scoes);
            $nextisvisible = false;
            if (($nextsco !== false) && (isset($optionaldatas[$nextsco->identifier]))) {
                if (!isset($optionaldatas[$nextsco->identifier]->isvisible) || 
                   (isset($optionaldatas[$nextsco->identifier]->isvisible) && ($optionaldatas[$nextsco->identifier]->isvisible == 'true'))) {
                    $nextisvisible = true;
                }
            }
            if ($nextisvisible && ($nextsco !== false) && ($sco->parent != $nextsco->parent) && 
               (($level==0) || (($level>0) && ($nextsco->parent == $sco->identifier)))) {
                $sublist++;
                $icon = 'minus';
                if (isset($_COOKIE['hide:udutuitem'.$nextsco->id])) {
                    $icon = 'plus';
                }
                $result->toc .= '<a href="javascript:expandCollide(img'.$sublist.','.$sublist.','.$nextsco->id.');">'.
                                '<img id="img'.$sublist.'" src="'.$udutupixdir.'/'.$icon.'.gif" alt="'.$strexpand.'" title="'.$strexpand.'"/></a>';
            } else if ($isvisible) {
                $result->toc .= '<img src="'.$udutupixdir.'/spacer.gif" />';
            }
            if (empty($sco->title)) {
                $sco->title = $sco->identifier;
            }
            if (!empty($sco->launch)) {
                if ($isvisible) {
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
                            $statusicon = '<img src="'.$udutupixdir.'/'.$usertrack->status.'.gif" alt="'.$strstatus.'" title="'.$strstatus.'" />';
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
                        if (isset($usertrack->{'cmi.core.exit'}) && ($usertrack->{'cmi.core.exit'} == 'suspend')) {
                            $statusicon = '<img src="'.$udutupixdir.'/suspend.gif" alt="'.$strstatus.' - '.$strsuspended.'" title="'.$strstatus.' - '.$strsuspended.'" />';
                        }
                    } else {
                        if ($play && empty($scoid)) {
                            $scoid = $sco->id;
                        }
                        if ($sco->udututype == 'sco') {
                            $statusicon = '<img src="'.$udutupixdir.'/notattempted.gif" alt="'.get_string('notattempted','udutu').'" title="'.get_string('notattempted','udutu').'" />';
                            $incomplete = true;
                        } else {
                            $statusicon = '<img src="'.$udutupixdir.'/asset.gif" alt="'.get_string('asset','udutu').'" title="'.get_string('asset','udutu').'" />';
                        }
                    }

                    if ($sco->id == $scoid) {
                        $startbold = '<b>';
                        $endbold = '</b>';
                        $findnext = true;
                        $shownext = isset($optionaldatas[$sco->identifier]->next) ? $optionaldatas[$sco->identifier]->next : 0;
                        $showprev = isset($optionaldatas[$sco->identifier]->prev) ? $optionaldatas[$sco->identifier]->prev : 0;
                    }
                
                    if (($nextid == 0) && (udutu_count_launchable($udutu->id,$currentorg) > 1) && ($nextsco!==false) && (!$findnext)) {
                        if (!empty($sco->launch)) {
                            $previd = $sco->id;
                        }
                    }
                    require_once('sequencinglib.php');
                    if (udutu_seq_evaluate($sco->id,$usertracks)) {
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
                        $result->toc .= '&nbsp;'.format_string($sco->title)."</li>\n";
                    }
                }
            } else {
                $result->toc .= '&nbsp;'.format_string($sco->title)."</li>\n";
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
