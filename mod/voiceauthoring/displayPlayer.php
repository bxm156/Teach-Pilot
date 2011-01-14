<?php
require_once("../../config.php");
require_once("lib.php");

$iframeId=optional_param('id','',PARAM_CLEAN);
$title = urldecode( optional_param('title','',PARAM_CLEAN));
$mid=optional_param('mid','',PARAM_CLEAN);
$rid=optional_param('rid','',PARAM_CLEAN);

if(strlen($title)>50){
    $title=substr($title,0,50)."...";
}

?>
<script>
if(parent.document.getElementById( "<?php echo $iframeId;?>" ) != null)
{
    parent.document.getElementById( "<?php echo $iframeId;?>" ).style.position ="absolute";
    parent.document.getElementById( "<?php echo $iframeId;?>" ).style.width = "370px";
    parent.document.getElementById( "<?php echo $iframeId;?>" ).style.height = "200px";
    parent.document.getElementById( "<?php echo $iframeId;?>" ).style.overflow ="hidden";
    parent.document.getElementById( "<?php echo $iframeId;?>" ).style.width = "370px";
    parent.document.getElementById( "<?php echo $iframeId;?>" ).style.paddingTop ="10px";
}
</script>
<link rel="STYLESHEET" href="css/StyleSheet.css" type="text/css" />
<style>

body 
{
    background-color: transparent;
}   

</style>
<div class="wimba_box" style="height:100px;">
    <div class="wimba_boxTop">
        <div class="wimbaBoxTopDiv" style="_height:80px">
            <span class="wimba_boxTopTitle"><?php echo $title; ?>         
            </span>
            
            <span title="close" class="wimba_close_box" onclick="javascript:parent.document.getElementById('<?php echo $iframeId;?>').data=null;javascript:parent.document.getElementById('<?php echo $iframeId;?>').style.display='none';return false;">Close</span>
            
            <p >
                <SCRIPT type="text/javascript">
                  this.focus();
                </SCRIPT>
                
                <SCRIPT type="text/javascript" SRC="<?php echo $CFG->voicetools_servername; ?>/ve/play.js"></SCRIPT>
                <SCRIPT type="text/javascript">
                    var w_p = new Object();
                    w_p.mid="<?php echo $mid;?>";
                    w_p.rid="<?php echo $rid;?>";
                    w_p.language = "en";
                    w_p.autostart = "true";
                    
                    if (window.w_ve_play_tag) w_ve_play_tag(w_p);
                    else document.write("Applet should be there, but the Wimba Voice server is down");
                  
                </SCRIPT>
           
            </p>
        </div>
    </div>
    <div class="wimba_boxBottom">
        <div>
        </div> 
    </div>
</div>

            