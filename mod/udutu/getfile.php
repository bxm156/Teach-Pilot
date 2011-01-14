<?php
	$path = $_POST["fileURL"];
	$courseName = $_POST['courseName'];
	$moodleCourseID = $_POST['moodleCourseID'];


	require('../../config.php');
	$courseName = str_replace("'","",$courseName);
	$page = file_get_contents($path);
	$MyFile = $CFG->dataroot.'/'.$moodleCourseID.'/'.$courseName.'.zip';
	
	$handling = fopen($MyFile, 'w+');
	$StringData = $page;
	$Size = fwrite($handling, $StringData); 
	
    
	echo "<html>";
	echo "<head>";
	echo "<meta http-equiv='content-type' content='text/html; charset=utf-8' />";
	echo "<link rel='stylesheet' type='text/css' href='/../moodle/theme/standard/styles.php' />";
	echo "<link rel='stylesheet' type='text/css' href='/../moodle/theme/standardwhite/styles.php' />";
	echo "</head>";
	echo "<body>";
	if($Size > 0)
	echo $courseName." is uploaded succesfully.";
    else
    echo " Sorry there was a problem uploading ".$courseName;
	echo"<br><br>";
	echo "<input type='submit' value='Continue' onclick='return set_value(\"".$courseName.".zip"."\")'>";
	echo "</body>";
	echo '<script type="text/javascript">

        function set_value(txt) {
            opener.document.getElementById("id_reference_value").value = txt;
            opener.document.getElementById("id_success").style.visibility="visible";
            window.close();
        }

    </script>
    </html>';

	
?>