<?php  // $Id: loadSCO.php,v 1.33 2007/01/25 13:49:50 bobopinna Exp $
    require_once('../../config.php');
    require_once('locallib.php');

    $id = optional_param('id', '', PARAM_INT);       // Course Module ID, or
    $a = optional_param('a', '', PARAM_INT);         // udutu ID
    $scoid = required_param('scoid', PARAM_INT);     // sco ID

    $delayseconds = 2;  // Delay time before sco launch, used to give time to browser to define API

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
    } else if (!empty($a)) {
        if (! $udutu = get_record('udutu', 'id', $a)) {
            error('Course module is incorrect');
        }
        if (! $course = get_record('course', 'id', $udutu->course)) {
            error('Course is misconfigured');
        }
        if (! $cm = get_coursemodule_from_instance('udutu', $udutu->id, $course->id)) {
            error('Course Module ID was incorrect');
        }
    } else {
        error('A required parameter is missing');
    }

    require_login($course->id, false, $cm);
    if (!empty($scoid)) {
    //
    // Direct SCO request
    //
        if ($sco = udutu_get_sco($scoid)) {
            if ($sco->launch == '') {
                // Search for the next launchable sco
                if ($scoes = get_records_select('udutu_scoes','udutu='.$udutu->id." AND launch<>'' AND id>".$sco->id,'id ASC')) {
                    $sco = current($scoes);
                }
            }
        }
    }
    //
    // If no sco was found get the first of udutu package
    //
    if (!isset($sco)) {
        $scoes = get_records_select('udutu_scoes','udutu='.$udutu->id." AND launch<>''",'id ASC');
        $sco = current($scoes);
    }

    if ($sco->udututype == 'asset') {
       $attempt = udutu_get_last_attempt($udutu->id,$USER->id);
       $element = $udutu->version == 'udutu_13'?'cmi.completion_status':'cmi.core.lesson_status';
       $value = 'completed';
       $result = udutu_insert_track($USER->id, $udutu->id, $sco->id, $attempt, $element, $value);
    }
    
    //
    // Forge SCO URL
    //
    $connector = '';
    $version = substr($udutu->version,0,4);
    if ((isset($sco->parameters) && (!empty($sco->parameters))) || ($version == 'AICC')) {
        if (stripos($sco->launch,'?') !== false) {
            $connector = '&';
        } else {
            $connector = '?';
        }
        if ((isset($sco->parameters) && (!empty($sco->parameters))) && ($sco->parameters[0] == '?')) {
            $sco->parameters = substr($sco->parameters,1);
        }
    }
    
    if ($version == 'AICC') {
        if (isset($sco->parameters) && (!empty($sco->parameters))) {
            $sco->parameters = '&'. $sco->parameters;
        }
        $launcher = $sco->launch.$connector.'aicc_sid='.sesskey().'&aicc_url='.$CFG->wwwroot.'/mod/udutu/aicc.php'.$sco->parameters;
    } else {
        if (isset($sco->parameters) && (!empty($sco->parameters))) {
            $launcher = $sco->launch.$connector.$sco->parameters;
        } else {
            $launcher = $sco->launch;
        }
    }
    
    if (udutu_external_link($sco->launch)) {
        // Remote learning activity
        $result = $launcher;
    } else if ($udutu->reference[0] == '#') {
        // Repository
        require_once($repositoryconfigfile);
        $result = $CFG->repositorywebroot.substr($udutu->reference,1).'/'.$sco->launch;
    } else {
        if ((basename($udutu->reference) == 'imsmanifest.xml') && udutu_external_link($udutu->reference)) {
            // Remote manifest
            $result = dirname($udutu->reference).'/'.$launcher;
        } else {
            // Moodle internal package/manifest or remote (auto-imported) package
            if (basename($udutu->reference) == 'imsmanifest.xml') {
                $basedir = dirname($udutu->reference);
            } else {
                $basedir = $CFG->moddata.'/udutu/'.$udutu->id;
            }
            if ($CFG->slasharguments) {
                $result = $CFG->wwwroot.'/file.php/'.$udutu->course.'/'.$basedir.'/'.$launcher;
            } else {
                $result = $CFG->wwwroot.'/file.php?file=/'.$udutu->course.'/'.$basedir.'/'.$launcher;
            }
        }
    }
?>
<html>
    <head>
        <title>LoadSCO</title>
        <script type="text/javascript">
        //<![CDATA[
            setTimeout('document.location = "<?php echo $result ?>";',<?php echo $delayseconds ?>000);
        //]]>
        </script>
        <noscript>
            <meta http-equiv="refresh" content="<?php echo $delayseconds ?>;url=<?php echo $result ?>" />
        </noscript> 
    </head>
    <body>
        &nbsp;
    </body>
</html>
