<?PHP 


require_once($CFG->libdir.'/datalib.php');


class block_bvoicerecorder extends block_base {

  function init() {
   
    $this->title = "Voice Authoring";
    $this->version = 2008111200;
  }
  
  function specialization() {
        $block_info = get_record("voiceauthoring_block","bid",$this->instance->id);
        $this->title = isset($block_info->title) ? format_string($block_info->title) : format_string("Voice Authoring");
    }

  function preferred_width() {
    return 215 ;
  }

  function applicable_formats() {
  
    return array('site-index' => false,
                 'course-view' => true
               );

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
    $this->content->text .= '<iframe src='.$CFG->wwwroot.'/mod/voiceauthoring/voiceauthoring.php?course_id='.$COURSE->id.
                                '&block_id='.$this->instance->id.
                                'name="frameWidget" style="overflow:hidden;margin-top:-10px" 
                                FRAMEBORDER=0 width="215px" height="150px"></iframe>';  
            
    $this->content->text .= '</td></tr>';           
    $this->content->text .= '</table>';



    return $this->content;
  }
  function instance_allow_multiple() {
    return true;
  }


  function instance_allow_config() {
    return false;
  }
  function has_config() {
    return false;
  }
 
}


?>

