<?php
define('SECURITY_CONSTANT', 1);

include('../config.php');

require_login();

$error = false;

if (function_exists('has_capability')) {
	if (!has_capability('moodle/legacy:admin', get_context_instance(CONTEXT_SYSTEM))) {
		$error = true;
	}
}
else if (!function_exists('isadmin') or !isadmin($USER->id)) {
	$error = true;
}

if (!$error) {
	include('./install/kernel/init.php');
	include('./install/components/header.php');
	include('./uninstall/components/center.php');
	include('./install/components/footer.php');
}
else {
	redirect($CFG->wwwroot);
}
?>