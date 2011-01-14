<?php
require_once("includes/config.php");
$mysql = mysql_connect("localhost",$CFG->dbuser,$CFG->dbpass);
mysql_select_db($CFG->dbname,$mysql);
$query = "SELECT `id`, `name` FROM `".$CFG->prefix."course_categories`";
$result = mysql_query($query,$mysql);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Add Course Tool by Bryan Marty</title>
</head>
<body>
<h1>
Add Course Tool
</h1>
<form action="process.php" method="post" enctype="multipart/form-data" name="AddCourse">
  <label>Course Name
    <input type="text" name="courseName" id="courseName" />
  </label>
  <br />
  <label>Short Name
    <input type="text" name="shortName" id="shortName" />
  </label>
<br />
  <label>Category
  <select name="category">
  <?php while($row_result = mysql_fetch_assoc($result)) { ?>
    <option value="<?php echo $row_result['id']; ?>"><?php echo $row_result['name']; ?></option>
    <?php } ?>
  </select>
  </label>
  <br />
  <label>Professor Case ID
    <input name="prof" type="text" size="10" maxlength="10" />
  </label>
  <br />
  <label>User List
    <input name="userList" type="file" />
  </label>
  <br />
  <input name="Submit" type="submit" value="Add Course" />
</form>
</body>
</html>