<?php // $Id: pagelib.php,v 1.14.4.1 2007/11/02 16:19:58 tjhunt Exp $

require_once($CFG->libdir.'/pagelib.php');
require_once($CFG->dirroot.'/course/lib.php'); // needed for some blocks

define('PAGE_CASECOURSEWARE_VIEW',   'mod-casecourseware-v');

page_map_class(PAGE_CASECOURSEWARE_VIEW, 'page_casecourseware');

$DEFINEDPAGES = array(PAGE_CASECOURSEWARE_VIEW);

/**
 * Class that models the behavior of a quiz
 *
 * @author Jon Papaioannou
 * @package pages
 */

class page_casecourseware extends page_generic_activity {

    function init_quick($data) {
        if(empty($data->pageid)) {
            error('Cannot quickly initialize page: empty course id');
        }
        $this->activityname = 'casecourseware';
        parent::init_quick($data);
    }
  
    function get_type() {
        return PAGE_CASECOURSEWARE_VIEW;
    }
	   // BLOCKS RELATED SECTION

    // Which are the positions in this page which support blocks? Return an array containing their identifiers.
    // BE CAREFUL, ORDER DOES MATTER! In textual representations, lists of blocks in a page use the ':' character
    // to delimit different positions in the page. The part before the first ':' in such a representation will map
    // directly to the first item of the array you return here, the second to the next one and so on. This way,
    // you can add more positions in the future without interfering with legacy textual representations.
    function blocks_get_positions() {
        return array(BLOCK_POS_LEFT, BLOCK_POS_RIGHT);
    }

    // When a new block is created in this page, which position should it go to?
    function blocks_default_position() {
        return BLOCK_POS_LEFT;
    }

    // When we are creating a new page, use the data at your disposal to provide a textual representation of the
    // blocks that are going to get added to this new page. Delimit block names with commas (,) and use double
    // colons (:) to delimit between block positions in the page. See blocks_get_positions() for additional info.
    function blocks_get_default() {
        global $CFG;

        $this->init_full();

            $pageformat = $this->courserecord->format;
            if (!empty($CFG->{'defaultblocks_'. $pageformat})) {
                $blocknames = $CFG->{'defaultblocks_'. $pageformat};
            }
            else {
                $format_config = $CFG->dirroot.'/course/format/'.$pageformat.'/config.php';
                if (@is_file($format_config) && is_readable($format_config)) {
                    require($format_config);
                }
                if (!empty($format['defaultblocks'])) {
                    $blocknames = $format['defaultblocks'];
                }
                else if (!empty($CFG->defaultblocks)){
                    $blocknames = $CFG->defaultblocks;
                }
                /// Failsafe - in case nothing was defined.
                else {
                    $blocknames = 'participants,activity_modules,search_forums,admin,course_list:news_items,calendar_upcoming,recent_activity';
                }
            }
        

        return $blocknames;
    }

    // Given an instance of a block in this page and the direction in which we want to move it, where is
    // it going to go? Return the identifier of the instance's new position. This allows us to tell blocklib
    // how we want the blocks to move around in this page in an arbitrarily complex way. If the move as given
    // does not make sense, make sure to return the instance's original position.
    //
    // Since this is going to get called a LOT, pass the instance by reference purely for speed. Do **NOT**
    // modify its data in any way, this will actually confuse blocklib!!!
    function blocks_move_position(&$instance, $move) {
        if($instance->position == BLOCK_POS_LEFT && $move == BLOCK_MOVE_RIGHT) {
            return BLOCK_POS_RIGHT;
        } else if ($instance->position == BLOCK_POS_RIGHT && $move == BLOCK_MOVE_LEFT) {
            return BLOCK_POS_LEFT;
        }
        return $instance->position;
    }
}



?>
