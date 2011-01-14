<?php  // $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $

/**
 * This page prints a particular instance of vimoodle
 *
 * @author  Your Name <your@email.address>
 * @version $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $
 * @package mod/vimoodle
 */

/// (Replace vimoodle with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/localib.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // vimoodle instance ID

if ($id) {
    if (! $cm = get_coursemodule_from_id('vimoodle', $id)) {
        error('Course Module ID was incorrect');
    }

    if (! $course = get_record('course', 'id', $cm->course)) {
        error('Course is misconfigured');
    }

    if (! $vimoodle = get_record('vimoodle', 'id', $cm->instance)) {
        error('Course module is incorrect');
    }

} else if ($a) {
    if (! $vimoodle = get_record('vimoodle', 'id', $a)) {
        error('Course module is incorrect');
    }
    if (! $course = get_record('course', 'id', $vimoodle->course)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('vimoodle', $vimoodle->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

add_to_log($course->id, "vimoodle", "view", "view.php?id=$cm->id", "$vimoodle->id");

/// Print the page header
$strvimoodles = get_string('modulenameplural', 'vimoodle');
$strvimoodle  = get_string('modulename', 'vimoodle');

$navlinks = array();
$navlinks[] = array('name' => $strvimoodles, 'link' => "index.php?id=$course->id", 'type' => 'activity');
$navlinks[] = array('name' => format_string($vimoodle->name), 'link' => '', 'type' => 'activityinstance');

$navigation = build_navigation($navlinks);

print_header_simple(format_string($vimoodle->name), '', $navigation, '', '', true,
              update_module_button($cm->id, $course->id, $strvimoodle), navmenu($course, $cm));

/// Print the main part of the page
$user = $vimoodle->caseid;
$video = $vimoodle->videoid;
$pin = $vimoodle->pin;
$album = $vimoodle->albumid;
$client = new jsonRPCClient();

require_once("layout.php");

/// Finish the page
print_footer($course);

?>