<?php
require_once("includes/class.ldap.php");
require_once("includes/config.php");
$mysql = mysql_connect("localhost",$CFG->dbuser,$CFG->dbpass);
mysql_select_db($CFG->dbname,$mysql);
//Functions
function print_r_html($arr, $style = "display: none; margin-left: 10px;")
{ static $i = 0; $i++;
  echo "\n<div id=\"array_tree_$i\" class=\"array_tree\">\n";
  foreach($arr as $key => $val)
  { switch (gettype($val))
    { case "array":
        echo "<a onclick=\"document.getElementById('";
        echo "array_tree_element_".$i."').style.display = ";
        echo "document.getElementById('array_tree_element_$i";
        echo "').style.display == 'block' ?";
        echo "'none' : 'block';\"\n";
        echo "name=\"array_tree_link_$i\" href=\"#array_tree_link_$i\">".htmlspecialchars($key)."</a><br />\n";
        echo "<div class=\"array_tree_element_\" id=\"array_tree_element_$i\" style=\"$style\">";
        echo print_r_html($val);
        echo "</div>";
      break;
      case "integer":
        echo "<b>".htmlspecialchars($key)."</b> => <i>".htmlspecialchars($val)."</i><br />";
      break;
      case "double":
        echo "<b>".htmlspecialchars($key)."</b> => <i>".htmlspecialchars($val)."</i><br />";
      break;
      case "boolean":
        echo "<b>".htmlspecialchars($key)."</b> => ";
        if ($val)
        { echo "true"; }
        else
        { echo "false"; }
        echo  "<br />\n";
      break;
      case "string":
        echo "<b>".htmlspecialchars($key)."</b> => <code>".htmlspecialchars($val)."</code><br />";
      break;
      default:
        echo "<b>".htmlspecialchars($key)."</b> => ".gettype($val)."<br />";
      break; }
    echo "\n"; }
  echo "</div>\n"; } 
////
if(!isset($_POST['course']) or empty($_POST['course'])) 
{
	die("Invalid Course Name");	
}
$course = intval($_POST['course']);

if(!isset($_POST['role']) or empty($_POST['role']))
{
	die("Invalid Roles");
}
$roles = $_POST['role'];
$cas = $_POST['cas'];

//Read CSV file
$user = array();
//Add CAS First
$user[0]['cas'] = $cas;
$user[0]['role'] = $roles;
if (is_uploaded_file($_FILES['userList']['tmp_name'])) {
	$row = 1;
	$file = fopen($_FILES['userList']['tmp_name'],"r");
	while(!feof($file))
	{
		$line = fgetcsv($file);
		$user[$row]['cas'] = $line[0];
		$user[$row]['role'] = $line[1];
		$row++;
	}
	fclose($file);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Processing...</title>
</head>

<body>
<h1>
Processing...
</h1>
<?php
echo "Finding Context ID...";
$query = "SELECT `id` FROM `".$CFG->prefix."context` WHERE `contextlevel` = '50' AND `instanceid` = '".$course."' LIMIT 1";
$result = mysql_query($query,$mysql);
$row_result = mysql_fetch_assoc($result);
$contextID = $row_result['id'];
?>
Imported Info:<br />
Course ID: <?php echo $course; ?><br />
Context ID: <?php echo $contextID; ?><br />
Enroll List and Roles:<br />
<blockquote>
<?php print_r_html($user); ?>
</blockquote>
<h2>
Step 1: Analyze Users
</h2>
<?php
$list = array();
foreach($user as $usr)
{
	$listArray[] = $usr['cas'];
	$list = implode("' OR `username` = '",$listArray);
	$role[$usr['cas']] = $usr['role'];
	
}
	echo "Building Query...";
	$query = "SELECT `id`,`username` FROM `".$CFG->prefix."user` WHERE `username` = '".$list."' LIMIT ".count($listArray);
	echo "Done <br />";
	echo "Retrieving users in database....";
	$result = mysql_query($query,$mysql);
	
	$userDatabase = array();
	$row = 0;
	while($row_result = mysql_fetch_assoc($result))
	{
		$userDatabase[$row]['id'] = $row_result['id'];
		$userDatabase[$row]['cas'] = $row_result['username'];
		$row++;
	}
	echo count($userDatabase);
	echo " Done <br />";
	//Of the users in the database, we should remove the ones that may already be assigned to the class
	echo "Checking for duplicates...";
	foreach($userDatabase as $key=>$usr) 
	{
		$query = "SELECT `id` FROM `".$CFG->prefix."role_assignments` WHERE `userid` = '".$usr['id']."' AND `contextid` = '".$contextID."' LIMIT 1";
		$result = mysql_query($query,$mysql);
		$num = mysql_num_rows($result);
		if($num == 1)
		{
			unset($userDatabase[$key]);
		}
	}
	print_r_html($userDatabase);
	echo "Done <br />";
	//
	echo "Crosschecking users in database with users in database for users to add...";
	$usersToAdd = $user;
	foreach($userDatabase as $usr)
	{
		$cas = $usr['cas'];
		
		foreach($usersToAdd as $key=>$user_csv)
		{
			if($user_csv['cas'] == $cas) {
				unset($usersToAdd[$key]);
			}
		}
	}
	echo count($usersToAdd)." Done <br />";
	?>
	<blockquote>
	<?php print_r_html($usersToAdd); ?>
    </blockquote>
	<?php
	echo "Populating list with LDAP...";
	$ldap = new Tool_LDAP();
	foreach($usersToAdd as $key=>$usr)
	{
		$result = $ldap->getValuesFromCas($usr['cas']);
		if($result) {
			$usersToAdd[$key]['firstName'] = $result['firstName'];
			$usersToAdd[$key]['lastName'] = $result['lastName'];
			$usersToAdd[$key]['mail'] = $result['mail'];
		}
	}
	echo " Done<br />";
	?>
    <blockquote>
    <?php print_r_html($usersToAdd); ?>
    </blockquote>
    <h2>
    Step 2: Add Necessary Users
    </h2>
    <?php
	echo "Adding Users...";
	$count = 0;
	foreach ($usersToAdd as $key=>$usr)
	{
		$query = "INSERT INTO `".$CFG->prefix."user` SET `auth` = 'cas', `confirmed` = '1', `mnethostid` = '1', `username` = '".$usr['cas']."', `password` = 'not cached', `firstname` = '".$usr['firstName']."', `lastname` = '".$usr['lastName']."', `email` = '".$usr['mail']."', `city` = 'Cleveland', `country` = 'US', `timemodified` = '".time()."'";
		mysql_query($query,$mysql);
		$id = mysql_insert_id($mysql);
		$usersToAdd[$key]['id'] = $id;
		$count++;
	}
	echo $count." Done<br />";
	echo "Compiling list of users...";
	$listOfUsers = array();
	$count = 0;
	foreach($userDatabase as $usr)
	{
		$listOfUsers[$count]['cas'] = $usr['cas'];
		$listOfUsers[$count]['id'] = $usr['id'];
		$listOfUsers[$count]['role'] = $role[$usr['cas']];
		$count++;
	}
	foreach($usersToAdd as $usr)
	{
		$listOfUsers[$count]['cas'] = $usr['cas'];
		$listOfUsers[$count]['id'] = $usr['id'];
		$listOfUsers[$count]['role'] = $role[$usr['cas']];
		$count++;
	}
	echo "Done <br />";
	?>
    <blockquote>
    <?php print_r_html($listOfUsers); ?>
    </blockquote>

	<h2>
    Step 3: Assigning Roles
    </h2>
    <?php
	echo "Inserting Roles...";
	$roleID['professor'] = 3;
	$roleID['ta'] = 4;
	$roleID['student'] = 5;
	$count = 0;
	$insert = array();
	foreach($listOfUsers as $usr)
	{
		$insert[] = "(".$roleID[$usr['role']].", {$contextID},".$usr['id'].",".time().",".time().",2,'manual')";
		$count++;
	}
	$query = "INSERT INTO `".$CFG->prefix."role_assignments` (`roleid`,`contextid`,`userid`,`timestart`,`timemodified`,`modifierid`,`enrol`) VALUES ";
	$query .= implode(',',$insert);
	mysql_query($query,$mysql);
	echo $count." Done <br />";
	?>
</body>
</html>