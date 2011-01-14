<?php
class block_caseLinks extends block_base {
  function init() {
    $this->title   = 'Case Links';
    $this->version = 20100809;
  }
	function get_content() {
   	 if ($this->content !== NULL) {
	      return $this->content;
	    }
		global $COURSE;
	    $this->content         =  new stdClass;
	    $this->content->text   = '<a href="/course/view.php?id='.$COURSE->id.'">Course Homepage</a><br />
			<a href="http://www.case.edu/" target="_blank">Case Homepage</a><br />
			<a href="http://webmail.case.edu/" target="_blank">Webmail</a><br />
			<a href="http://courseware.case.edu/" target="_blank">Courseware</a><br />
			<a href="http://www.case.edu/erp/sis/" target="_blank">SIS</a><br />
			<a href="http://studentaffairs.case.edu/My/Housing/" target="_blank">My Housing</a><br />
			<a href="http://finaid.case.edu/CWRUfas.aspx?Menu&Office=CWRUfas&Level=0" target="_blank">BriefCase</a>';
	    return $this->content;
	}
	function instance_allow_multiple() {
	  return true;
	}
	function applicable_formats() {
	  return array('all' => true);
	}
}   // Here's the closing curly bracket for the class definition
	    // and here's the closing PHP tag from the section above.

		
?>