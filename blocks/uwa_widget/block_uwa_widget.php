<?php
include_once ('lib/uwawidget.php');

/**
 * Moodle-Block that allows to include UWA-Widgets.
 * See http://eco.netvibes.org for available widgets.
 * 
 * 
 * @author Skaldrom Y. Sarg http://www.oncode.info
 * @copyright Copyright &copy; 2007, Skaldrom Y. Sarg of oncode.info
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL V2
 * @version 1.0
 */
class block_uwa_widget extends block_base {
	protected $uwawidget;

	function init() {
		$this->title= get_string('uwa_widget', 'block_uwa_widget');
		$this->version= 2008012900;
	}

	function specialization() {
		if (isset ($this->config->moduleUrl)) {
			$this->uwawidget= new uwawidget($this->config->moduleUrl);
			if (isset ($this->config->configuration)) {
				$this->uwawidget->setConfiguration($this->config->configuration);
			}
			if (isset ($this->config->preferences)) {
				$this->uwawidget->setPreferences($this->config->preferences);
			}
			$this->title= $this->uwawidget->getTitle();
		}
	}

	function applicable_formats() {
		return array (
			'all' => true
		);
	}

	function initContent($text= "") {
		$this->content= new stdClass;
		$this->content->items= array ();
		$this->content->icons= array ();
		$this->content->footer= '';
		$this->content->text= $text ? $text : get_string('needsconfiguration', 'block_uwa_widget');
	}

	function get_content() {
		global $COURSE, $CFG;


		if ($this->content !== NULL) {
			return $this->content;
		}

		if (!$this->content) {
			$this->initContent();
		}

		//$this->config->moduleurl= 'http://www.netvibes.com/api/uwa/examples/digg.xhtml';
		if (isset ($this->uwawidget)) {
			$div= "UWAWidget-" . $this->instance->id;
			$this->content->text= $this->uwawidget->getWidgetHTML($div, get_string('widgetloading', 'block_uwa_widget', $this->uwawidget->getTitle()));
		} else {
			$this->content->text= get_string('needsconfiguration', 'block_uwa_widget');
		}
		if ($this->content !== NULL) {
			return $this->content;
		}
	}

	function instance_config_save($data) {
		$savedata= null;
		if (isset ($data->general['moduleUrl'])) {
			$savedata->moduleUrl= $data->general['moduleUrl'];
		}

		$savedata->configuration= $data->configuration;
		$savedata->preferences= $data->preferences;

		return parent :: instance_config_save($savedata);
	}

	function instance_allow_multiple() {
		return true;
	}
	function instance_allow_config() {
		return true;
	}
}
?>
