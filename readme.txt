=== Mailigen Widget ===
Contributors: krisjanis@imedia.lv
Tags: email marketing, mailigen, mailing list, newsletter, signup form, widget
Requires at least: 3.0.1
Tested up to: 4.1
Stable tag: trunk

Add a signup form to your sidebar for your Mailigen mailing list.

== Description ==

This plugin provides an easy, lightweight way to let your users sign up for your Mailigen list.
The Wordpress widget sign-up form is a double opt-in signup form, just like your other Mailigen forms. When a potential subscriber fills out the form, they will receive confirmation to their email address asking them to confirm their subscription.

The Mailigen Widget:

*	is easy to use
*	is easy to configure
*	is AJAX-based
*	displays the collection of only information that you actually need (i.e., an email address and a name) to ask from your mailers

> __This plugin requires a <a href="http://www.mailigen.com" title="Sign up for a free Mailigen trial" rel="nofollow">Mailigen account</a>.__ <br />*Don't have an account?* Mailigen offers a <a href="http://www.mailigen.com/sign-up" rel="nofollow">free 30 day trial</a>, so sign up and give this plugin a try!


== Installation ==

1. Upload the mailigen-widget to /wp-content/plugins/.
1. Activate the plugin through the "Plugins" menu in WordPress.
1. Enter valid Mailigen user credentials on the plugin admin page ("Settings" >> "Mailigen Widget").
1. Select a mailing list, preferable input fields.
1. Drag the widget into your sidebar from the "Widgets" menu in WordPress and you're ready to go!
1. Please rate the plugin.

== Frequently Asked Questions ==

= Do I need a Mailigen account? =

Yes, this plugin requires a <a href="http://www.mailigen.com/sign-up" title="Sign up for Mailigen" rel="nofollow">Mailigen account</a>.

= Where is the settings page =

In the WordPress administration, navigate to Settings > Mailigen Widget in the WordPress sidebar. The URL should be `[yoursite.com]/wp-admin/options-general.php?page=settings-mailigen`


== Screenshots ==

1. Just add your Mailigen user credentials.
1. Choose your Mailigen list and prefered fields.
1. Select your Widget Options.
1. The widget displays in your sidebar.


== Changelog ==

= 1.2.1 =
* Added new features (Thanks to Ted Barnett):
 * multiple subscribe form widgets allowed on one page
 * ability to hide field labels (adds labels inside text fields)

= 1.2.0 =
* Added new features (Thanks to Ted Barnett):
 * ability to collect checkbox, dropdown, and radio fields (user must replicate values on the plugin settings separated by a comma)
 * ability to set a widget description text
 * ability to set a custom Success Message
 * ability to set an optional Redirect URL after submission
 * ability to turn off double opt-in
 * ability to turn off update existing user
 * ability to turn off send welcome email
* Added waiting indicator when user needs to wait for a response from server after pressing subscribe button
* Updated Mailigen API library to version 1.5

= 1.1.2 =
* Fixed bug where the signup form was not working in non-index pages

= 1.1.1 =
* Fixed bug when plugin could not be activated because of a fatal error `Parse error: syntax error, unexpected ':' in ../wp-content/plugins/mailigen-widget/mailigen-widget.php on line 385`. Shorthand form of ternary operator `?:` is available starting from PHP v5.3

= 1.1 =
* First release.


== Upgrade Notice ==

= 1.2.1 =
* Added new features (Thanks to Ted Barnett):
 * multiple subscribe form widgets allowed on one page
 * ability to hide field labels (adds labels inside text fields)

= 1.2.0 =
* Added new features (Thanks to Ted Barnett):
 * ability to collect checkbox, dropdown, and radio fields (user must replicate values on the plugin settings separated by a comma)
 * ability to set a widget description text
 * ability to set a custom Success Message
 * ability to set an optional Redirect URL after submission
 * ability to turn off double opt-in
 * ability to turn off update existing user
 * ability to turn off send welcome email
* Added waiting indicator when user needs to wait for a response from server after pressing subscribe button
* Updated Mailigen API library to version 1.5

= 1.1.2 =
* Fixed bug where the signup form was not working in non-index pages

= 1.1.1 =
* Fixed bug when plugin could not be activated because of a fatal error `Parse error: syntax error, unexpected ':' in ../wp-content/plugins/mailigen-widget/mailigen-widget.php on line 385`. Shorthand form of ternary operator `?:` is available starting from PHP v5.3

= 1.1 =
* First release.