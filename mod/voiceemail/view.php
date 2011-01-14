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

/* $Id: view.php 65289 2008-07-03 18:45:06Z thomasr $ */

error_reporting(E_ERROR);
require_once('../../config.php');
require_once('lib.php');     
//Wimba Library
require_once ("lib/php/common/WimbaLib.php");
require_once ("lib/php/common/DatabaseManagement.php");      
require_once('lib/php/vt/WimbaVoicetools.php');   
require_once('lib/php/vt/WimbaVoicetoolsAPI.php');
require_once('lib/php/common/WimbaCommons.php');   
require_once('lib/php/vt/VtAction.php');   

$id = optional_param('id', 0, PARAM_INT);   // Course Module ID, or
$action = optional_param('action', PARAM_ACTION);

if ((isset($action) && $action!="launchCalendar" ) && $id || !isset($action) ) 
{
        if (! $cm = get_record("course_modules", "id", $id)) 
        {
            error("Course Module ID was incorrect");
        }
        
        if (! $course = get_record("course", "id", $cm->course)) 
        {
            error("Course is misconfigured");
        }
        
        if (! $voicetool = get_record("voiceemail", "id", $cm->instance)) 
        
        {
            error("Course module is incorrect");
        }
} 
else 
{
        if (! $voicetool = get_record("voiceemail", "id", $id)) 
        {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $voicetool->course)) 
        {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("voiceemail", $voicetool->id, $course->id)) 
        {
            error("Course Module ID was incorrect");
        }
}

require_login($course->id);

if ($voicetool->isfirst == 0)
{
    $voicetool->isfirst = 1;
    $voicetool->name = addslashes($voicetool->name);
    update_record("voiceemail",$voicetool); 
    redirection("$CFG->wwwroot/course/view.php?id=$course->id");
}

$servername = $CFG->voicetools_servername;
$strvoicetools = get_string("modulenameplural", "voiceemail");
$strvoicetool  = get_string("modulename", "voiceemail");

$sentence1 = get_string ('vtpopupshouldappear.1', 'voiceemail');
$sentence2 = "<a href='javascript:startVoiceTools()';>".get_string ('vtpopupshouldappear.2', 'voiceemail')."</a>";
$sentence3 = get_string ('vtpopupshouldappear.3', 'voiceemail');
$strLaunchComment = $sentence1.$sentence2.$sentence3;                           



//get the informations related to the Vt resource
$vtAction = new vtAction($USER->email); 
$dbResource = get_record("voiceemail_resources","id", $voicetool->rid);
$resource = $vtAction->getResource( $dbResource->rid ) ;  
//check the availability of the resource   

$roleSwitch=isSwitch();//the user have switched his role?
//determinate the role for the wimba tools 
$context = get_context_instance( CONTEXT_MODULE, $cm->id );

$role = voiceemail_getRole($context);  
       
if( $resource->error==true )
{
    wimba_add_log( WIMBA_ERROR,voiceemail_LOGS, "view.php : problem to get the resource(rid : ".$voicetool->rid.") linked to this activity" ); 
    error( get_string( "problem_vt", "voiceemail" ), "$CFG->wwwroot/course/view.php?id=$course->id" );
}

$currentUser = $vtAction->createUser( $USER->firstname."_".$USER->lastname,$USER->email );
$currentUserRights = $vtAction->createUserRights( $resource->getType(),$role );

$resource->setEmailFrom( $USER->email );

//get the vt session
$vtSession=$vtAction->getVtSession( $resource, $currentUser, $currentUserRights );      
if(function_exists("build_navigation"))
{  
	$cm->modname="voiceemail";
	$cm->name=$voicetool->name;
    $navigation = build_navigation('', $cm);    

    print_header("$course->shortname: $voicetool->name", $course->fullname,
                    $navigation,
                    "",
                    "", 
                    true, 
                    update_module_button(
                                        $cm->id,
                                        $course->id,
                                        $strvoicetool." ".get_string('activity','voiceemail')
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
    $navigation[$strvoicetools] = "$CFG->wwwroot/mod/voiceemail/index.php?id=$course->id";
    
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
<script  type="text/javascript">  
var popup;  
function doOpenPopup ( url, type )
{                 
  popup=window.open ( url, type+"_popup", "scrollbars=no,toolbar=no,menubar=no,resizable=yes");    
}

function startVoiceTools(){
  <?php if(isset($roleSwitch) && $roleSwitch==true)  { ?>
        result=window.confirm("<?php echo get_string ('launchstudent', 'voiceemail');?>");
        if(result==true) {     
          doOpenPopup("<?php echo $servername ?>/<?php echo $resource->getType();?>?action=display_popup&nid=<?php echo $vtSession->getNid() ?>","<?php echo $resource->getType()?>");
        } 
        else
        {
          location.href= document.referrer;                     
        }
 <?php }else{ ?>
        doOpenPopup("<?php echo $servername ?>/<?php echo $resource->getType();?>?action=display_popup&nid=<?php echo $vtSession->getNid() ?>","<?php echo $resource->getType()?>");      
 <?php } ?>
}
  
</script>
<link rel="STYLESHEET" href="<?php p($CFG->wwwroot) ?>/mod/voiceemail/css/StyleSheet.css"" type="text/css" />

<div class="general_font" style="border:solid 1px #808080;background-color:white;width:700px;height:400px;margin-left:20%;margin-top:5%">
     <div class="headerBar">
            <div class="headerBarLeft" >
                <span>Wimba</span>
            </div>
    </div>
    <div style="height:340px;width:700px;">
        <span style="display:block;padding-top:150px;padding-left:200px">
            <?php 
            echo $strLaunchComment    
            ?>
            <script>
            startVoiceTools();
            </script>   
        </span>
    </div>
     <div style="border-top:1px solid; background-color:#F0F0F0;width:700px;height:25px">
        <a href="<?php echo $CFG->wwwroot;?>/course/view.php?id=<?php p($course->id)?>"   style="padding-left: 550px; margin-top: 2px;" class="regular_btn">
            <span style="width:110px">Close</span>
        </a>                                               
    </div>
</div>  
<?php
    /// Finish the page
    print_footer($course);
?>
