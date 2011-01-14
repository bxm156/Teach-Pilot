<?PHP
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

/* $Id: index.php 76005 2009-08-25 14:43:11Z trollinger $ */

/// This page lists all the instances of liveclassroom in a particular course


require_once("../../config.php");
require_once("lib.php");

require_once("lib/php/common/WimbaCommons.php");
require_once("lib/php/common/WimbaLib.php");

$id = optional_param('id', 0, PARAM_INT);
$roomname = optional_param('idroomname', 0, PARAM_TEXT);

if (! $course = get_record("course", "id", $id)) {
  error("Course ID is incorrect");
}

require_login($course->id);

$strliveclassrooms = get_string("modulenameplural", "liveclassroom");
    

if( function_exists("build_navigation") )
{//moodle 1.9
    $navlinks = array();
    $navlinks[] = array('name' => $strliveclassrooms, 'link' => '', 'type' => 'activity');
    
    print_header("$course->shortname: $strliveclassrooms", $course->fullname,
                    build_navigation($navlinks),
                    "", "", true, '', navmenu($course)); 
}
else
{
    $navigation = array();
    if ($course->id != SITEID) {
            $navigation[$course->shortname] = "$CFG->wwwroot/course/view.php?id=$course->id";
    }
    $navigation[$strliveclassrooms] = "";
    
    
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
    $breadcrumb, 
    "", "", true, "", 
    navmenu($course));
}
                    
                    
/// Get all the appropriate data
$url = liveclassroom_get_url_params($course->id);
    
?>

<div align="center">
    <iframe id="iframeW" width="702px" height="502px" name="frameWidget" frameborder="0" scrolling="no" align="middle" style="margin-top:50px">
      <p>Sorry your navigator can't display this iframe
    </iframe>
</div>

<script>
	document.getElementById("iframeW").src='<?php echo "welcome.php?id=".$id."&".$url."&time=".time() ?>'
</script>

<?php
/// Finish the page
    print_footer($course);
?>
