<?php
require_once("../../config.php");
require_once("lib.php");

$block_id=optional_param('block_id', '',PARAM_ALPHANUM);
$course_id=optional_param('course_id', '',PARAM_ALPHANUM);
$config = get_record("voiceemail_block","block_id",$block_id);
$checked_all="checked";
$checked_student="checked";
$checked_instructor="checked";
$checked_recipient="checked";
if( isset($config->all_users_enrolled) && $config->all_users_enrolled == "0")
{
    $checked_all="";
    
}
if( isset($config->student) && $config->student == "0")
{
    $checked_student="";
    
}
if( isset($config->instructor) && $config->instructor == "0")
{
    $checked_instructor="";
    
}
if( isset($config->recipient) && $config->recipient == "0")
{
    $checked_recipient="";
    
}
?>

<link rel="STYLESHEET" href="css/StyleSheet.css" type="text/css" />
<form action="manageActionBlock.php">
    <input type="hidden" value="<?php echo $block_id; ?>"" name="block_id" >
    <input type="hidden" value="<?php echo $course_id; ?>"" name="course_id" >
    <input type="hidden" value="updateConfig" name="action" >
       
    <div style="width:700px;"  class="general_font"> 
        <div style="border:1px solid #78879A ">
            <p style="padding-left:150px">
                <input id="block_send_vmail_all_users_enrolled" <?php echo $checked_all; ?> type="checkbox"  name="block_send_vmail_all_users_enrolled" value="true"/>
               <label class="TextRegular_Right" for="block_send_vmail_all_users_enrolled"><?php echo get_string('block_send_vmail_all','voiceemail') ?></label>        
            </p>
            <p style="padding-left:150px">
              <input id="block_send_vmail_instructors" <?php echo $checked_instructor; ?> type="checkbox"  name="block_send_vmail_instructors" value="true"/>
               <label class="TextRegular_Right" for="block_send_vmail_instructors"><?php echo get_string('block_send_vmail_instructors','voiceemail') ?></label>
                  
            </p>
            <p style="padding-left:150px">
               <input id="block_send_vmail_students" <?php echo $checked_student; ?> type="checkbox"  name="block_send_vmail_students" value="true"/>
               <label class="TextRegular_Right" for="block_send_vmail_students"><?php echo get_string('block_send_vmail_students','voiceemail') ?></label>
         
            </p>
            <p style="padding-left:150px">
               <input id="block_send_vmail_selected" <?php echo $checked_recipient; ?> type="checkbox"  name="block_send_vmail_selected" value="true"/>
               <label class="TextRegular_Right" for="block_send_vmail_selected"><?php echo get_string('block_send_vmail_selected','voiceemail') ?></label> 
            </p>
        </div>
        <p style="padding-left:290px;padding-top:10px">
         <input type="submit" class="regular_btn-submit" value="<?php print_string('savechanges') ?>" /></td>
        </p>
     
    </div>
</form>
