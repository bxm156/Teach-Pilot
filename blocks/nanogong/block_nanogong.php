<?php 
class block_nanogong extends block_base
{
	function init() {
    	$this->title   = "NanoGong";
    	$this->version = 2010123001;
  }
function get_content() {
    if ($this->content !== NULL) {
      return $this->content;
    }
	global $CFG;
	$url = $CFG->wwwroot ."/blocks/nanogong/nanogong.jar";
    $this->content         =  new stdClass;
    $this->content->text   = '<div align="center"><applet id="applet" archive="'.$url.'" code="gong.NanoGong" width="180" height="40"></applet></div>';
    $this->content->footer = '';

    return $this->content;
  }
   // Here's the closing curly bracket for the class definition
    // and here's the closing PHP tag from the section above.

	function applicable_formats() {
	  return array(
	           'course-view' => true
			);
	}
	function preferred_width() {
	  // The preferred value is in pixels
	  return 200;
	}
}
?>
