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
 * Date: April 2006                                                           *
 *                                                                            *
 ******************************************************************************/

/* $Id: view.php 64437 2008-06-16 15:03:21Z thomasr $ */
error_reporting(E_ERROR);
require_once('../../config.php');
require_once('lib.php');     
//Wimba Library
require_once ("lib/php/common/WimbaLib.php");
  
$id = optional_param('id', 0, PARAM_INT);   // Course Module ID, or
$action = optional_param('launchCal',"", PARAM_TEXT);

if ( empty($action) ) 
{
        if (! $cm = get_record("course_modules", "id", $id)) 
        {
            error("Course Module ID was incorrect");
        }
        
        if (! $course = get_record("course", "id", $cm->course)) 
        {
            error("Course is misconfigured");
        }
        
        if (! $voicetool = get_record("voiceauthoring", "id", $cm->instance)) 
        
        {
            error("Course module is incorrect");
        }
} 
else 
{
        if (! $voicetool = get_record("voiceauthoring", "id", $id)) 
        {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $voicetool->course)) 
        {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("voiceauthoring", $voicetool->id, $course->id)) 
        {
            error("Course Module ID was incorrect");
        }
}


require_login($voicetool->course);
//redirection to the course page
//redirection("$CFG->wwwroot/course/view.php?id=$course->id#$voicetool->section");
$url = "displayPlayer.php?rid=".$voicetool->rid."&mid=".$voicetool->mid."&title=".urlencode($voicetool->activityname);
if ($voicetool->isfirst == 0)
{
    $voicetool->isfirst = 1;
    $voicetool->name = addslashes($voicetool->name);
    update_record("voiceauthoring",$voicetool); 
    redirection("$CFG->wwwroot/course/view.php?id=$course->id#$voicetool->section");
}
else if( !empty($action) &&  format_string ("<iframe>test</iframe>") != "test" )//iframe allowed
{
     redirection("$CFG->wwwroot/course/view.php?id=$course->id&launchCal=".$action);
}
else
{
?>

<script>
function openVA(){
    pop = window.open ("<?php echo $url?>", "va_popup", "width=310,height=155,location=0;scrollbars=0,toolbar=0,menubar=0,resizable=yes")   

    if(pop == null) return false;
    if(pop.closed != false) return false;

}

  
if(openVA() != false)
    top.location="<?php echo $CFG->wwwroot?>/course/view.php?id=<?php echo $course->id?>#<?php echo $voicetool->section?>";
    
</script>
<?php }?>

<?php
$servername = $CFG->voicetools_servername;
$strvoicetools = get_string("modulenameplural", "voiceauthoring");
$strvoicetool  = get_string("modulename", "voiceauthoring");

$sentence1 = get_string ('vtpopupshouldappear.1', 'voiceauthoring');
$sentence2 = "<a href='javascript:openVA()';>".get_string ('vtpopupshouldappear.2', 'voiceauthoring')."</a>";
$sentence3 = get_string ('vtpopupshouldappear.3', 'voiceauthoring');
$strLaunchComment = $sentence1.$sentence2.$sentence3;  
if(function_exists("build_navigation"))
{  
    $cm->modname="voiceauthoring";
    $cm->name=$voicetool->activityname;
    $navigation = build_navigation('', $cm);    

    print_header("$course->shortname: $voicetool->activityname", $course->fullname,
                    $navigation,
                    "",
                    "", 
                    true, 
                    update_module_button(
                                        $cm->id,
                                        $course->id,
                                        $strvoicetool." ".get_string('activity','voiceauthoring')
                    ),
                    navmenu($course)
             ); 
}
else
{
    $navigation = array();
    if ($course->id != SITEID) {
            $navigation[$course->shortname] = "$CFG->wwwroot/course/view.php?id=$course->id";
    }
    $navigation[$strvoicetools] = "$CFG->wwwroot/mod/voiceauthoring/index.php?id=$course->id";
 
    
    $urls = array();
    foreach($navigation as $text => $href) {
        if (empty($href)) {
            $urls[] = $text;
        } else {
            $urls[] = '<a href="'.$href.'">'.$text.'</a>';
        }
    }
    $breadcrumb = implode(' -> ', $urls);
    
    print_header("$course->shortname: $voicetool->name", "$course->fullname",
    $breadcrumb." $voicetool->name", 
    "", "", true, update_module_button($cm->id, $course->id, $strvoicetool), 
    navmenu($course, $cm));                       
}             



?>

<link rel="STYLESHEET" href="<?php p($CFG->wwwroot) ?>/mod/voiceauthoring/css/StyleSheet.css"" type="text/css" />
<div style="border:solid 1px #808080;width:700px;height:400px;background-color:white;margin-left:20%;margin-top:5%"  class="general_font">
    <div class="headerBar">
        <div class="headerBarLeft" >
            <span>Wimba</span>
        </div>
    </div>
     <div style="height:340px;width:700px;">
        <span style="display:block;padding-top:150px;padding-left:200px">
            <?php echo $strLaunchComment;?>
        </span>
    </div>
     <div style="border-top:1px solid; background-color:#F0F0F0;width:700px;height:25px">
        <a href="<?php echo $CFG->wwwroot;?>/course/view.php?id=<?php p($course->id)?>"   style="padding-left: 550px; margin-top: 2px;" class="regular_btn">
            <span style="width:110px"><?php echo get_string ('close', 'voiceauthoring'); ?></span>
        </a>                                               
    </div>
</div>
<?php
    /// Finish the page
    print_footer($course);
?>