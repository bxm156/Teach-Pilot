<?php // $Id: scorm_12lib.php,v 1.4.2.2 2007/04/12 09:27:22 csantossaenz Exp $

function udutu_eval_prerequisites($prerequisites,$usertracks) {
    $element = '';
    $stack = array();
    $statuses = array(
                'passed' => 'passed',
                'completed' => 'completed',
                'failed' => 'failed',
                'incomplete' => 'incomplete',
                'browsed' => 'browsed',
                'not attempted' => 'notattempted',
                'p' => 'passed',
                'c' => 'completed',
                'f' => 'failed',
                'i' => 'incomplete',
                'b' => 'browsed',
                'n' => 'notattempted'
                );
    $i=0;  
    while ($i<strlen($prerequisites)) {
        $symbol = $prerequisites[$i];
        switch ($symbol) {
            case '&':
            case '|':
                $symbol .= $symbol;
            case '~':
            case '(':
            case ')':
            case '*':
                $element = trim($element);
                
                if (!empty($element)) {
                    $element = trim($element);
                    if (isset($usertracks[$element])) {
                        $element = '((\''.$usertracks[$element]->status.'\' == \'completed\') || '.
                                  '(\''.$usertracks[$element]->status.'\' == \'passed\'))'; 
                    } else if (($operator = strpos($element,'=')) !== false) {
                        $item = trim(substr($element,0,$operator));
                        if (!isset($usertracks[$item])) {
                            return false;
                        }
                        
                        $value = trim(trim(substr($element,$operator+1)),'"');
                        if (isset($statuses[$value])) {
                            $status = $statuses[$value];
                        } else {
                            return false;
                        }
                                              
                        $element = '(\''.$usertracks[$item]->status.'\' == \''.$status.'\')';
                    } else if (($operator = strpos($element,'<>')) !== false) {
                        $item = trim(substr($element,0,$operator));
                        if (!isset($usertracks[$item])) {
                            return false;
                        }
                        
                        $value = trim(trim(substr($element,$operator+2)),'"');
                        if (isset($statuses[$value])) {
                            $status = $statuses[$value];
                        } else {
                            return false;
                        }
                        
                        $element = '(\''.$usertracks[$item]->status.'\' != \''.$status.'\')';
                    } else if (is_numeric($element)) {
                        if ($symbol == '*') {
                            $symbol = '';
                            $open = strpos($prerequisites,'{',$i);
                            $opened = 1;
                            $closed = 0;
                            for ($close=$open+1; (($opened > $closed) && ($close<strlen($prerequisites))); $close++) { 
                                 if ($prerequisites[$close] == '}') {
                                     $closed++;
                                 } else if ($prerequisites[$close] == '{') {
                                     $opened++;
                                 }
                            } 
                            $i = $close;
                            
                            $setelements = explode(',', substr($prerequisites, $open+1, $close-($open+1)-1));
                            $settrue = 0;
                            foreach ($setelements as $setelement) {
                                if (udutu_eval_prerequisites($setelement,$usertracks)) {
                                    $settrue++;
                                }
                            }
                            
                            if ($settrue >= $element) {
                                $element = 'true'; 
                            } else {
                                $element = 'false';
                            }
                        }
                    } else {
                        return false;
                    }
                    
                    array_push($stack,$element);
                    $element = '';
                }
                if ($symbol == '~') {
                    $symbol = '!';
                }
                if (!empty($symbol)) {
                    array_push($stack,$symbol);
                }
            break;
            default:
                $element .= $symbol;
            break;
        }
        $i++;
    }
    if (!empty($element)) {
        $element = trim($element);
        if (isset($usertracks[$element])) {
            $element = '((\''.$usertracks[$element]->status.'\' == \'completed\') || '.
                       '(\''.$usertracks[$element]->status.'\' == \'passed\'))'; 
        } else if (($operator = strpos($element,'=')) !== false) {
            $item = trim(substr($element,0,$operator));
            if (!isset($usertracks[$item])) {
                return false;
            }
            
            $value = trim(trim(substr($element,$operator+1)),'"');
            if (isset($statuses[$value])) {
                $status = $statuses[$value];
            } else {
                return false;
            }
            
            $element = '(\''.$usertracks[$item]->status.'\' == \''.$status.'\')';
        } else if (($operator = strpos($element,'<>')) !== false) {
            $item = trim(substr($element,0,$operator));
            if (!isset($usertracks[$item])) {
                return false;
            }
            
            $value = trim(trim(substr($element,$operator+1)),'"');
            if (isset($statuses[$value])) {
                $status = $statuses[$value];
            } else {
                return false;
            }
            
            $element = '(\''.$usertracks[$item]->status.'\' != \''.trim($status).'\')';
        } else {
            return false;
        }
        
        array_push($stack,$element);
    }
    return eval('return '.implode($stack).';');
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
            $isvisible = false;
            if ($optionaldatas = udutu_get_sco($sco->id, UDUTU_SCO_DATA)) {
                if (!isset($optionaldatas->isvisible) || (isset($optionaldatas->isvisible) && ($optionaldatas->isvisible == 'true'))) {
                    $isvisible = true;
                }
            }
            else
				$isvisible = true;
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
            if (($nextsco !== false) && ($optionaldatas = udutu_get_sco($nextsco->id, UDUTU_SCO_DATA))) {
                if (!isset($optionaldatas->isvisible) || (isset($optionaldatas->isvisible) && ($optionaldatas->isvisible == 'true'))) {
                    $nextisvisible = true;
                }
            }
            if ($nextisvisible && ($nextsco !== false) && ($sco->parent != $nextsco->parent) && (($level==0) || (($level>0) && ($nextsco->parent == $sco->identifier)))) {
                $sublist++;
                $icon = 'minus';
                if (isset($_COOKIE['hide:udutuitem'.$nextsco->id])) {
                    $icon = 'plus';
                }
                $result->toc .= '<a href="javascript:expandCollide(img'.$sublist.','.$sublist.','.$nextsco->id.');"><img id="img'.$sublist.'" src="'.$udutupixdir.'/'.$icon.'.gif" alt="'.$strexpand.'" title="'.$strexpand.'"/></a>';
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
                        $incomplete = true;
                        if ($sco->udututype == 'sco') {
                            $statusicon = '<img src="'.$udutupixdir.'/notattempted.gif" alt="'.get_string('notattempted','udutu').'" title="'.get_string('notattempted','udutu').'" />';
                        } else {
                            $statusicon = '<img src="'.$udutupixdir.'/asset.gif" alt="'.get_string('asset','udutu').'" title="'.get_string('asset','udutu').'" />';
                        }
                    }
                    if ($sco->id == $scoid) {
                        $scodata = udutu_get_sco($sco->id, UDUTU_SCO_DATA);
                        $startbold = '<b>';
                        $endbold = '</b>';
                        $findnext = true;
                        $shownext = isset($scodata->next) ? $scodata->next : 0;
                        $showprev = isset($scodata->previous) ? $scodata->previous : 0;
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
                        $result->toc .= $statusicon.'&nbsp;'.$sco->title."</li>\n";
                    }
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
            $sco = udutu_get_sco($scoid);
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
