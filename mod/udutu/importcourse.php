<?php 
require_once("../../config.php");
$id = required_param('id', PARAM_INT);
$host = $_SERVER["HTTP_HOST"];
$path = $_SERVER["PHP_SELF"];
$host = str_replace('importcourse.php','getfile.php',$host.$path);
//echo $host;

 echo "<html>";
 				echo "<head>";
 	    		echo "<meta http-equiv='content-type' content='text/html; charset=utf-8' />";
 				echo "<link rel='stylesheet' type='text/css' href='/../moodle/theme/standard/styles.php' />";
 				echo "<link rel='stylesheet' type='text/css' href='/../moodle/theme/standardwhite/styles.php' />";
				echo "</head>";
 			 	echo "<body onload='document.getElementById(\"redirectForm\").submit()'>";		
 			 	echo 	"<form id='redirectForm' method='POST' action=".$CFG->udutu_path.">";		 	
 			 	echo		"<input type='hidden' name='moodleCourseID' value='$id' />";
 			 	echo 		"<input type='hidden' name='host' value='$host'/>" ;
 			 	echo	"</form>";
			 	echo "</body>";
 			 	echo "</html>";
  

?>




