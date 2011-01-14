<?php // $Id: sequencinglib.php,v 1.3 2007/01/16 14:03:15 bobopinna Exp $

function udutu_seq_evaluate($scoid,$usertracks) {
    return true;
}

function udutu_seq_overall ($scoid,$userid,$request) {
    $seq = udutu_seq_navigation($scoid,$userid,$request);
    if ($seq->navigation) {
        if ($seq->termination != null) {
            $seq = udutu_seq_termination($scoid,$userid,$seq);
        }
        if ($seq->sequencing != null) {
        //    udutu_sequencing_sequencing($scoid,$userid,$seq);
        }
        if ($seq->target != null) {
        //    udutu_sequencing_delivery($scoid,$userid,$seq);
        }
    }
    if ($seq->exception != null) {
    //    udutu_sequencing_exception($seq);
    }
    return 'true';
}

function udutu_seq_navigation ($scoid,$userid,$request) {
    /// Sequencing structure
    $seq = new stdClass();
    $seq->currentactivity = udutu_get_sco($scoid);
    $seq->active = udutu_seq_is('active',$scoid,$userid);
    $seq->suspended = udutu_seq_is('suspended',$scoid,$userid);
    $seq->navigation = null;
    $seq->termination = null;
    $seq->sequencing = null;
    $seq->target = null;
    $seq->exception = null;

    switch ($request) {
        case 'start_':
            if (empty($seq->currentactivity)) {
                $seq->navigation = true;
                $seq->sequencing = 'start';
            } else {
                $seq->exception = 'NB.2.1-1'; /// Sequencing session already begun
            } 
        break;
        case 'resumeall_':
            if (empty($seq->currentactivity)) {
                if ($track = get_record('udutu_scoes_track','scoid',$scoid,'userid',$userid,'name','suspendedactivity')) {
                    $seq->navigation = true;
                    $seq->sequencing = 'resumeall';
                } else {
                    $seq->exception = 'NB.2.1-3'; /// No suspended activity found
                }
            } else {
                $seq->exception = 'NB.2.1-1'; /// Sequencing session already begun
            } 
        break;
        case 'continue_':
        case 'previous_':
            if (!empty($seq->currentactivity)) {
                $sco = $seq->currentactivity;
                if ($sco->parent != '/') {
                    if ($parentsco = udutu_get_parent($sco)) {
                        if (isset($parentsco->flow) && ($parent->flow == true)) {
                            // Current activity is active !
                            if ($request == 'continue_') {
                                $seq->navigation = true;
                                $seq->termination = 'exit';
                                $seq->sequencing = 'continue';
                            } else {
                                if (isset($parentsco->forwardonly) && ($parent->forwardolny == false)) {
                                    $seq->navigation = true;
                                    $seq->termination = 'exit';
                                    $seq->sequencing = 'previous';
                                } else {
                                    $seq->exception = 'NB.2.1-5'; /// Violates control mode
                                }
                            }
                        }
                    }
                }
            } else {
                $seq->exception = 'NB.2.1-2'; /// Current activity not defined
            }
        break;
        case 'forward_':
        case 'backward_':
            $seq->exception = 'NB.2.1-7' ; /// None to be done, behavior not defined
        break;
        case 'exit_':
        case 'abandon_':
            if (!empty($seq->currentactivity)) {
                // Current activity is active !
                $seq->navigation = true;
                $seq->termination = substr($request,0,-1);
                $seq->sequencing = 'exit';
            } else {
                $seq->exception = 'NB.2.1-2'; /// Current activity not defined
            }
        case 'exitall_':
        case 'abandonall_':
        case 'suspendall_':
            if (!empty($seq->currentactivity)) {
                $seq->navigation = true;
                $seq->termination = substr($request,0,-1);
                $seq->sequencing = 'exit';
            } else {
                $seq->exception = 'NB.2.1-2'; /// Current activity not defined
            }
        break;
        default: /// {target=<STRING>}choice 
            if ($targetsco = get_record('udutu_scoes','udutu',$sco->udutu,'identifier',$request)) {
                if ($targetsco->parent != '/') {
                    $seq->target = $request;
                } else {
                    if ($parentsco = udutu_get_parent($targetsco)) {
                        if (isset($parentsco->choice) && ($parent->choice == true)) {
                            $seq->target = $request;
                        }
                    } 
                }
                if ($seq->target != null) {
                    if (empty($seq->currentactivity)) {
                        $seq->navigation = true;
                        $seq->sequencing = 'choice';
                    } else {
                        if (!$sco = udutu_get_sco($scoid)) {
                            return $seq;
                        }
                        if ($sco->parent != $target->parent) {
                            $ancestors = udutu_get_ancestors($sco);
                            $commonpos = udutu_find_common_ancestor($ancestors,$targetsco);
                            if ($commonpos !== false) {
                                if ($activitypath = array_slice($ancestors,0,$commonpos)) {
                                    foreach ($activitypath as $activity) {
                                        if (udutu_seq_is('active',$activity->id,$userid) && (isset($activity->choiceexit) && ($activity->choiceexit == false))) {
                                            $seq->navigation = false;
                                            $seq->termination = null;
                                            $seq->sequencing = null;
                                            $seq->target = null;
                                            $seq->exception = 'NB.2.1-8'; /// Violates control mode
                                            return $seq;
                                        }
                                    } 
                                } else {
                                    $seq->navigation = false;
                                    $seq->termination = null;
                                    $seq->sequencing = null;
                                    $seq->target = null;
                                    $seq->exception = 'NB.2.1-9';
                                }
                            }
                        }
                        // Current activity is active !
                        $seq->navigation = true;
                        $seq->sequencing = 'choice';
                    }
                } else {
                    $seq->exception = 'NB.2.1-10';  /// Violates control mode
                }
            } else {
                $seq->exception = 'NB.2.1-11';  /// Target activity does not exists
            }
        break;
    }
    return $seq;
}

function udutu_seq_temination ($seq,$userid) {
    if (empty($seq->currentactivity)) {
        $seq->termination = false;
        $seq->exception = 'TB.2.3-1';
        return $seq;
    }

    $sco = $seq->currentactivity;

    if ((($seq->termination == 'exit') || ($seq->termination == 'abandon')) && !$seq->active) {
        $seq->termination = false;
        $seq->exception = 'TB.2.3-2';
        return $seq;
    }
    switch ($seq->termination) {
        case 'exit':
            udutu_seq_end_attempt($sco,$userid);
            $seq = udutu_seq_exit_action_rules($seq,$userid);
            do {
                $exit = true;
                $seq = udutu_seq_post_cond_rules($seq,$userid);
                if ($seq->termination == 'exitparent') {
                    if ($sco->parent != '/') {
                        $sco = udutu_get_parent($sco);
                        $seq->currentactivity = $sco;
                        $seq->active = udutu_seq_is('active',$sco->id,$userid);
                        udutu_seq_end_attempt($sco,$userid);
                        $exit = false;
                    } else {
                        $seq->termination = false;
                        $seq->exception = 'TB.2.3-4';
                        return $seq;
                    }
                }
            } while (($exit == false) && ($seq->termination == 'exit'));
            if ($seq->termination == 'exit') {
                $seq->termination = true;
                return $seq;
            }
        case 'exitall':
            if ($seq->active) {
                udutu_seq_end_attempt($sco,$userid);
            }
            /// Terminate Descendent Attempts Process
            if ($ancestors = udutu_get_ancestors($sco)) { 
                foreach ($ancestors as $ancestor) {
                    udutu_seq_end_attempt($ancestor,$userid);
                    $seq->currentactivity = $ancestor;
                }
            }
            $seq->active = udutu_seq_is('active',$seq->currentactivity->id,$userid);
            $seq->termination = true;
        break;
        case 'suspendall':
            if (($seq->active) || ($seq->suspended)) {
                udutu_seq_set('suspended',$sco->id,$userid);
            } else {
                if ($sco->parent != '/') {
                    $parentsco = udutu_get_parent($sco);
                    udutu_seq_set('suspended',$parentsco->id,$userid);
                } else {
                    $seq->termination = false;
                    $seq->exception = 'TB.2.3-3';
                    // return $seq;
                }
            }
            if ($ancestors = udutu_get_ancestors($sco)) { 
                foreach ($ancestors as $ancestor) {
                    udutu_seq_set('active',$ancestor->id,$userid,0,false);
                    udutu_seq_set('suspended',$ancestor->id,$userid);
                    $seq->currentactivity = $ancestor;
                }
                $seq->termination = true;
                $seq->sequencing = 'exit';
            } else {
                $seq->termination = false;
                $seq->exception = 'TB.2.3-5';
            }
        break;
        case 'abandon':
            udutu_seq_set('active',$sco->id,$userid,0,false);
            $seq->active = null;
            $seq->termination = true;
        break;
        case 'abandonall':
            if ($ancestors = udutu_get_ancestors($sco)) { 
                foreach ($ancestors as $ancestor) {
                    udutu_seq_set('active',$ancestor->id,$userid,0,false);
                    $seq->currentactivity = $ancestor;
                }
                $seq->termination = true;
                $seq->sequencing = 'exit';
            } else {
                $seq->termination = false;
                $seq->exception = 'TB.2.3-6';
            }
        break;
        default:
            $seq->termination = false;
            $seq->exception = 'TB.2.3-7';
        break;
    }
    return $seq;
}

function udutu_seq_end_attempt($sco,$userid) {
    if (udutu_is_leaf($sco)) {
        if (!isset($sco->tracked) || ($sco->tracked == 1)) {
            if (!udutu_seq_is('suspended',$sco->id,$userid)) {
                if (!isset($sco->completionsetbycontent) || ($sco->completionsetbycontent == 0)) {
                    if (!udutu_seq_is('attemptprogressstatus',$sco->id,$userid,$attempt)) {
                        udutu_seq_set('attemptprogressstatus',$sco->id,$userid,$attempt);
                        udutu_seq_set('attemptcompletionstatus',$sco->id,$userid,$attempt);
                    }
                }
                if (!isset($sco->objectivesetbycontent) || ($sco->objectivesetbycontent == 0)) {
                    if ($sco->objectives) {
                        foreach ($objectives as $objective) {
                            if ($objective->primary) {
                                if (!udutu_seq_objective_progress_status($sco,$userid,$objective)) {
                                    udutu_seq_set('objectiveprogressstatus',$sco->id,$userid,$attempt);
                                    udutu_seq_set('objectivesatisfiedstatus',$sco->id,$userid,$attempt);
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
        if ($children = udutu_get_children($sco)) {
            $suspended = false;
            foreach ($children as $child) {
                if (udutu_seq_is('suspended',$child,$userid)) {
                    $suspended = true;
                    break;
                }
            }
            if ($suspended) {
                udutu_seq_set('suspended',$sco,$userid);
            } else { 
                udutu_seq_set('suspended',$sco,$userid,0,false);
            }
        }
    }
    udutu_seq_set('active',$sco,$userid,0,false);
    udutu_seq_overall_rollup($sco,$userid);
}

function udutu_seq_is($what, $scoid, $userid, $attempt=0) {
    /// Check if passed activity $what is active
    $active = false;
    if ($track = get_record('udutu_scoes_track','scoid',$scoid,'userid',$userid,'element',$what)) {
        $active = true;
    }
    return $active;
}

function udutu_seq_set($what, $scoid, $userid, $attempt=0, $value='true') {
    /// set passed activity to active or not
    if ($value == false) {
        delete_record('udutu_scoes_track','scoid',$scoid,'userid',$userid,'element',$what);
    } else {
        $sco = udutu_get_sco($scoid);
        udutu_insert_track($userid, $sco->udutu, $sco->id, 0, $what, $value);
    }
}

function udutu_seq_exit_action_rules($seq,$userid) {
    $sco = $seq->currentactivity;
    $ancestors = udutu_get_ancestors($sco);
    $exittarget = null;
    foreach (array_reverse($ancestors) as $ancestor) {
        if (udutu_seq_rules_check($ancestor,'exit') != null) {
            $exittarget = $ancestor;
            break;
        }
    }
    if ($exittarget != null) {
        $commons = array_slice($ancestors,0,udutu_find_common_ancestor($ancestors,$exittarget)); 
 
        /// Terminate Descendent Attempts Process
        if ($commons) { 
            foreach ($commons as $ancestor) {
                udutu_seq_end_attempt($ancestor,$userid);
                $seq->currentactivity = $ancestor;
            }
        }
    }
    return $seq;
}

function udutu_seq_post_cond_rules($seq,$userid) {
    $sco = $seq->currentactivity;
    if (!$seq->suspended) {
        if ($action = udutu_seq_rules_check($sco,'post') != null) {
            switch($action) {
                case 'retry':
                case 'continue':
                case 'previous':
                    $seq->sequencing = $action;
                break;
                case 'exitparent':
                case 'exitall':
                    $seq->termination = $action;
                break;
                case 'retryall':
                    $seq->termination = 'exitall';
                    $seq->sequencing = 'retry';
                break;
            }
        }
    }
    return $seq;
}

?>
