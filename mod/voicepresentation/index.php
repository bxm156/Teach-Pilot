<?PHP
/******************************************************************************
 *                                                                            *
 * Copyright (c) 1999-2006 Horizon Wimba, All Rights Reserved.                *
 *                                                                            *
 * COPYRIGHT:                                                                 *
 *      This software is the property of Horizon Wimba.                       *
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
 *      along with the Horizon Wimba Moodle Integration;                      *
 *      if not, write to the Free Software Foundation, Inc.,                  *
 *      51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA                *
 *                                                                            *
 * Author: Hugues Pisapia                                                     *
 *                                                                            *
 * Date: 15th April 2006                                                      *
 *                                                                            *
 ******************************************************************************/

/* $Id: index.php 76014 2009-08-25 20:30:43Z trollinger $ */

/// This page lists all the instances of voicetools in a particular course
error_reporting(E_ERROR);
require_once("../../config.php");
require_once("lib.php");
require_once("lib/php/common/WimbaLib.php");

$course_id = optional_param('id', 0, PARAM_INT); // course
$grade_id = optional_param('gradeId', 0, PARAM_INT); // course
$action = optional_param('action', "", PARAM_ALPHANUM); // course
$resource_id = optional_param('resource_id', "", PARAM_CLEAN);

if (! $course = get_record("course", "id", $course_id)) 
{
    error("Course ID is incorrect");
}

require_login($course->id);

$strvoicetools = get_string("modulenameplural", 'voicepresentation');

if( function_exists("build_navigation"))
{
//Management of the breadcrump
 
    $navlinks = array();
    $navlinks[] = array('name' => $strvoicetools, 'link' => '', 'type' => 'activity');
    
    print_header("$course->shortname: $strvoicetools", $course->fullname,
                        build_navigation($navlinks),
                        "", "", true, '', navmenu($course)); 
}
else
{
    
    $navigation = array();
    if ($course->id != SITEID) {
            $navigation[$course->shortname] = "$CFG->wwwroot/course/view.php?id=$course->id";
    }
    $navigation[$strvoicetools] = "";
   
    
    $urls = array();
    foreach($navigation as $text => $href) {
        if (empty($href)) {
            $urls[] = $text;
        } else {
            $urls[] = '<a href="'.$href.'">'.$text.'</a>';
        }
    }
    $breadcrumb = implode(' -> ', $urls);
    
     print_header("$course->shortname", "$course->fullname",
        "$breadcrumb", 
        "", "", true, "", navmenu($course));                       
}


$url_params = voicepresentation_get_url_params($course->id);

if( isset($action) && $action == "displayGrade"){
  $url = "grades.php?gradeId=".$grade_id."&resource_id=".$resource_id."&";
}else{
  $url = "welcome.php?";
}
//create the url which all the parameters needed

?>

<div align="center">
    <iframe id="iframeW"  width="702px" height="402px" name="frameWidget" frameborder="0" scrolling="no" align="middle" style="margin-top:50px">
        <p>Sorry your navigator can't display this iframe
    </iframe>
</div>

<script>
	document.getElementById("iframeW").src='<?php echo $url."id=".$course->id."&".$url_params."&time=".time() ?>'
</script>
<?php
/// Finish the page	
    print_footer($course);
?>
