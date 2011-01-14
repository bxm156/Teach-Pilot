<?php
/******************************************************************************
 *                                                                            *
 * Copyright (c) 1999-2008  Wimba, All Rights Reserved.                       *
 *                                                                            *
 * COPYRIGHT:                                                                 *
 *      This software is the property of Wimba.                               *
 *      You can redistribute it and/or modify it under the terms of           *
 *      the GNU General Public License as published by the                    *
 *      Free Software Foundation.                                             *
 *                                                                            *
 * WARRANTIES:                                                                *
 *      This software is distributed in the hope that it will be useful,      *
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *      GNU General Public License for more details.                          *
 *                                                                            *
 *      You should have received a copy of the GNU General Public License     *
 *      along with the Wimba Moodle Integration;                              *
 *      if not, write to the Free Software Foundation, Inc.,                  *
 *      51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA                *
 *                                                                            *
 * Author: Hugues Pisapia                                                     *
 *                                                                            *
 * Date: 15th April 2006                                                      *
 *                                                                            *
 ******************************************************************************/

/* $Id: view.php 76722 2009-10-13 20:06:16Z bdrust $ */

/// This page prints a particular instance of the live classroom links

require_once("../../config.php");
require_once("lib/php/lc/LCAction.php");
require_once("lib/php/common/WimbaCommons.php");
require_once("lib/php/common/WimbaLib.php");
require_once("lib.php");

$id = optional_param('id', 0, PARAM_INT);//instance id

if ((isset($_GET["action"]) && $_GET["action"]!="launchCalendar" && $id) || !isset($_GET["action"]) ) 
{
  if (! $cm = get_record("course_modules", "id", $id)) 
  {
    error("Course Module ID was incorrect");
  }
  if (! $course = get_record("course", "id", $cm->course)) 
  {
    error("Course is misconfigured");
  }
  if (! $liveclassroom = get_record("liveclassroom", "id", $cm->instance)) 
  {
    error("This Wimba Classroom instance doesn't exist");
  }
} 
else 
{
  if (! $liveclassroom = get_record("liveclassroom", "id", $id)) 
  {
    error("This Wimba Classroom instance doesn't exist");
  }
  if (! $course = get_record("course", "id", $liveclassroom->course)) 
  {
    error("Course is misconfigured");
  }
  if (! $cm = get_coursemodule_from_instance("liveclassroom", $liveclassroom->id, $course->id)) 
  {
    error("Course Module ID was incorrect");
  }
}

require_login($course->id);    

if ($liveclassroom->isfirst == 0)
{
    $liveclassroom->isfirst = 1;
    $liveclassroom->name = addslashes($liveclassroom->name);
    update_record("liveclassroom",$liveclassroom); 
    redirection("$CFG->wwwroot/course/view.php?id=$course->id");
}

$api = new LCAction(null, $CFG->liveclassroom_servername, $CFG->liveclassroom_adminusername, $CFG->liveclassroom_adminpassword, $CFG->dataroot);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
#if(getRoleForWimbaTools($course->id, $USER->id)=="Instructor")
if (liveclassroom_getRole($context) == "Instructor")
{
	$authToken = $api->getAuthokenNormal($course->id."_T",$USER->firstname,$USER->lastname);
}
else
{
	$authToken = $api->getAuthokenNormal($course->id."_S",$USER->firstname,$USER->lastname);

}

$classid = $liveclassroom->type;

//get the room
$room=$api->getRoom($classid);

$strliveclassrooms = get_string("modulenameplural", "liveclassroom");
$strliveclassroom  = get_string("modulename", "liveclassroom");

if( function_exists("build_navigation") )
{//moodle 1.9
	$cm->modname="liveclassroom";
	$cm->name=$liveclassroom->name;
    $navigation = build_navigation('', $cm);    
    
    print_header("$course->shortname : $liveclassroom->name", 
                $course->fullname,
                $navigation,
                "",
                "",
                true, 
                update_module_button($cm->id, $course->id, $strliveclassroom." ".get_string('activity','liveclassroom')), 
                navmenu($course)); 
    
}
else
{
    $navigation = array();
    if ($course->id != SITEID) {
            $navigation[$course->shortname] = "$CFG->wwwroot/course/view.php?id=$course->id";
    }
    $navigation[$strliveclassrooms] = "$CFG->wwwroot/mod/liveclassroom/index.php?id=$course->id";

    
    $urls = array();
    foreach($navigation as $text => $href) {
        if (empty($href)) {
            $urls[] = $text;
        } else {
            $urls[] = '<a href="'.$href.'">'.$text.'</a>';
        }
    }
    $breadcrumb = implode(' -> ', $urls);
    
    print_header("$course->shortname: $liveclassroom->name", "$course->fullname",
    $breadcrumb." $liveclassroom->name", 
    "", "", true, update_module_button($cm->id, $course->id, $strliveclassroom), 
    navmenu($course, $cm));
}

?>
<link rel="STYLESHEET" href="<?php p($CFG->wwwroot) ?>/mod/liveclassroom/css/StyleSheet.css"" type="text/css" />
<script type="text/javascript" src='<?PHP p($CFG->liveclassroom_servername)?>/js/launch.js'></script>

<script type="text/javascript">
function startLiveClassroom(){
  startHorizon('<?php p($classid) ?>',null,null,null,null,'hzA=<?php p($authToken)?>'); 
}
</script>

<div style="border:solid 1px #808080;width:700px;height:400px;background-color:white;margin-left:20%;margin-top:5%"  class="general_font">
    <div class="headerBar">
        <div class="headerBarLeft" >
            <span>Wimba</span>
        </div>
    </div>
     <div style="height:340px;width:700px;">
        <span style="display:block;padding-top:150px;padding-left:200px">
                	<?php if($room->isPreview() == false || liveclassroom_getRole($context) == "Instructor"){?>
	                	<script>startLiveClassroom()</script>
	                   <?php echo get_string ('lcpopupshouldappear.1', 'liveclassroom');?>
					    <a href="javascript:startLiveClassroom ();">
						<?php echo get_string ('lcpopupshouldappear.2', 'liveclassroom');?>
						</a>
					   <?php echo get_string ('lcpopupshouldappear.3', 'liveclassroom');
				  
                
                	}else{
						 echo get_string ('activity_tools_not_available', 'liveclassroom');   
          			 }?>
				   </span>
    </div>
     <div style="border-top:1px solid; background-color:#F0F0F0;width:700px;height:25px">
        <a href="<?php echo $CFG->wwwroot;?>/course/view.php?id=<?php p($course->id)?>"   style="padding-left: 550px; margin-top: 2px;" class="regular_btn">
            <span style="width:110px"><?php echo get_string ('close', 'liveclassroom'); ?></span>
        </a>                                               
    </div>
</div>
<?php
	/// Finish the page
	print_footer($course);
?>
