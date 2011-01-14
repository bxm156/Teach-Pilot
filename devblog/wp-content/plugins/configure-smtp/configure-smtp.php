<?php
/*
Plugin Name: Configure SMTP
Version: 2.7
Plugin URI: http://coffee2code.com/wp-plugins/configure-smtp
Author: Scott Reilly
Author URI: http://coffee2code.com
Description: Configure SMTP mailing in WordPress, including support for sending e-mail via SSL/TLS (such as GMail).

This plugin is the renamed, rewritten, and updated version of the wpPHPMailer plugin.

Use this plugin to customize the SMTP mailing system used by default by WordPress to handle *outgoing* e-mails.
It offers you the ability to specify:

	* SMTP host name
	* SMTP port number
	* If SMTPAuth (authentication) should be used.
	* SMTP username
	* SMTP password
	* If the SMTP connection needs to occur over ssl or tls

In addition, you can instead indicate that you wish to use GMail to handle outgoing e-mail, in which case the above
settings are automatically configured to values appropriate for GMail, though you'll need to specify your GMail
e-mail (included the "@gmail.com") and password.

Regardless of whether SMTP is enabled or configured, the plugin provides you the ability to define the name and 
email of the 'From:' field for all outgoing e-mails.

A simple test button is also available that allows you to send a test e-mail to yourself to check if sending
e-mail has been properly configured for your blog.

Compatible with WordPress 2.8+, 2.9+.

=>> Read the accompanying readme.txt file for more information.  Also, visit the plugin's homepage
=>> for more information and the latest updates

Installation:

1. Download the file http://coffee2code.com/wp-plugins/configure-smtp.zip and unzip it into your 
/wp-content/plugins/ directory (or install via the built-in WordPress plugin installer).
2. Activate the plugin through the 'Plugins' admin menu in WordPress.
3. Click the plugin's 'Settings' link next to its 'Deactivate' link (still on the Plugins page), or click on the 
Settings -> SMTP link, to go to the plugin's admin settings page.  Optionally customize the settings (to configure it 
if the defaults aren't valid for your situation).
4. (optional) Use the built-in test to see if your blog can properly send out e-mails.

*/

/*
Copyright (c) 2004-2010 by Scott Reilly (aka coffee2code)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation 
files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, 
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the 
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

if ( !class_exists('ConfigureSMTP') ) :

class ConfigureSMTP {
	var $admin_options_name = 'c2c_configure_smtp';
	var $nonce_field = 'update-configure_smtp';
	var $textdomain = 'configure-smtp';
	var $show_admin = true;	// Change this to false if you don't want the plugin's admin page shown.
	var $plugin_name = '';
	var $short_name = '';
	var $plugin_basename = '';
	var $config = array();
	var $gmail_config = array(
		'host' => 'smtp.gmail.com',
		'port' => '465',
		'smtp_auth' => true,
		'smtp_secure' => 'ssl'
	);
	var $options = array(); // Don't use this directly

	function ConfigureSMTP() {
		global $pagenow;
		$this->plugin_name = __('Configure SMTP', $this->textdomain);
		$this->short_name = __('SMTP', $this->textdomain);
		$this->plugin_basename = plugin_basename(__FILE__);
		$this->config = array(
			// input can be 'checkbox', 'short_text', 'text', 'textarea', 'inline_textarea', 'select', 'hidden', 'password', or 'none'
			//	an input type of 'select' must then have an 'options' value (an array) specified
			// datatype can be 'array' or 'hash'
			// can also specify input_attributes
			'use_gmail' => array('input' => 'checkbox', 'default' => false,
				'label' => __('Send e-mail via GMail?', $this->textdomain),
				'help' => __('Clicking this will override many of the settings defined below. You will need to input your GMail username and password below.', $this->textdomain),
				'input_attributes' => 'onclick="return configure_gmail();"'),
			'host' => array('input' => 'text', 'default' => 'localhost',
				'label' => __('SMTP host', $this->textdomain),
				'help' => __('If "localhost" doesn\'t work for you, check with your host for the SMTP hostname.', $this->textdomain)),
			'port' => array('input' => 'short_text', 'default' => 25,
				'label' => __('SMTP port', $this->textdomain),
				'help' => __('This is generally 25.', $this->textdomain)),
			'smtp_secure' => array('input' => 'select', 'default' => 'None',
				'label' => __('Secure connection prefix', $this->textdomain),
				'options' => array('', 'ssl', 'tls'),
				'help' => __('Sets connection prefix for secure connections (prefix method must be supported by your PHP install and your SMTP host)', $this->textdomain)),
			'smtp_auth'	=> array('input' => 'checkbox', 'default' => false,
				'label' => __('Use SMTPAuth?', $this->textdomain),
				'help' => __('If checked, you must provide the SMTP username and password below', $this->textdomain)),
			'smtp_user'	=> array('input' => 'text', 'default' => '',
				'label' => __('SMTP username', $this->textdomain),
				'help' => ''),
			'smtp_pass'	=> array('input' => 'password', 'default' => '',
				'label' => __('SMTP password', $this->textdomain),
				'help' => ''),
			'wordwrap' => array('input' => 'short_text', 'default' => '',
				'label' => __('Wordwrap length', $this->textdomain),
				'help' => __('Sets word wrapping on the body of the message to a given number of characters.', $this->textdomain)),
			'from_email' => array('input' => 'text', 'default' => '',
				'label' => __('Sender e-mail', $this->textdomain),
				'help' => __('Sets the From email address for all outgoing messages. Leave blank to use the WordPress default. This value will be used even if you don\'t enable SMTP. NOTE: This may not take effect depending on your mail server and settings, especially if using SMTPAuth (such as for GMail).', $this->textdomain)),
			'from_name'	=> array('input' => 'text', 'default' => '',
				'label' => __('Sender name', $this->textdomain),
				'help' => __('Sets the From name for all outgoing messages. Leave blank to use the WordPress default. This value will be used even if you don\'t enable SMTP.', $this->textdomain))
		);

		add_action('activate_' . str_replace(trailingslashit(WP_PLUGIN_DIR), '', __FILE__), array(&$this, 'install'));
		if ( 'options-general.php' == $pagenow )
			add_action('admin_footer', array(&$this, 'add_js'));
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('phpmailer_init', array(&$this, 'phpmailer_init'));
		add_action('wp_mail_from', array(&$this, 'wp_mail_from'));
		add_action('wp_mail_from_name', array(&$this, 'wp_mail_from_name'));
	}

	function install() {
		$this->options = $this->get_options();
		update_option($this->admin_options_name, $this->options);
	}

	function add_js() {
		$alert = __('Be sure to specify your GMail email address (with the @gmail.com) as the SMTP username, and your GMail password as the SMTP password.', $this->textdomain);
		echo <<<JS
		<script type="text/javascript">
			function configure_gmail() {
				if (jQuery('#use_gmail').attr('checked') == true) {
					jQuery('#host').val('{$this->gmail_config['host']}');
					jQuery('#port').val('{$this->gmail_config['port']}');
					jQuery('#smtp_auth').attr('checked', {$this->gmail_config['smtp_auth']});
					jQuery('#smtp_secure').val('{$this->gmail_config['smtp_secure']}');
					if (!jQuery('#smtp_user').val().match(/.+@gmail.com$/) ) {
						jQuery('#smtp_user').val('USERNAME@gmail.com').focus().get(0).setSelectionRange(0,8);
					}
					alert('{$alert}');
					return true;
				}
			}
		</script>

JS;
	}

	function admin_menu() {
		if ( $this->show_admin && current_user_can('manage_options') ) {
			add_filter( 'plugin_action_links_' . $this->plugin_basename, array(&$this, 'plugin_action_links') );
			add_options_page($this->plugin_name, $this->short_name, 'manage_options', $this->plugin_basename, array(&$this, 'options_page'));
		}
	}

	function plugin_action_links( $action_links ) {
		$settings_link = '<a href="options-general.php?page='.$this->plugin_basename.'">' . __('Settings', $this->textdomain) . '</a>';
		array_unshift( $action_links, $settings_link );
		return $action_links;
	}

	function phpmailer_init( $phpmailer ) {
		$options = $this->get_options();
		$phpmailer->IsSMTP();
		$phpmailer->Host = $options['host'];
		$phpmailer->Port = $options['port'] ? $options['port'] : 25;
		$phpmailer->SMTPAuth = $options['smtp_auth'] ? $options['smtp_auth'] : false;
		if ( $phpmailer->SMTPAuth ) {
			$phpmailer->Username = $options['smtp_user'];
			$phpmailer->Password = $options['smtp_pass'];
		}
		if ( $options['smtp_secure'] != '' )
			$phpmailer->SMTPSecure = $options['smtp_secure'];
		if ( $options['wordwrap'] > 0 )
			$phpmailer->WordWrap = $options['wordwrap'];
		return $phpmailer;
	}

	function wp_mail_from( $from ) {
		$options = $this->get_options();
		if ( $options['from_email'] )
			$from = $options['from_email'];
		return $from;
	}

	function wp_mail_from_name( $from_name ) {
		$options = $this->get_options();
		if ( $options['from_name'] )
			$from_name = htmlspecialchars_decode($options['from_name']);
		return $from_name;
	}

	function get_options() {
		if ( !empty($this->options) ) return $this->options;
		// Derive options from the config
		$options = array();
		foreach (array_keys($this->config) as $opt) {
			$options[$opt] = $this->config[$opt]['default'];
		}
		$existing_options = get_option($this->admin_options_name);
		if ( !empty($existing_options) ) {
			foreach ($existing_options as $key => $value)
				$options[$key] = $value;
		}
		$this->options = $options;
		return $options;
	}

	function options_page() {
		$options = $this->get_options();
		// See if user has submitted form
		if ( isset($_POST['submitted']) ) {
			check_admin_referer($this->nonce_field);

			foreach (array_keys($options) AS $opt) {
				$options[$opt] = htmlspecialchars(stripslashes($_POST[$opt]));
				$input = $this->config[$opt]['input'];
				if ( ($input == 'checkbox') && !$options[$opt] )
					$options[$opt] = 0;
				if ( $this->config[$opt]['datatype'] == 'array' ) {
					if ( $input == 'text' )
						$options[$opt] = explode(',', str_replace(array(', ', ' ', ','), ',', $options[$opt]));
					else
						$options[$opt] = array_map('trim', explode("\n", trim($options[$opt])));
				}
				elseif ( $this->config[$opt]['datatype'] == 'hash' ) {
					if ( !empty($options[$opt]) ) {
						$new_values = array();
						foreach (explode("\n", $options[$opt]) AS $line) {
							list($shortcut, $text) = array_map('trim', explode("=>", $line, 2));
							if ( !empty($shortcut) ) $new_values[str_replace('\\', '', $shortcut)] = str_replace('\\', '', $text);
						}
						$options[$opt] = $new_values;
					}
				}
			}
			// If GMail is to be used, those settings take precendence
			if ( $options['use_gmail'] )
				$options = wp_parse_args($this->gmail_config, $options);

			// Remember to put all the other options into the array or they'll get lost!
			update_option($this->admin_options_name, $options);
			$this->options = $options;
			echo "<div id='message' class='updated fade'><p><strong>" . __('Settings saved', $this->textdomain) . '</strong></p></div>';
		}
		elseif ( isset($_POST['submit_test_email']) ) {
			check_admin_referer($this->nonce_field);
			$user = wp_get_current_user();
			$email = $user->user_email;
			$timestamp = current_time('mysql');
			
			$message = sprintf(__('Hi, this is the %s plugin e-mailing you a test message from your WordPress blog.', $this->textdomain), $this->plugin_name);
			$message .= "\n\n";
			$message .= sprintf(__('This message was sent with this time-stamp: %s', $this->textdomain), $timestamp);
			$message .= "\n\n";
			$message .= __('Congratulations!  Your blog is properly configured to send e-mail.', $this->textdomain);
			wp_mail($email, __('Test message from your WordPress blog', $this->textdomain), $message);
			echo '<div class="updated"><p>' . __('Test e-mail sent.', $this->textdomain) . '</p>';
			echo '<p>' . sprintf(__('The body of the e-mail includes this time-stamp: %s.', $this->textdomain), $timestamp) . '</p></div>';
		}

		$action_url = $_SERVER['PHP_SELF'] . '?page=' . $this->plugin_basename;
		$logo = plugins_url(basename($_GET['page'], '.php') . '/c2c_minilogo.png');

		echo "<div class='wrap'><div class='icon32' style='width:44px;'><img src='$logo' alt='" . esc_attr__('A plugin by coffee2code', $this->textdomain) . "' /><br /></div>";
		echo '<h2>' . __('Configure SMTP Settings', $this->textdomain) . '</h2>';
		$str = '<a href="#test">' . __('test', $this->textdomain) . '</a>';
		echo '<p>' . sprintf(__('After you\'ve configured your SMTP settings, use the %s to send a test message to yourself.', $this->textdomain), $str) . '</p>';
		echo "<form name='configure_smtp' action='$action_url' method='post'>";
		wp_nonce_field($this->nonce_field);
		echo '<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform form-table">';
				foreach (array_keys($options) as $opt) {
					$input = $this->config[$opt]['input'];
					if ($input == 'none') continue;
					$label = $this->config[$opt]['label'];
					$value = $options[$opt];
					if ($input == 'checkbox') {
						$checked = ($value == 1) ? 'checked=checked ' : '';
						$value = 1;
					} else {
						$checked = '';
					};
					if ($this->config[$opt]['datatype'] == 'array') {
						if ($input == 'textarea' || $input == 'inline_textarea')
							$value = implode("\n", $value);
						else
							$value = implode(', ', $value);
					} elseif ($this->config[$opt]['datatype'] == 'hash') {
						$new_value = '';
						foreach ($value AS $shortcut => $replacement) {
							$new_value .= "$shortcut => $replacement\n";
						}
						$value = $new_value;
					}
					echo "<tr valign='top'>";
					if ($input == 'textarea') {
						echo "<td colspan='2'>";
						if ($label) echo "<strong>$label</strong><br />";
						echo "<textarea name='$opt' id='$opt' {$this->config[$opt]['input_attributes']}>" . $value . '</textarea>';
					} else {
						echo "<th scope='row'>$label</th><td>";
						if ($input == "inline_textarea")
							echo "<textarea name='$opt' id='$opt' {$this->config[$opt]['input_attributes']}>" . $value . '</textarea>';
						elseif ($input == 'select') {
							echo "<select name='$opt' id='$opt'>";
							foreach ($this->config[$opt]['options'] as $sopt) {
								$selected = $value == $sopt ? " selected='selected'" : '';
								echo "<option value='$sopt'$selected>$sopt</option>";
							}
							echo "</select>";
						} else {
							$tclass = ($input == 'short_text') ? 'small-text' : 'regular-text';
							if ($input == 'short_text') $input = 'text';
							echo "<input name='$opt' type='$input' id='$opt' value='$value' class='$tclass' $checked {$this->config[$opt]['input_attributes']} />";
						}
					}
					if ($this->config[$opt]['help']) {
						echo "<br /><span style='color:#777; font-size:x-small;'>";
						echo $this->config[$opt]['help'];
						echo "</span>";
					}
					echo "</td></tr>";
				}
		$txt = __('Save Changes', $this->textdomain);
		echo <<<END
			</table>
			<input type="hidden" name="submitted" value="1" />
			<div class="submit"><input type="submit" name="Submit" class="button-primary" value="{$txt}" /></div>
		</form>
			</div>
END;
		echo <<<END
				<style type="text/css">
					#c2c {
						text-align:center;
						color:#888;
						background-color:#ffffef;
						padding:5px 0 0;
						margin-top:12px;
						border-style:solid;
						border-color:#dadada;
						border-width:1px 0;
					}
					#c2c div {
						margin:0 auto;
						padding:5px 40px 0 0;
						width:45%;
						min-height:40px;
						background:url('$logo') no-repeat top right;
					}
					#c2c span {
						display:block;
						font-size:x-small;
					}
				</style>
				<div id='c2c' class='wrap'>
					<div>
END;
		$c2c = '<a href="http://coffee2code.com" title="coffee2code.com">' . __('Scott Reilly, aka coffee2code', $this->textdomain) . '</a>';
		echo sprintf(__('This plugin brought to you by %s.', $this->textdomain), $c2c);
		echo '<span><a href="http://coffee2code.com/donate" title="' . esc_attr__('Please consider a donation', $this->textdomain) . '">' .
		__('Did you find this plugin useful?', $this->textdomain) . '</a></span>';
		echo '</div></div>';

		$user = wp_get_current_user();
		$email = $user->user_email;
		echo '<div class="wrap"><h2><a name="test"></a>' . __('Send A Test', $this->textdomain) . "</h2>\n";
		echo '<p>' . __('Click the button below to send a test email to yourself to see if things are working.  Be sure to save any changes you made to the form above before sending the test e-mail.  Bear in mind it may take a few minutes for the e-mail to wind its way through the internet.', $this->textdomain) . "</p>\n";
		echo '<p>' . sprintf(__('This e-mail will be sent to your e-mail address, %s.', $this->textdomain), $email) . "</p>\n";
		echo "<form name='configure_smtp' action='$action_url' method='post'>\n";
		wp_nonce_field($this->nonce_field);
		echo '<input type="hidden" name="submit_test_email" value="1" />';
		echo '<div class="submit"><input type="submit" name="Submit" value="' . esc_attr__('Send test e-mail', $this->textdomain) . '" /></div>';
		echo '</form></div>';
	}

} // end ConfigureSMTP

endif; // end if !class_exists()

// This function was introduced in PHP5. Backcomp via http://php.net/manual/en/function.htmlspecialchars-decode.php
if ( !function_exists('htmlspecialchars_decode') ) {
	function htmlspecialchars_decode( $string, $quote_style = ENT_COMPAT ) {
		$translation = array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style));
		if ( $quote_style === ENT_QUOTES ) { $translation['&#039;'] = '\''; }
		return strtr($string, $translation);
	}
}

if ( class_exists('ConfigureSMTP') )
	new ConfigureSMTP();

?>