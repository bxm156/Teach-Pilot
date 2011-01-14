<?php
require_once("includes/config.php");
$mysql = mysql_connect("localhost",$CFG->dbuser,$CFG->dbpass);
mysql_select_db($CFG->dbname,$mysql);
$query = "SELECT `id`, `fullname` FROM `".$CFG->prefix."course` WHERE `format` <> 'site'";
$result = mysql_query($query,$mysql);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Add User</title>
</head>

<body>
<h1>
Add User Tool
</h1>
<form action="adduser.php" method="post" enctype="multipart/form-data" name="deleteCourse">

  <select name="course">
    <?php while($row_result = mysql_fetch_assoc($result)) { ?>
    <option value="<?php echo $row_result['id']; ?>"><?php echo $row_result['fullname']; ?></option>
    <?php }  ?>
  </select>
  <br />
  <label>Case ID
    <input name="cas" type="text" size="10" maxlength="10" />
  </label>
  <br />
  <label>Role
    <select name="role">
      <option value="student" selected="selected">Student</option>
      <option value="ta">TA</option>
      <option value="professor">Professor</option>
    </select>
  </label>
  <br />
  -- OR --
  <br />
  <input name="userList" type="file" id="userList" />
  <br />
    <input name="addUser" type="submit" value="Add User" id="addUser" />
 
</form>
</body>
</html>