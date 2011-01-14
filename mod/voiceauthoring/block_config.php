<?php
require_once("../../config.php");
require_once("lib.php");

$block_id=optional_param('block_id', '',PARAM_ALPHANUM);
$course_id=optional_param('course_id', '',PARAM_ALPHANUM);
$config = get_record("voiceauthoring_block","bid",$block_id);

?>
<link rel="STYLESHEET" href="css/StyleSheet.css" type="text/css" />
<form action="manageActionBlock.php">
    <input type="hidden" value="<?php echo $block_id; ?>"" name="block_id" >
    <input type="hidden" value="<?php echo $course_id; ?>"" name="course_id" >
    <input type="hidden" value="updateConfig" name="action" >
       
    <div style="width:700px" class="general_font"> 
        <div style="border:1px solid #78879A ">
            <p>
               <label class="LargeLabel_Width TextRegular_Right" for="block_voiceauthoring_title"><?php echo get_string('block_voiceauthoring_title','voiceauthoring') ?></label>        
                <input id="block_voiceauthoring_title" type="text"  name="block_voiceauthoring_title" maxlength="50" width="250px" value="<?php echo $config->title ?>" />
            </p>
            <p >
               <label class="LargeLabel_Width TextRegular_Right" for="block_voiceauthoring_comment"><?php echo get_string('block_voiceauthoring_comment','voiceauthoring') ?></label>        
                <textarea id="block_voiceauthoring_comment" rows="5" cols="30"  name="block_voiceauthoring_comment"><?php echo $config->comment ?></textarea>
            </p>
        </div>
       <p style="height:30px;padding-left:290px">  
        <input type="submit" class="regular_btn-submit" value="<?php print_string("savechanges") ?>" />
       </p>
       
    </div>
</form>
