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
 * Author: Thomas Rollinger                                                   *
 *                                                                            *
 * Date: March 2007                                                           *
 *                                                                            *
 ******************************************************************************/

/* $Id: mod.html 65289 2008-07-03 18:45:06Z thomasr $ */
error_reporting(E_ERROR);
require_once("../../config.php");
require_once("lib.php");
require_once("lib/php/common/WimbaLib.php");

$course_id = optional_param('id', 0, PARAM_INT); // course

if (! $course = get_record("course", "id", $course_id)) 
{
    error("Course ID is incorrect");
}

require_login($course->id);

$strvoicetools = get_string("modulenameplural", 'voiceemail');
$strvoicetool  = get_string("modulename", 'voiceemail');
 
if(function_exists("build_navigation"))
{  
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
    $breadcrumb, 
    "", "", true, "", navmenu($course));                       
}     
 
$activities = count_records("voiceemail", "course", $course_id);
$bvoiceemail = get_record("block", "name","bvoiceemail");       
$blocks = count_records("block_instance", "pageid", $course_id,"blockid",$bvoiceemail->id);    
        
             
?>
<br> 
<link rel="STYLESHEET" href="<?php p($CFG->wwwroot) ?>/mod/voiceemail/css/StyleSheet.css" type="text/css" />
<div class="content general_font" id=content style="width:700px;height:400px;background-color:white;margin:0 auto;border: solid 1px #D9DEE5;" align=center>
    <div class="headerBar">
        <div class="headerBarLeft" >
            <span>Wimba</span>
        </div>
    </div>
    <div id="activity" style="height:220px; width:700px; margin-top:120px;" align="center">
        <span class="TextRegular" style="display:block; height:60px; width:700px; text-align:center">
            <?php echo get_string("you_have",'voiceemail')." ".$activities ." ".get_string("vmailActivities","voiceemail"); ?>
        </span>
        <span class="TextRegular" style="display:block; height:60px; width:700px; text-align:center">
            <?php echo get_string("you_have",'voiceemail')." ".$blocks." ".get_string("vmailBlocks","voiceemail"); ?>
        </span>
        <span class="TextRegular" style="display:block;height:40px;width:700px;text-align:center">
            <?php echo get_string("ManageToolsNotAvailableStart","voiceemail"); ?>
        </span>
    </div>
    <div style="border-top:1px solid; background-color:#F0F0F0;width:700px;height:25px">
        <a href="<?php echo $CFG->wwwroot;?>/course/view.php?id=<?php p($course_id)?>"   style="padding-left: 550px; margin-top: 2px;" class="regular_btn">
            <span style="width:110px">Ok</span>
        </a>                                               
    </div>
</div>
</body>

<?php
/// Finish the page 
    print_footer($course);
?>
