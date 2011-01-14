=== Configure SMTP ===
Contributors: coffee2code
Donate link: http://coffee2code.com/donate
Tags: email, smtp, gmail, sendmail, wp_mail, phpmailer, outgoing mail, tls, ssl, security, privacy, wp-phpmailer, coffee2code
Requires at least: 2.8
Tested up to: 2.9.1
Stable tag: 2.7
Version: 2.7

Configure SMTP mailing in WordPress, including support for sending e-mail via SSL/TLS (such as GMail).

== Description ==

Configure SMTP mailing in WordPress, including support for sending e-mail via SSL/TLS (such as GMail).

This plugin is the renamed, rewritten, and updated version of the wpPHPMailer plugin.

Use this plugin to customize the SMTP mailing system used by default by WordPress to handle *outgoing* e-mails. It offers you the ability to specify:

* SMTP host name
* SMTP port number
* If SMTPAuth (authentication) should be used.
* SMTP username
* SMTP password
* If the SMTP connection needs to occur over ssl or tls

In addition, you can instead indicate that you wish to use GMail to handle outgoing e-mail, in which case the above settings are automatically configured to values appropriate for GMail, though you'll need to specify your GMail e-mail (including the "@gmail.com") and password.

Regardless of whether SMTP is enabled, the plugin provides you the ability to define the name and e-mail of the 'From:' field for all outgoing e-mails.

A simple test button is also available that allows you to send a test e-mail to yourself to check if sending e-mail has been properly configured for your blog.

== Installation ==

1. Unzip `configure-smtp.zip` inside the `/wp-content/plugins/` directory (or install via the built-in WordPress plugin installer)
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Click the plugin's `Settings` link next to its `Deactivate` link (still on the Plugins page), or click on the `Settings` -> `SMTP` link, to go to the plugin's admin settings page.  Optionally customize the settings (to configure it if the defaults aren't valid for your situation).
1. (optional) Use the built-in test to see if your blog can properly send out e-mails.

== Frequently Asked Questions ==

= I am already able to receive e-mail sent by my blog, so would I have any use or need for this plugin? =

Most likely, no.  Not unless you have a preference for having your mail sent out via a different SMTP server, such as GMail.

= How do I find out my SMTP host, and/or if I need to use SMTPAuth and what my username and password for that are? =

Check out the settings for your local e-mail program.  More than likely that is configured to use an outgoing SMTP server.  Otherwise, contact your host or someone more intimately knowledgeable about your situation.

= I've sent out a few test e-mails using the test button after having tried different values for some of the settings; how do I know which one worked? =

If your settings worked, you should receive the test e-mail at the e-mail address associated with your WordPress blog user account.  That e-mail contains a time-stamp which was reported to you by the plugin when the e-mail was sent.  If you are trying out various setting values, be sure to record what your settings were and what the time-stamp was when sending with those settings.

== Screenshots ==

1. A screenshot of the plugin's admin settings page.

== Changelog ==

= 2.7 =
* Fix to prevent HTML entities from appearing in the From name value in outgoing e-mails
* Added full support for localization
* Added .pot file
* Noted that overriding the From e-mail value may not take effect depending on mail server and settings, particular if SMTPAuth is used (i.e. GMail)
* Changed invocation of plugin's install function to action hooked in constructor rather than in global space
* Update object's option buffer after saving changed submitted by user
* Miscellaneous tweaks to update plugin to my current plugin conventions
* Noted compatibility with WP2.9+
* Dropped compatibility with versions of WP older than 2.8
* Updated readme.txt
* Updated copyright date

= 2.6 =
* Now show settings page JS in footer, and only on the admin settings page
* Removed hardcoded path to plugins dir
* Changed permission check
* Minor reformatting (added spaces)
* Tweaked readme.txt
* Removed compatibility with versions of WP older than 2.6
* Noted compatibility with WP 2.8+

= 2.5 =
* NEW
* Added support for GMail, including configuring the various settings to be appropriate for GMail
* Added support for SMTPSecure setting (acceptable values of '', 'ssl', or 'tls')
* Added "Settings" link next to "Activate"/"Deactivate" link next to the plugin on the admin plugin listings page
* CHANGED
* Tweaked plugin's admin options page to conform to newer WP 2.7 style
* Tweaked test e-mail subject and body
* Removed the use_smtp option since WP uses SMTP by default, the plugin can't help you if it isn't using SMTP already, and the plugin should just go ahead and apply if it is active
* Updated description, installation instructions, extended description, copyright
* Extended compatibility to WP 2.7+
* Facilitated translation of some text
* FIXED
* Fixed bug where specified wordwrap value wasn't taken into account

= 2.0 =
* Initial release after rewrite from wpPHPMailer

= pre-2.0 =
* Earlier versions of this plugin existed as my wpPHPMailer plugin, which due to the inclusion of PHPMailer within WordPress's core and necessary changes to the plugin warranted a rebranding/renaming.

