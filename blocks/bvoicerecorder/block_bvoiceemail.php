<?PHP 

require_once($CFG->libdir.'/datalib.php');

class block_bvoiceemail extends block_base {

  function init() {
    $this->title = "Voice E-Mail";
    $this->version = 2004111200;
  }

  function preferred_width() {
    return 220;
  }

  function applicable_formats() {
      return array('site-index' => false,                 'course-view' => true               );

  }

  function get_content() {

    global $CFG, $USER, $COURSE;
    if ($this->content !== NULL) {
      return $this->content;
    }

    $this->content = new stdClass;
    $this->content->text = '';
    $this->content->footer = '';
    $this->content->text .= '<table>';     
    $this->content->text .= '<tr><td align="center">';	
    $this->content->text .= '<iframe src='.$CFG->wwwroot.'/mod/voiceemail/block.php?course_id='. $COURSE->id.'&block_id='.$this->instance->id.'  name="frameWidget" style="overflow:hidden" FRAMEBORDER=0 width="220px" height="150px"></iframe>';			
    $this->content->text .= '</td></tr>';			
    $this->content->text .= '</table>';
    
    return $this->content;
  }
  
  function instance_allow_multiple() {
    return false;
  }


  function instance_allow_config() {
    return true;
  }
  function has_config() {
    return true;
  }
 
}


?>