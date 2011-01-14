<?php  // $Id: filter.php,v 4 2010/04/22 00:00:00 gibson Exp $

function nanogong_filter($courseid, $text) {
		global $CFG;
    $search = '/(<nanogong.*?>\s*)+/is';
    if (preg_match($search, $text)) { // Solved bug reported by Nicolas Dunand
        $nanogong_script = <<<NANOGONG_SCRIPT
<script language="Javascript" type="text/javascript">
var current_nanogong_id = -1;
var loaded_nanogong_id = -1;

function set_nanogong_visible(visible) {
    var div = document.getElementById("nanogong_applet_container_for_embed");
    if (div) div.style.visibility = (visible)? "visible" : "hidden";
}

function set_nanogong_position(obj) {
	var div = document.getElementById("nanogong_applet_container_for_embed");
	var curTop = obj.offsetParent.offsetTop + obj.offsetTop, curLeft = obj.offsetParent.offsetLeft + obj.offsetWidth;
	div.style.top = curTop + "px";
	div.style.left = curLeft + "px";
}

function show_nanogong_applet(obj, id, url) {
  if (current_nanogong_id != id)
  {
  	set_nanogong_position(obj);
  	set_nanogong_visible(true);
  	current_nanogong_id = id;

  	if(loaded_nanogong_id != id) {
  		document.getElementById("embedded_nanogong_player").sendGongRequest("LoadFromURL", url);
  		loaded_nanogong_id = id;
  	}
  }
}

function hide_nanogong_applet(id) {
  if (current_nanogong_id == id){
    set_nanogong_visible(false);
    current_nanogong_id = -1;
	}
}

function onclick_nanogong_applet(obj, id, url) {
    if (current_nanogong_id == id)
        hide_nanogong_applet(id);
    else
        show_nanogong_applet(obj, id, url);
}
</script>
	
<div id="nanogong_applet_container_for_embed" style="position:absolute; top: -40px; left: -130px;">
	<script>
		var loaded;
		if(!loaded)
		{
			loaded = true;
			
			document.getElementById("nanogong_applet_container_for_embed").innerHTML += "<applet id='embedded_nanogong_player' archive='{$CFG->wwwroot}/mod/nanogong/nanogong.jar' code='gong.NanoGong' width='130px' height='40px'><param name='ShowTime' value='true' /><param name='ShowAudioLevel' value='false' /><param name='ShowRecordButton' value='false' /></applet>";
		}
	</script>
</div>

NANOGONG_SCRIPT;
        return $nanogong_script.preg_replace_callback($search, 'nanogong_filter_callback', $text);
    }
    else
        return $text;
}

function nanogong_filter_callback($nanogong_tag) {
    global $CFG;
    static $nanogong_index = 0;

    $CFG->currenttextiscacheable = false;   // Cannot cache a nanogong filter

    $search = '/caption="([^"]*)"/is';
    preg_match_all($search, $nanogong_tag[0], $matches, PREG_SET_ORDER);
    $caption = (isset($matches[0][1]))? $matches[0][1] : null;
    $matches = null;

    $search = '/url="([^"]*)"/is';
    preg_match_all($search, $nanogong_tag[0], $matches, PREG_SET_ORDER);
    $url = (isset($matches[0][1]))? $matches[0][1] : null;
    $matches = null;

//    $search = '/showRecordButton="(true|false)"/is';
//    preg_match_all($search, $nanogong_tag[0], $matches, PREG_SET_ORDER);
//    $showRecordButton = (isset($matches[0][1]))? $matches[0][1] : "false";
//    $matches = null;
//
//    $search = '/showAudioLevel="(true|false)"/is';
//    preg_match_all($search, $nanogong_tag[0], $matches, PREG_SET_ORDER);
//    $showAudioLevel = (isset($matches[0][1]))? $matches[0][1] : "false";
//    $matches = null;
//
//    $search = '/showSpeedButton="(true|false)"/is';
//    preg_match_all($search, $nanogong_tag[0], $matches, PREG_SET_ORDER);
//    $showSpeedButton = (isset($matches[0][1]))? $matches[0][1] : "true";
//    $matches = null;
//
//    $search = '/showSaveButton="(true|false)"/is';
//    preg_match_all($search, $nanogong_tag[0], $matches, PREG_SET_ORDER);
//    $showSaveButton = (isset($matches[0][1]))? $matches[0][1] : "true";
//    $matches = null;
//
//    $param = "";
    if ($url != "") {
        if ($CFG->slasharguments)
            $url = "{$CFG->wwwroot}/file.php$url";
        else
            $url = "{$CFG->wwwroot}/file.php?file=$url";
        //$param .= "<param name=\"SoundFileURL\" value=\"$url\" />";
    }
//    if ($showRecordButton == "false")
//        $param .= "<param name=\"ShowRecordButton\" value=\"false\" />";
//    if ($showAudioLevel == "false")
//        $param .= "<param name=\"ShowAudioLevel\" value=\"false\" />";
//    if ($showSpeedButton == "false")
//        $param .= "<param name=\"ShowSpeedButton\" value=\"false\" />";
//    if ($showSaveButton == "false")
//        $param .= "<param name=\"ShowSaveButton\" value=\"false\" />";
//
//    $width = 180;
//    if ($showRecordButton == "false") $width -= 30;
//    if ($showAudioLevel == "false") $width -= 20;
//    if ($showSpeedButton == "false") $width -= 35;
//    if ($showSaveButton == "false") $width -= 30;

    if ($caption == null) $caption = "";

    $nanogong_span = <<<NANOGONG_SPAN
<span style="position:relative;font-size:7pt;font-weight:bold;text-decoration:none;color:black">
	<img alt="Show/Hide NanoGong" src="$CFG->wwwroot/filter/nanogong/pix/sound.gif" onclick="javascript: onclick_nanogong_applet(this, $nanogong_index, '$url');" />
	$caption
</span>
NANOGONG_SPAN;
    $nanogong_index++;

    return $nanogong_span;
}

?>
