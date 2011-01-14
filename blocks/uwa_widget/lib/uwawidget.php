<?php

/**
 * A library to handle the embedding of of uwa widgets.
 *
 * UWA is the Cross-Platform Widget API (http://dev.netvibes.com/) 
 * See http://dev.netvibes.com/doc/uwa_specification/uwa_skeleton and
 * http://dev.netvibes.com/doc/uwa_specification/content_of_the_xhtml_file
 * for more information
 * 
 * Nomenclature:
 * Configuration: Settings for display
 * Preference: Widget dependent settings
 * PreferenceSettings: Metadata for the widget dependent settings 
 * 
 * @author Skaldrom Y. Sarg http://www.oncode.info
 * @copyright Copyright &copy; 2007, Skaldrom Y. Sarg of oncode.info
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL V2
 * @version 1.0
 */

class uwawidget {
	const DEFAULTTITLE= 'Default Title'; // Default Module Title
	protected $moduleUrl= "";
	static protected $divId= 0;

	protected $configurationSettings= array ();
	protected $configuration= array ();
	protected $preferencesSettings= array ();
	protected $preferences= array ();

	protected $metaData= array (); // Contains author, keywords, ...

	protected $additionalData= array (); // Contains icon, stylesheet

	function __construct($moduleUrl= "") {
		$this->initConfigurationSettings();
		$this->initValues($this->configurationSettings, $this->configuration);
		if ($moduleUrl) {
			$this->moduleUrl= $moduleUrl;
			$this->fetchWidgetInfos();
			$this->initValues($this->preferencesSettings, $this->preferences);
		}
	}

	protected function fetchWidget() {
		// Try the common way, curl otherwise
		if ($widget= @ file($this->moduleUrl)) {
			$widget= join('', $widget);
		} else {
			$ch= curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->moduleUrl);
			curl_setopt($ch, CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.6) Gecko/20070723 Iceweasel/2.0.0.6 (Debian-2.0.0.6-0etch1+lenny1)');
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$widget= curl_exec($ch);
			curl_close($ch);
		}
		if (!$widget) {
			throw new exception("Connection problems (Cable, IP, Proxy, .. ???). You need to enable fopen wrappers or include cURL to get $this->moduleUrl.");
		}



		return $widget;
	}

	/**
	 * Get widget header sanitized
	 */
	protected function fetchWidgetHead() {
		$ret="";
		$widget=$this->fetchWidget();
		//
		// Remove the whole script-part , because it is often broken like hell, filled with non-entities-tags!		
		$widget= preg_replace('@< *script +type *= *"text/javascript" *>.*< */script *>@isU', '', $widget);

		// To avoid XML-Errors, we just want the head
		preg_match('@.*</ *head *>@is', $widget, $widgethead);
		if(!$widgethead) {
			throw new exception("Widget has no valid XML.");
		}

		$widget=$widgethead[0];
		// We do not need the style either
		$widget= preg_replace('@< *style.*>.*< */style *>@isU', '', $widget);

		$widget=$widget."<body></body></html>";


		// Strange Namespaces
		$widget= preg_replace('@< *widget *:(.+)>@i', '<$1>', $widget);
		$widget= preg_replace('@< */ *widget *:(.+)>@i', '</$1>', $widget);
		return $widget;
			
	}

	public function getModuleUrl() {
		return $this->moduleUrl;
	}

	public function cleanUp() {
		$this->moduleUrl= "";
		$this->metaData= array ();
		$this->additionalData= array ();
		$this->preferences= array ();
		$this->preferencesSettings= array ();
	}

	public function setModuleUrl($moduleUrl) {
		$this->cleanUp();
		$this->__construct($moduleUrl);
	}

	public function getTitle() {
		return $this->configuration['title'];
	}

	public function getAdditionalData() {
		return $this->additionalData;
	}

	/**
	 * Returns an Array with the modules Metadata, like author, keywords an the like
	 */
	public function getMetaData() {
		return $this->metaData;
	}

	/**
	 * Takes an array of display-preferences. The following fields may be set:
	 * Setting            Description                                                          Example                  Default value
	 * title:                the title of the widget (string)                           'title':'Digg'             'Default title'
	 * height:            the height of the div, in pixels (string)              'height':'250'          '250'
	 * borderWidth:  the width of the div's border, in pixels (string) 'borderWidth':'1'    '1'
	 * color:               the color of the widget's border (string)            'color':'#aaaaaa'     '#aaaaaa'
	 * displayTitle:    wether or not to display the title (boolean)      'displayTitle':true    true
	 * displayFooter: wether or not to display the footer,                  'displayFooter':true true 
	 *                          which includes the "powered by netvibes uwa" 
	 *                          logo (boolean) 
	 */
	public function setConfiguration($configuration) {
		if ($configuration["title"] == self :: DEFAULTTITLE) {
			$configuration["title"]= $this->getTitle();
		}
		$this->fillValues($configuration, $this->configuration);
	}

	/** 
	 * Retrieve to save!
	 */
	public function getConfiguration() {
		return $this->configuration;
	}

	public function setPreferences($preferences) {
		$this->fillValues($preferences, $this->preferences);
	}

	/**
	 * Fills values from configuration forms. The valuecontainer has to contain indices
	 * for all values (initValues!!!)
	 * 
	 */
	protected function fillValues($values, & $container) {
		foreach ($container as $name => $content) {
			if (isset ($values[$name])) {
				$container[$name]= $values[$name];
			} else {
				$container[$name]= '';
			}
		}
	}

	/** 
	 * Retrieve to save!
	 */
	public function getPreferences() {
		return $this->preferences;
	}

	/**
	 * Get as much infos as possible out of the widgets xhtml file.
	 */
	protected function fetchWidgetInfos() {
	          error_reporting(E_ALL);
		$widgetCode= $this->fetchWidgetHead();

		// We cannot use simple XML, because the xml is heavy broken in lots of widgets
		try {
		  $xml= new SimpleXMLElement($widgetCode);
		} catch(Exception $e) {
			throw new exception("$this->moduleUrl has no valid XML.");
		}
		//print_r($xml); print "\n\n\n"; print htmlentities($widgetCode); die();

		// Fetch metadata
		$this->metaData= array ();
		foreach ($xml->head->meta as $data) {
			$index= (string) $data['name'];
			$value= (string) $data['content'];
			$this->metaData[$index]= $value;
		}

		// Additional data
		foreach ($xml->head->link as $data) {
			$index= (string) $data['rel'];
			$value= (string) $data['href'];
			$this->additionalData[$index]= $value;
		}

		// Configuration

		if (isset ($xml->head->title[0])) {
			$this->configuration['title']= (string) $xml->head->title[0];
		}

		// Preference settings
		if (!isset ($xml->head->preferences->preference)) {
			$xml->head->preferences->preference= "";
		}
		foreach ($xml->head->preferences->preference as $preference) {
			$index= (string) $preference['name'];

			if (isset ($preference['label']))
				$this->preferencesSettings[$index]['settings']['label']= (string) $preference['label'];
			if (isset ($preference['type']))
				$this->preferencesSettings[$index]['settings']['type']= (string) $preference['type'];
			if (isset ($preference['defaultValue']))
				$this->preferencesSettings[$index]['settings']['defaultValue']= (string) $preference['defaultValue'];
			if (isset ($preference['onchange']))
				$this->preferencesSettings[$index]['settings']['onchange']= (string) $preference['onchange'];
			if (isset ($preference['min']))
				$this->preferencesSettings[$index]['settings']['min']= (string) $preference['min'];
			if (isset ($preference['max']))
				$this->preferencesSettings[$index]['settings']['max']= (string) $preference['max'];
			if (isset ($preference['step']))
				$this->preferencesSettings[$index]['settings']['step']= (string) $preference['step'];
			if (isset ($preference->option)) {
				foreach ($preference->option as $option) {
					$label= (string) $option['label'];
					$value= (string) $option['value'];
					$this->preferencesSettings[$index]['option'][$label]= $value;
				}
			}
		}
		//print_r($this->configuration);
	}

	/**
	 * Fill Preferences, either with values or defaults
	 */
	protected function initValues($settings, & $values) {
		foreach ($settings as $name => $content) {
			if (!isset ($values[$name])) {
				// true and false for checkboxes
				$emptyvalue= "";
				if ($content['settings']['type'] == 'boolean') {
					$emptyvalue= "false";
				}
				$values[$name]= isset ($content['settings']['defaultValue']) ? $content['settings']['defaultValue'] : $emptyvalue;
			}
		}
	}

	/**
	 * Fill display configuration settings, either with values or defaults
	 */
	protected function initConfigurationSettings() {
		if (!isset ($this->configurationSettings['title'])) {
			$this->configurationSettings['title']['settings']= array (
				'type' => 'text',
				'label' => "Title",
				'defaultValue' => self :: DEFAULTTITLE
			);
		}
		if (!isset ($this->configurationSettings['height'])) {
			$this->configurationSettings['height']['settings']= array (
				'type' => 'text',
				'label' => "Height",
				'defaultValue' => '250'
			);
		}
		if (!isset ($this->configurationSettings['borderWidth'])) {
			$this->configurationSettings['borderWidth']['settings']= array (
				'type' => 'text',
				'label' => "Width of border",
				'defaultValue' => '0'
			);
		}
		if (!isset ($this->configurationSettings['color'])) {
			$this->configurationSettings['color']['settings']= array (
				'type' => 'text',
				'label' => "Bordercolor",
				'defaultValue' => '#aaaaaa'
			);
		}
		if (!isset ($this->configurationSettings['displayTitle'])) {
			$this->configurationSettings['displayTitle']['settings']= array (
				'type' => 'boolean',
				'label' => "Show title",
				'defaultValue' => 'false'
			);
		}
		if (!isset ($this->configurationSettings['displayFooter'])) {
			$this->configurationSettings['displayFooter']['settings']= array (
				'type' => 'boolean',
				'label' => "Show footer",
				'defaultValue' => 'false'
			);
		}
	}

	/**
	 * Returns tha HTML for embedding
	 * 
	 * @param $div string ID of the DIV-Element
	 */
	public function getWidgetHTML($div= "", $loadingwidgetmessage= "", $inline= false) {
		// Fill empty preferences			
		if (!$this->moduleUrl) {
			return;
		}
		if (!$div) {
			$div= "UWAWidget" . ($this->divId++);
		}
		if (!$loadingwidgetmessage) {
			$loadingwidgetmessage= "Widget " + $this->getTitle() . " loading...";
		}
		$ret= '<script type="text/javascript" src="http://www.netvibes.com/js/UWA/load.js.php?env=BlogWidget"></script>' . "\n";
		$ret .= '  <div id="' . $div . '">' . $loadingwidgetmessage . "</div>\n";
		$ret .= '  <script type="text/javascript">' . "\n";
		$ret .= "  var BW = new UWA.BlogWidget(" . "\n";
		$ret .= "  { container: document.getElementById('" . $div . "'),\n";
		$ret .= "  moduleUrl: '" . $this->moduleUrl . "', \n";
		$ret .= "  inline: " . ($inline ? "true" : "false") . " } );\n";

		if ($this->configuration) {
			$ret .= "  BW.setConfiguration(\n";
			$ret .= "    {" . $this->getJSArray($this->configuration) . "});\n";
		}
		if ($this->preferences) {
			$ret .= "  BW.setPreferencesValues(\n";
			$ret .= "    {" . $this->getJSArray($this->preferences) . "});\n";
		}
		$ret .= "  </script>\n";
		return $ret;
	}

	/**
	 * Creates an array for the JavaScript part
	 */
	protected function getJSArray(array $tupels) {
		$ret= array ();
		foreach ($tupels as $key => $value) {
			$ret[]= "'" . $key . "': '" . $value . "'";
		}
		return join(', ', $ret);
	}

	/**
	 * Creates Form Data of a setting
	 */
	protected function getSettingsFormDataFromSettings($name, $setting, $values) {
		$ret= array ();
		switch ($setting['settings']['type']) {
			case 'text' :
				$ret['type']= 'text';
				break;
			case 'boolean' :
				$ret['type']= 'checkbox';
				break;
			case 'hidden' :
				$ret['type']= 'hidden';
				break;
			case 'range' :
				$ret['type']= 'select';
				if (isset ($setting['settings']['min']) && isset ($setting['settings']['max'])) {
					$step= isset ($setting['settings']['step']) ? $setting['settings']['step'] : 1;
					for ($i= $setting['settings']['min']; $i <= $setting['settings']['max']; $i += $step) {
						$ret['option'][$i]= $i;
					}
				}
				break;
			case 'list' :
				$ret['type']= 'select';
				if (isset ($setting['option']) && is_array($setting['option'])) {
					$ret['option']= $setting['option'];
				}
				break;
			case 'password' :
				$ret['type']= 'passwort';
				break;
			case 'textarea' :
				$ret['type']= 'textarea';
				break;
			default :
				throw new exception("Do not know the field type " . $setting['settings']['type'] . "!");
				break;
		}

		if (isset ($setting['settings']['label'])) {
			$ret['label']= $setting['settings']['label'];
		} else {
			$ret['label']= $name;
		}
		if (isset ($setting['settings']['onchange'])) {
			$ret['onchange']= $setting['settings']['onchange'];
		}

		// Get current value
		if (isset ($values[$name])) {
			$ret['value']= $values[$name];
		} else {
			$emptyvalue= '';
			if ($setting['settings']['type'] == 'boolean') {
				$emptyvalue= "false";
			}
			$ret['value']= $emptyvalue;
		}
		return $ret;
	}

	/**
	 * This function returns data that facilates configuration/settings
	 * form generation
	 * 
	 * Use this function if you have a custom formbuilding system.
	 * 
	 * Return:
	 * $array[NAME][DATA]
	 * 
	 * SECTION can be "general", "configuration" and "settings"
	 * NAME is the name of the formfield
	 * DATA can be:
	 *   type: What type of input shall be shown
	 *   option: (optional) list of options "label"=>"value"
	 *   value: Current Value of this field
	 *   label: A label
	 *   onchange: What happens on change? 
	 * 
	 * @param $section string Defines the form: "general", "configuration" or "preferences""
	 */
	public function getSettingsFormData($section) {
		$ret= array ();
		switch ($section) {
			case "general" :
				$ret['moduleUrl']= array (
					'type' => 'text',
					'value' => $this->moduleUrl,
					'label' => "Widget URL"
				);
				break;
			case "configuration" :
				foreach ($this->configurationSettings as $name => $setting) {
					$ret[$name]= $this->getSettingsFormDataFromSettings($name, $setting, $this->configuration);
				}
				break;
			case "preferences" :
				foreach ($this->preferencesSettings as $name => $setting) {
					$ret[$name]= $this->getSettingsFormDataFromSettings($name, $setting, $this->preferences);
				}
				break;
			default :
				throw new exception("No section $section for formdata.");
				break;
		}
		return $ret;
	}

	/**
	 * Create an Array in the form [SECTION][LABEL] => "HTML-for-Input-field"
	 * Include it between form and submit-tags and you are done.
	 * The "hidden" index contains all hidden fields.
	 * 
	 * @param $section string Defines the form: "general", "configuration" or "preferences""
	 * @param $prefice array 		Array with prefices for the different sections
	 */
	public function getSettingsHTML($section) {
		$ret= array ();
		$settings= $this->getSettingsFormData($section);
		
		foreach ($settings as $name => $setting) {
			$label= $setting["label"];
			switch ($setting['type']) {
				case 'text' :
					$ret[$label]= '<input type="text" name="' . $section . "[" . $name . ']" value="' . $setting['value'] . '" />';
					break;
				case 'checkbox' :
					$ret[$label]= '<input type="checkbox" name="' . $section . "[" . $name . ']" value="true" ' . ($setting['value'] == 'true' ? 'checked ' : '') . '/>';
					break;
				case 'hidden' :
					$ret['hidden'][$label]= '<input type="hidden" name="' . $section . "[" . $name . ']" value="' . $setting['value'] . '" />';
					break;
				case 'select' :
					$options= array ();
					foreach ($setting['option'] as $optionlabel => $value) {
						$options[]= '<option value ="' . $value . '"' . ($setting['value'] == $value ? ' selected' : '') . '>' . $optionlabel . "</option>\n";
					}
					$ret[$label]= '<select name="' . $section . "[" . $name . ']" size="1">' . "\n" . join('', $options) . '</select>';
					break;
				case 'password' :
					$ret[$label]= '<input type="password" name="' . $section . "[" . $name . ']" size="5" value="' . $setting['value'] . '" />';
					break;
				case 'textarea' :
					$ret[$label]= '<textarea name="' . $section . "[" . $name . ']">' . $setting['value'] . '</textarea>';
					break;
				default :
					throw new exception("Do not know the settings type " . $setting['type'] . "!");
					break;
			}
		}
		return $ret;
	}

	public function __toString() {
		$ret= "<pre>\n";
		$ret .= "ModuleUrl: $this->moduleUrl \n";
		$ret .= "\nMetaData:\n";
		$ret .= print_r($this->metaData, 1);
		$ret .= "\nAdditionalData:\n";
		$ret .= print_r($this->additionalData, 1);
		$ret .= "\nConfigurationSettings:\n";
		$ret .= print_r($this->configurationSettings, 1);
		$ret .= "\nConfiguration:\n";
		$ret .= print_r($this->configuration, 1);
		$ret .= "\nPreferencesSettings:\n";
		$ret .= print_r($this->preferencesSettings, 1);
		$ret .= "\nPreferences:\n";
		$ret .= print_r($this->preferences, 1);
		$ret .= "</pre>\n";
		return $ret;
	}
}
?>
