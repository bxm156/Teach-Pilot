<?php  // $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $

/**
 * This page prints a particular instance of casecourseware
 *
 * @author  Your Name <your@email.address>
 * @version $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $
 * @package mod/casecourseware
 */

/// (Replace casecourseware with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/blocklib.php');
require_once(dirname(__FILE__).'/localib.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/pagelib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // casecourseware instance ID
$edit = optional_param('edit', -1, PARAM_BOOL);

if ($id) {
    if (! $cm = get_coursemodule_from_id('casecourseware', $id)) {
        error('Course Module ID was incorrect');
    }

    if (! $course = get_record('course', 'id', $cm->course)) {
        error('Course is misconfigured');
    }

    if (! $casecourseware = get_record('casecourseware', 'id', $cm->instance)) {
        error('Course module is incorrect');
    }

} else if ($a) {
    if (! $casecourseware = get_record('casecourseware', 'id', $a)) {
        error('Course module is incorrect');
    }
    if (! $course = get_record('course', 'id', $casecourseware->course)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('casecourseware', $casecourseware->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $casecourseware->id);

add_to_log($course->id, "casecourseware", "view", "view.php?id=$cm->id", "$casecourseware->id");

//Blocks
//$PAGE       = page_create_instance($casecourseware->id);
$PAGE = page_create_object(PAGE_CASECOURSEWARE_VIEW,$casecourseware->id);
$pageblocks = blocks_setup($PAGE);

// Print the page header
   if ($edit != -1 and $PAGE->user_allowed_editing()) {
       $USER->editing = $edit;
   }


// AJAX-capable course format?
$useajax = false; 
$ajaxformatfile = $CFG->dirroot.'/course/format/'.$course->format.'/ajax.php';
$bodytags = '';

if (empty($CFG->disablecourseajax) and file_exists($ajaxformatfile)) {      // Needs to exist otherwise no AJAX by default

    // TODO: stop abusing CFG global here
    $CFG->ajaxcapable = false;           // May be overridden later by ajaxformatfile
    $CFG->ajaxtestedbrowsers = array();  // May be overridden later by ajaxformatfile

    require_once($ajaxformatfile);

    if (!empty($USER->editing) && $CFG->ajaxcapable && has_capability('moodle/course:manageactivities', $context)) {
                                                         // Course-based switches

        if (ajaxenabled($CFG->ajaxtestedbrowsers)) {     // Browser, user and site-based switches
            
            require_js(array('yui_yahoo',
                             'yui_dom',
                             'yui_event',
                             'yui_dragdrop',
                             'yui_connection',
                             'yui_selector',
                             'yui_element',
                             'ajaxcourse_blocks',
                             'ajaxcourse_sections'));
            
            if (debugging('', DEBUG_DEVELOPER)) {
                require_js(array('yui_logger'));

                $bodytags = 'onload = "javascript:
                show_logger = function() {
                    var logreader = new YAHOO.widget.LogReader();
                    logreader.newestOnTop = false;
                    logreader.setTitle(\'Moodle Debug: YUI Log Console\');
                };
                show_logger();
                "';
            }

            // Okay, global variable alert. VERY UGLY. We need to create
            // this object here before the <blockname>_print_block()
            // function is called, since that function needs to set some
            // stuff in the javascriptportal object.
            $COURSE->javascriptportal = new jsportal();
            $useajax = true;
        }
    }
}

$CFG->blocksdrag = $useajax;   // this will add a new class to the header so we can style differently

/// Print the page header
$strcasecoursewares = get_string('modulenameplural', 'casecourseware');
$strcasecourseware  = get_string('modulename', 'casecourseware');

$navlinks = array();
$navlinks[] = array('name' => $strcasecoursewares, 'link' => "index.php?id=$course->id", 'type' => 'activity');
$navlinks[] = array('name' => format_string($casecourseware->name), 'link' => '', 'type' => 'activityinstance');

$navigation = build_navigation($navlinks);

/*print_header_simple(format_string($casecourseware->name), '', $navigation, '', '', true,
              update_module_button($cm->id, $course->id, $strcasecourseware), navmenu($course, $cm),false,$bodytags);
*/
$PAGE->print_header(format_string($casecourseware->name), NULL, '', $bodytags);

/*
        $section->course = $course->id;   // Create a default section.
        $section->section = 0;
        $section->visible = 1;
        $section->id = 0;
 		$sections = array($section);
*/
 echo '<div class="weekscss-format">';
// Include the actual course format.
  // require($CFG->dirroot .'/course/format/'. $course->format .'/format.php');
   // Content wrapper end.
   //echo "</div>\n\n";
	if (blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $USER->editing) {
       echo '<div id="left-column">';
       blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
       echo '</div>';
   }
   
/// The right column, BEFORE the middle-column.
   if (blocks_have_content($pageblocks, BLOCK_POS_RIGHT) || $USER->editing) {
       echo '<div id="right-column">';
       blocks_print_group($PAGE, $pageblocks, BLOCK_POS_RIGHT);
       echo '</div>';
   }
 echo '<div id="middle-column">';

/// Print the main part of the page
$user = $casecourseware->caseid;
$mo = $casecourseware->videolink;
$ts   = time();
$sig  = casecourseware_fh32('314159265:'.$mo.':'.$user.':'.$ts.':271828183');

print '<div align="center"><div id="cw3_mo_flp"><h2 class="cw3_fancy">'.$casecourseware->name.'</h2><div><object class="pboj" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="640" height="407" id="flv_obj" ><param name="movie" value="https://mv-web-secure.case.edu/caseondemand169_v1.8.swf?oid='.$mo.'&amp;ts='.$ts.'&amp;user='.$user.'&amp;sig='.$sig.'" /><param name="allowscriptaccess" value="always" /><param name="salign" value="lt" /><param name="quality" value="high" /><param name="scale" value="default" /><param name="allowfullscreen" value="true" /><param name="resize" value="true" /><!--[if !IE]>-->

<object class="pobj" type="application/x-shockwave-flash" data="https://mv-web-secure.case.edu/caseondemand169_v1.8.swf?oid='.$mo.'&amp;ts='.$ts.'&amp;user='.$user.'&amp;sig='.$sig.'" width="640" height="407"><param name="allowscriptaccess" value="always" /><param name="salign" value="lt" /><param name="quality" value="high" /><param name="scale" value="default" /><param name="allowfullscreen" value="true" /><param name="resize" value="true" /><!--<![endif]--><p>This content requires the Adobe Flash Player version 9,0,124,0 or higher. You can download the player from the <a href="http://www.adobe.com/flash">Adobe Flash Player site</a></p><!--[if !IE]>--></object><!--<![endif]--></object></div></div>'.$casecourseware->intro.'</div>';
  echo '</div>';
echo '</div>';
  echo '<div class="clearer"></div>';
/// Finish the page
print_footer($course);

?>
