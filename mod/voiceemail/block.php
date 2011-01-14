<?php
require_once("../../config.php");
require_once("lib.php");

$course_id=optional_param('course_id', '',PARAM_ALPHANUM);
$block_id=optional_param('block_id', '',PARAM_ALPHANUM);
$config_block = get_record("voiceemail_block",'block_id',$block_id);

?>
<html>
<head>
<title>Voice E-Mail Block</title>
<link rel="STYLESHEET" href="<?php p($CFG->wwwroot) ?>/mod/voiceemail/css/StyleSheet.css" type="text/css" />
<style>
*{
    padding:0px;
    margin:0px;
}
</style>
</head>
<body>

<script>
function openWimbaPopup(url,type)
{   
    url = url+"?type="+type+"&course_id=<?php echo $course_id; ?>&block_id=<?php echo $block_id; ?>";
    popup = window.open (url, "vmail_popup", "width=450px,height=500px,scrollbars=no,toolbar=no,menubar=no,resizable=yes"); 
}
</script>
<div id="vmail_block">
    <?php if(!isset($config_block) || (
                isset($config_block->all_users_enrolled ) && $config_block->all_users_enrolled == "0" && 
                isset($config_block->instructor ) && $config_block->instructor == "0" &&
                isset($config_block->student ) && $config_block->student == "0" &&
                isset($config_block->recipient) && $config_block->recipient == "0" )
                ){ ?>
    <p>
        <span class="TextMinor"><?php echo get_string('nothing_selected','voiceemail') ?></span>
    </p>
    <?php }else{ ?>
    <p>
        <span class="TextMinor"><?php echo get_string('block_send_sentence','voiceemail') ?></span>
    </p>
    <?php } ?>
    <?php if(empty($config_block) || $config_block->all_users_enrolled == "1"){ ?>
    <p style="padding-left:15px;padding-top:10px">
        <a href="#" onclick="openWimbaPopup('manageActionBlock.php','all')" class="TextRegular">
            <?php echo get_string('block_send_vmail_all','voiceemail') ?>
        </a>
    </p>
    <?php } ?>
    <?php if(empty($config_block) || $config_block->instructor == "1"){ ?>
    <p style="padding-left:15px;padding-top:10px">
        <a href="#" onclick="openWimbaPopup('manageActionBlock.php','instructors')" class="TextRegular">
            <?php echo get_string('block_send_vmail_instructors','voiceemail') ?>
        </a>
    </p>
    <?php } ?>
    <?php if(empty($config_block) || $config_block->student == "1"){ ?>
    <p style="padding-left:15px;padding-top:10px">
        <a href="#" onclick="openWimbaPopup('manageActionBlock.php','students')" class="TextRegular">
            <?php echo get_string('block_send_vmail_students','voiceemail') ?>
        </a>
    </p>
    <?php } ?>
    <?php if(empty($config_block) || $config_block->recipient == "1"){ ?>
    <p style="padding-left:15px;padding-top:10px">
        <a href="#" onclick="openWimbaPopup('listAvailableRecipients.php','selected')" class="TextRegular">
            <?php echo get_string('block_send_vmail_selected','voiceemail') ?>
        </a>
    </p>
    <?php } ?>
</div>
</body>
</html>