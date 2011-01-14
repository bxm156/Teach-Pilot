<?php // $Id: version.php,v 1.49.2.1 2007/03/13 22:09:24 bobopinna Exp $

/////////////////////////////////////////////////////////////////////////////////
///  Code fragment to define the version of udutu
///  This fragment is called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////


// NOTE  The version below was accidentally set a month into the future!  We need to 
//       catch up now, so until 27th October please only increment in very tiny steps 
//       in HEAD, until we get past that date..

$module->version  = 2007031300;   // The (date) version of this module
$module->requires = 2007020200;   // The version of Moodle that is required
$module->cron     = 300;            // How often should cron check this module (seconds)?

?>
