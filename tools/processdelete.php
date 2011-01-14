<?php 
require_once('../config.php');
require_once('../lib/moodlelib.php');
require_once('../course/lib.php');
$result = delete_course($_POST['courseID']);
fix_course_sortorder();
?>