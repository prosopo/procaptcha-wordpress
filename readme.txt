=== procap_ for WordPress ===
Contributors: procaptcha, kaggdesign
Tags: captcha, procaptcha, antispam, abuse, protect form
Requires at least: 5.1
Tested up to: 6.5
Requires PHP: 7.0.0
Stable tag: 3.10.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enables procap_ integration with WordPress and popular plugins.

== Description ==

[procap_](https://www.procaptcha.io/) is a drop-in replacement for reCAPTCHA that puts user privacy first.

Need to keep out bots? procap_ protects privacy while offering better protection against spam and abuse. Help build a better web.

== How procap_ Works ==

The purpose of a CAPTCHA is to distinguish between people and machines via a challenge-response test, and thus increase the cost of spamming or otherwise abusing websites by keeping out bots.

To use this plugin, install it and enter your sitekey and secret in the Settings -> procap_ menu after signing up on procap_.com.

[procap_ Free](https://www.procaptcha.io/) lets websites block bots and other forms of abuse via humanity challenges.

[procap_ Pro](https://www.procaptcha.io/pro) goes beyond the free procap_ service with advanced machine learning to reduce the challenge rate, delivering high security and low friction along with more features like UI customization.

== Screenshots ==

1. Login page with procap_ widget
2. Login page with procap_ challenge
3. WooCommerce Login/Register page
4. Contact From 7 with procap_
5. General settings page
6. Integrations settings page
7. Activating plugin from the Integration settings page

== Installation ==

Sign up at [procap_.com](https://www.procaptcha.io/) to get your sitekey and secret, then:

1. Install procap_ either via the WordPress.org plugin repository (best) or by uploading the files to your server. ([Upload instructions](https://www.wpbeginner.com/beginners-guide/step-by-step-guide-to-install-a-wordpress-plugin-for-beginners/))
2. Activate the procap_ plugin on the 'Plugins' admin page
3. Enter your site key and secret on the Settings->procap_->General page
4. Enable desired Integrations on the Settings->procap_->Integrations page

== Frequently Asked Questions ==

= How do I use the procap_ plugin? =

The procap_ plugin supports WordPress core and many plugins with forms automatically. You should select the supported forms on the procap_ Integrations settings page.

For non-standard cases, you can use the `[procaptcha]` shortcode provided by the plugin.

For example, we support Contact Forms 7 automatically. However, sometimes a theme can modify the form. In this case, you can manually add the `[cf7-procaptcha]` shortcode to the CF7 form.

To make procap_ work, the shortcode must be inside the <form ...> ... </form> tag.

= You don't support plugin X. How can I get support for it added? =

[Open a PR on GitHub](https://github.com/procap_/procaptcha-wordpress-plugin): or just email the authors of plugin X. Adding procap_ support is typically quite a quick task for most plugins.

= Does the [procaptcha] shortcode have arguments? =

Full list of arguments:

`
[procaptcha action="my_procap_action" name="my_procap_name" auto="true|false" force="true|false" size="normal|compact|invisible"]
`

The shortcode adds not only the procap_ div to the form, but also a nonce field. You can set your own nonce action and name. For this, use arguments in the shortcode:

`
[procaptcha action="my_procap_action" name="my_procap_name"]
`

and in the verification:

`
$result = procaptcha_request_verify( 'my_procap_action', 'my_procap_name' );
`

For the explanation of the auto="true|false" argument, see the section *"How to automatically verify an arbitrary form"*. By default, `auto="false"`.

The argument force="true|false" allows forcing verification of procap_ widget before submitting the form. By default, `force="false"`.

The argument size="normal|compact|invisible" allows setting the size of procap_ widget. By default, `size="normal"`.

= How to add procap_ to an arbitrary form =

First, add the procap_ snippet to the form.

If you create the form as an HTML block in the post content, insert the shortcode `[procaptcha]` inside it. It may look like this:

`
<form method="post">
	<input type="text" name="test_input">
	<input type="submit" value="Send">
    [procaptcha]
</form>
`

If you create the form programmatically, insert the following statement inside it:

`
?>
<form method="post">
	<input type="text" name="test_input">
	<input type="submit" value="Send">
    <?php echo do_shortcode( '[procaptcha]' ); ?>
</form>
<?php
`

Secondly, verify the result of procap_ challenge.

`
$result = procaptcha_verify_post();

if ( null !== $result ) {
    echo esc_html( $result );
    // Block processing of the form.
}
`

= How to automatically verify an arbitrary form =

Arbitrary user forms can be verified easily. Just add `auto="true"` or `auto="1"` to the shortcode:

`
[procaptcha auto="true"]
`

and insert this shortcode into your form.

Auto-verification works with forms sent by POST on frontend only. It works with forms in the post content and in widgets.

You can add also `force="true"` or `force="1"` argument to prevent sending a form without checking the procap_.

`
[procaptcha auto="true" force="true"]
`

= How to block procap_ on a specific page? =

procap_ starts early, so you cannot use standard WP functions to determine the page. For instance, to block it on `my-account` page, add the following code to your plugin's (or mu-plugin's) main file. This code won't work being added to a theme's functions.php file.

`
/**
* Filter procap_ activation flag.
*
* @param bool $activate Activate flag.
*
* @return bool
*/
function my_procap_activate( $activate ) {
  $url = isset( $_SERVER['REQUEST_URI'] ) ?
  filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ) :
  '';

  if ( '/my-account/' === $url ) {
    return false;
  }

  return $activate;
}

add_filter( 'procap_activate', 'my_procap_activate' );
`

= Skipping procap_ verification on a specific form =

The plugin has a filter to skip adding and verifying procap_ on a specific form. The filter receives three parameters: current protection status ('true' by default), source and form_id.

The source is the plugin's slug (like 'directory/main-plugin-file.php'), the theme name (like 'Avada') or the WordPress core (like 'WordPress').

The form_id is the form_id for plugins like Gravity Forms or WPForms, the post id for comments or a general name of the form when the form does not have an id (like WordPress core login form).

Filter arguments for some plugins/forms are listed below.

Affiliates
`$source: 'affiliates/affiliates.php'`
`$form_id: 'login' or 'register'`

Back In Stock Notifier
`$source: 'back-in-stock-notifier-for-woocommerce/cwginstocknotifier.php'`
`$form_id: product_id`

BBPress
`$source: 'bbpress/bbpress.php'`
`$form_id: 'new_topic' or 'reply'`

Beaver Builder
`$source: 'bb-plugin/fl-builder.php'`
`$form_id: 'contact' or 'login'`

Brizy
`$source: 'brizy/brizy.php'`
`$form_id: 'form'`

BuddyPress
`$source: 'buddypress/bp-loader.php'`
`$form_id: 'create_group' or 'register'`

Classified Listing
`$source: 'classified-listing/classified-listing.php'`
`$form_id: 'contact', 'login', 'lost_password' or 'register'`

Divi
`$source: 'Divi'`
`$form_id: post_id for comment form, 'contact', 'email_optin', or 'login'`

Download Manager
`$source: 'download-manager/download-manager.php'`
`$form_id: post_id of download item in the admin`

Easy Digital Downloads
`$source: 'easy-digital-downloads/easy-digital-downloads.php'`
`$form_id: 'checkout', 'login', 'lost_password' or 'register'`

Elementor Pro
`$source: 'elementor-pro/elementor-pro.php'`
`$form_id: Form ID set for the form Content->Additional Options or 'login'`

Jetpack
`$source: 'jetpack/jetpack.php'`
`$form_id: 'contact'`

Kadence Form
`$source: 'kadence-blocks/kadence-blocks.php'`
`$form_id: post_id`

Kadence Advanced Form
`$source: 'kadence-blocks/kadence-blocks.php'`
`$form_id: form_id`

LearnDash
`$source: 'sfwd-lms/sfwd_lms.php'`
`$form_id: 'login', 'lost_password' or 'register'`

Login/Signup Popup
`$source: 'easy-login-woocommerce/xoo-el-main.php'`
`$form_id: 'login', or 'register'`

MemberPress
`$source: 'memberpress/memberpress.php'`
`$form_id: 'login' or 'register'`

Paid Memberships Pro
`$source: 'paid-memberships-pro/paid-memberships-pro.php'`
`$form_id: 'checkout' or 'login'`

Passster
`$source: 'content-protector/content-protector.php'`
`$form_id: area_id`

Profile Builder
`$source: 'profile-builder/index.php'`
`$form_id: 'login', 'lost_password' or 'register'`

Subscriber
`$source: 'subscriber/subscriber.php'`
`$form_id: 'form'`

Support Candy
`$source: 'supportcandy/supportcandy.php'`
`$form_id: 'form'`

Theme My Login
`$source: 'theme-my-login/theme-my-login.php'`
`$form_id: 'login', 'lost_password' or 'register'`

Ultimate Member
`$source: 'ultimate-member/ultimate-member.php'`
`$form_id: form_id or 'password'`

UsersWP
`$source: 'userswp/userswp.php'`
`$form_id: 'forgot', 'login' or 'register'`

WooCommerce Wishlist
`$source: 'woocommerce-wishlists/woocommerce-wishlists.php'`
`$form_id: 'form'`

wpDiscuz
`$source: 'wpdiscuz/class.WpdiscuzCore.php'`
`$form_id: post_id`

WPForms
`$source: 'wpforms-lite/wpforms.php' or 'wpforms/wpforms.php'`
`$form_id: form_id`

wpForo
`$source: 'wpforo/wpforo.php'`
`$form_id: 'new_topic' for new topic form and topicid for reply form. Topicid can be found in HTML code searching for 'data-topicid' in Elements.`

Wordfence Login Security
`$source: 'wordfence-login-security/wordfence-login-security.php'`
`$form_id: 'login'`

Wordfence Security
`$source: 'wordfence/wordfence.php'`
`$form_id: 'login'`

WordPress Core
`$source: 'WordPress'`
`$form_id: post_id for comment form, 'login', 'lost_password', 'password_protected', or 'register'`

WooCommerce
`$source: 'woocommerce/woocommerce.php'`
`$form_id: 'checkout', 'login', 'lost_password', 'order_tracking', or 'register'`

Below is an example of how to skip the procap_ widget on a Gravity Form with id = 1.

`
/**
 * Filters the protection status of a form.
 *
 * @param string     $value   The protection status of a form.
 * @param string[]   $source  Plugin(s) serving the form.
 * @param int|string $form_id Form id.
 *
 * @return bool
 */
function procap_protect_form_filter( $value, $source, $form_id ) {
	if ( ! in_array( 'gravityforms/gravityforms.php', $source, true ) ) {
		// The form is not sourced by Gravity Forms plugin.
		return $value;
	}

	if ( 1 !== (int) $form_id ) {
		// The form has id !== 1.
		return $value;
	}

	// Turn off protection for Gravity form with id = 1.
	return false;
}

add_filter( 'procap_protect_form', 'procap_protect_form_filter', 10, 3 );
`

= How to show procap_ widget instantly? =

The plugin loads the procap_ script with a delay until user interaction: mouseenter, click, scroll or touch. This significantly improves Google Pagespeed Insights score.

To load the procap_ widget instantly, you can use the following filter:

`
/**
* Filters delay time for procap_ API script.
*
* Any negative value will prevent API script from loading at all,
* until user interaction: mouseenter, click, scroll or touch.
* This significantly improves Google Pagespeed Insights score.
*
* @param int $delay Number of milliseconds to delay procap_ API script.
*                   Any negative value means delay until user interaction.
*/
function my_procap_delay_api( $delay ) {
  return 0;
}

add_filter( 'procap_delay_api', 'my_procap_delay_api' );
`

= How to set procap_ language programmatically? =

procap_ defaults to using the user's language as reported by the browser. However, on multilingual sites you can override this to set the procap_ language to match the current page language. For this, you can use the following filter:

`
/**
* Filters procap_ language.
*
* @param string $language Language.
*/
function my_procap_language( $language ) {
  // Detect page language and return it.
  $page_language = 'some lang'; // Detection depends on the multilingual plugin used.

  return $page_language;
}

add_filter( 'procap_language', 'my_procap_language' );
`

= How to whitelist certain IPs =

You can use the following filter:

`
/**
 * Filter user IP to check if it is whitelisted.
 * For whitelisted IPs, procap_ will not be shown.
 *
 * @param bool   $whitelisted Whether IP is whitelisted.
 * @param string $ip          IP.
 *
 * @return bool
 */
function my_procap_whitelist_ip( $whitelisted, $ip ) {

  // Whitelist local IPs.
  if ( false === $ip ) {
    return true;
  }

  // Whitelist some other IPs.
  if ( '1.1.1.1' === $ip ) {
    return true;
  }

  return $whitelisted;
}

add_filter( 'procap_whitelist_ip', 'my_procap_whitelist_ip', 10, 2 );
`

= Why isn't my WPForms Lite installation working? =

Please make sure you have removed the reCAPTCHA keys under WPForms > Settings > reCAPTCHA to avoid a conflict.

= Where can I get more information about procap_? =

Please see our [website](https://procaptcha.io/).

== Privacy Notices ==

procap_ is designed to comply with privacy laws in every country, including GDPR, LGPD, CCPA, and more.

For example, procap_ has been certified under ISO 27001 and 27701 and is enrolled in the EU-US, UK-US, and Swiss-US Data Privacy Framework for GDPR compliance.

Details are available at [www.procaptcha.io/certifications](https://www.procaptcha.io/certifications) and [www.procaptcha.io/gdpr](https://www.procaptcha.io/gdpr).

With the default configuration, this plugin does not:

* track users by stealth;
* write any user's personal data to the database;
* send any data to external servers;
* use cookies.

Once you activate this plugin, the procap_-answering user's IP address and browser data may be sent to the procap_ service on pages where you have activated procap_ protection. However, procap_ is designed to minimize data used, process it very close to the user, and rapidly discard it after analysis.

For more details, please see the procap_ privacy policy at:

* [procap_.com](https://procap_.com/privacy)

=== Forms and Plugins Supported ==

* Login Form
* Register Form
* Lost Password Form
* Comment Form
* Post/Page Password Form
* ACF Extended Form
* Affiliates Login Form
* Affiliates Register Form
* Asgaros Forum New Topic Form
* Asgaros Forum Reply Form
* Avada Form
* Back In Stock Notifier
* bbPress New Topic Form
* bbPress Reply Form
* Beaver Builder Contact Form
* Beaver Builder Login Form
* BuddyPress Create Group Form
* Buddypress Registration Form
* Classified Listing Contact Form
* Classified Listing Login Form
* Classified Listing Lost Password Form
* Classified Listing Register Form
* CoBlocks Form
* Colorlib Customizer Login Form
* Colorlib Customizer Lost Password Form
* Colorlib Customizer Register Form
* Contact Form 7
* Divi Comment Form
* Divi Contact Form
* Divi Email Optin Form
* Divi Login Form
* Download Manager Button
* Droit Dark Mode
* Easy Digital Downloads Checkout Form
* Easy Digital Downloads Login Form
* Easy Digital Downloads Lost Password Form
* Easy Digital Downloads Register Form
* Elementor Pro Form
* Elementor Pro Login
* Fluent Forms
* Forminator
* Formidable Forms
* GiveWP Form
* Gravity Forms
* Gravity Perks Nested Forms
* Jetpack Forms
* Kadence Form
* Kadence Advanced Form
* LearnDash Login Form
* LearnDash Lost Password Form
* LearnDash Register Form
* Login/Signup Popup Login Form
* Login/Signup Popup Register Form
* Mailchimp for WP Form
* MailPoet Form
* MemberPress Login Form
* MemberPress Register Form
* Ninja Forms
* Otter Blocks Forms
* Paid Memberships Pro Checkout Form
* Paid Memberships Pro Login Form
* Passster Protection Form
* Profile Builder Login Form
* Profile Builder Recover Password Form
* Profile Builder Register Form
* Quform Forms
* Sendinblue Form
* Simple Download Monitor Form
* Simple Basic Contact Form
* Spectra — WordPress Gutenberg Blocks Form
* Subscriber Form
* Support Candy New Ticket Form
* Theme My Login: Login Form
* Theme My Login: Lost Password Form
* Theme My Login: Register Form
* Ultimate Member Login Form
* Ultimate Member Lost Password Form
* Ultimate Member Register Form
* UsersWP Forgot Password Form
* UsersWP Login Form
* UsersWP Register Form
* WooCommerce Login Form
* WooCommerce Registration Form
* WooCommerce Lost Password Form
* WooCommerce Checkout Form
* WooCommerce Order Tracking Form
* WooCommerce Wishlist
* Wordfence Security Login Form
* Wordfence Login Security Login Form
* WP Dark Mode
* WP Job Openings Form
* WPForms Form
* wpDiscuz Comment Form
* wpDiscuz Support Form
* wpForo New Topic Form
* wpForo Reply Form

=== Please note ===

**NOTE:** This is a community-developed plugin. Your PRs are welcome.

For feature requests and issue reports, please
[open a pull request](https://github.com/procap_/procaptcha-wordpress-plugin).

We also suggest emailing the authors of plugins you'd like to support procap_: it will usually take them only an hour or two to add native support. This will simplify your use of procap_, and is the best solution in the long run.

Some plugins listed have been superseded by native support, and are included only for legacy purposes.

You should always use native procap_ support if available for your plugin.
Please check with your plugin author if native support is not yet available.

Instructions for popular native integrations are below:

* [WPForms native integration: instructions to enable procap_](https://wpforms.com/docs/how-to-set-up-and-use-procaptcha-in-wpforms)

== Changelog ==

= 3.10.1 =
* Added filter `procap_add_csp_headers` to allow adding Content Security Policy headers.
* Fixed Content Security Policy headers processing.

= 3.10.0 =
* Tested with WordPress 6.5.
* Tested with WooCommerce 8.6.
* The minimum required WordPress version is now 5.1.
* Added Force procap_ check before submit feature.
* Added Elementor Pro Login integration.
* Added Login/Signup Popup integration.
* Added CoBlocks integration.
* Added Enterprise parameters to the System Info page.
* Added checking of Enterprise parameters before saving.
* Improved translation on Settings pages.
* Improved error reporting for Active procap_ on the General page.
* Fixed procap_ error codes table.
* Fixed Settings pages layout with Chrome 122.
* Fixed Content Security Policy headers.
* Fixed fatal error with Formidable Forms 6.8.2.

= 3.9.0 =
* Added Spectra — WordPress Gutenberg Blocks integration.
* Added Akismet integration.
* Added test of procap_ completion before checking the site config.
* Added site config check upon changing Enterprise params.
* Added auto verify feature for forms in widgets.
* Fixed site config check upon changing site and secret keys.
* Fixed the list of themes after activation on the Integrations page.
* Fixed jumping WooCommerce checkout page to procap_ on a page load.
* Fixed missing procap_ on the Divi Comment Form.

= 3.8.1 =
* Fixed activation and deactivation of plugin and themes on the Integrations page.

= 3.8.0 =
* Added search of plugin and themes on the Integrations page.
* Added toggling of sections on the General page.
* Added new dialog on activation and deactivation of plugin and themes.
* Added selection of a new theme on deactivation of the current one.
* Added 'backend' to optional Enterprise settings.
* Added filter `procap_api_host`, allowing to filter the API host.
* Added filter `procap_api_src`, allowing to filter the API source url with params.
* Updated integration with Back In Stock Notifier.
* Fixed Brevo (formerly Sendinblue) plugin position on Integrations page.
* Fixed testing config with test accounts.
* Fixed saving Notification state.
* Fixed compatibility of Ninja Forms with GeoDirectory.
* Fixed compatibility of Beaver Builder with GeoDirectory.
* Fixed compatibility of Divi with GeoDirectory.
* Fixed compatibility of MailPoet with GeoDirectory.
* Fixed compatibility of Passster with GeoDirectory.
* Fixed styles of Settings pages on mobile.

= 3.7.1 =
* Fixed adding arguments to api.js for Enterprise accounts.

= 3.7.0 =
* Tested with WooCommerce 8.5.
* Added optional Enterprise settings.
* Fixed improper display of the "rate plugin" message on options.php.
* Fixed colored border of procap_ challenge arrow.

= 3.6.0 =
* Tested with WooCommerce 8.4.
* Added compatibility with BuddyPress 12.0.
* Added procap_ tag to Contact Form 7 Admin Editor.
* Added support for WPForms embedded forms.
* Added Affiliates Login Form integration.
* Added Affiliates Register Form integration.
* Improved login forms security.
* Improved inline scripts to optimize page load time.
* Improved Integrations settings page - the Save Changes button moved up for better user experience.
* Fixed procap_ position in BuddyPress.
* Fixed procap_ position in wpDiscuz.
* Fixed fatal error in Brizy integration.
* Fixed auto-detection of procap_ language.
* Fixed and added some translations.

= 3.5.0 =
* Tested with PHP 8.3.
* Tested with WooCommerce 8.3.
* Added procap_ field to Gravity Forms admin editor.
* Added procap_ field to Ninja Forms admin editor.
* Added invisible procap_ support for Ninja Forms.
* Added the ability to process customized MailChimp forms.
* Added HTML Forms integration.
* Added the Auto Theme option to follow light/dark theme settings on site.
* Added support for WP Twenty Twenty-One theme dark mode.
* Added support for WP Dark Mode plugin.
* Added support for Droit Dark Mode plugin.
* Added ability to activate/deactivate themes from the Integrations settings page.
* Fixed loading of local .mo files.
* Fixed inability to send Divi Contact Form.
* Fixed MailPoet issues in admin.

= 3.4.1 =
* Tested with WordPress 6.4.
* Tested with WooCommerce 8.2.
* Added MailPoet integration.
* Added Simple Download Monitor integration.
* Added WP Job Openings integration.
* Added Simple Basic Contact Form integration.
* Added Easy Digital Downloads Login Form integration.
* Added Easy Digital Downloads Lost Password Form integration.
* Added Easy Digital Downloads Register Form integration.
* Added purging of old failed login data to keep the `procaptcha_login_data` option size small.
* Fixed compatibility with HPOS in WooCommerce.
* Fixed fatal error caused by broken backward compatibility in the Ultimate Member 2.7.0.
* Fixed SystemInfo on multisite.
* Fixed the missing dependency of WooCommerce checkout script.
* Fixed fatal error occurred during login under some conditions.
* Fixed the inability to send the Divi Contact Form when Divi Email Optin was active.

= 3.3.3 =
* Added compatibility with LearnDash.
* Added requirement to check the site config after changes in credentials.
* Added filter `procap_login_limit_exceeded`, allowing to filter the login limit exceeded status.
* Changed Brevo (formerly Sendinblue) logo.
* Fixed activation of procap_ with empty keys.
* Fixed autocomplete of the Site Key field by LastPass.
* Fixed form detection for Auto-Verify.
* Fixed Brevo form working in the post content only.
* Fixed procap_ not loading correctly for a Brevo form.
* Fixed Passster form working in the post content only.
* Fixed LearnDash form working in the post content only.
* Fixed auto-verify form not working on the homepage.

= 3.3.2 =
* Improved Beaver Builder login sequence.
* Improved Classified Listing login sequence.
* Improved Divi login sequence.
* Improved MemberPress login sequence.
* Improved Paid Membership Pro login sequence.
* Improved Profile Builder login sequence.
* Improved Ultimate Member login sequence.
* Improved Wordfence login sequence.
* Improved native WordPress login sequence.
* Fixed login error when WP Login form option was `'on'` and WC Login form option was `'off'`.
* Fixed compatibility with WPS Hide Login.
* Fixed compatibility with All-In-One Security.
* Fixed compatibility with Rename wp-admin Login.

= 3.3.0 =
* Color scheme in admin UI has been updated.
* Added compatibility with Passster.
* Added compatibility with Theme My Login.
* Added compatibility with Gravity Perks Nested Forms.
* Added compatibility with Wordfence Login Security.
* Added compatibility with Wordfence Security.
* Added compatibility with UsersWP.
* Added compatibility with Kadence Advanced Form.
* Improved support for a Kadence simple form.
* Replaced deprecated ajaxStop events.
* Fixed error on a Classified Listing Login form.
* Fixed admin page title.

= 3.2.0 =
* Tested with WooCommerce 8.0.
* Added ability to use procap_ field provided by the Fluent Forms plugin.
* Added ability to use procap_ field provided by the Forminator plugin.
* Added ability to use procap_ field provided by the Quform plugin.
* Added procap_ reset to allow sending an Elementor form several times without reloading the page.
* Added procap_ reset to allow sending a Forminator form several times without reloading the page.
* Added procap_ reset to allow sending a Quform form several times without reloading the page.
* Blocked procap_ settings on Fluent Forms admin pages with a notice having a link to the procap_ plugin General settings page.
* Blocked procap_ settings on Forminator admin pages with a notice having a link to the procap_ plugin General settings page.
* Blocked procap_ settings on Quform admin pages with a notice having a link to the procap_ plugin General settings page.
* Fixed Fluent Forms submit error.
* Fixed positioning of procap_ in Fluent Form.
* Fixed deprecation errors in debug.log that occurred with Fluent Forms.
* Fixed Forminator form display error.
* Fixed dynamic display of settings in sample procap_.

= 3.1.0 =
* Added notification system.
* Fixed mode selection for sample procap_ on the General settings page.

= 3.0.1 =
* Fixed error on Contact Form 7 validation.
* Fixed checkboxes disabled status after activation of a plugin on the Integrations page.

= 3.0.0 =
* Dropped support for PHP 5.6. The minimum required PHP version is now 7.0.
* Tested with WordPress 6.3.
* Tested with WooCommerce 7.9.
* Added procap_ config check to the General settings page.
* Added dynamic display of settings in sample procap_.
* Added compatibility with Ajax Gravity Forms.
* Added compatibility with Profile Builder.
* Added compatibility with an Easy Digital Downloads Checkout form.

[See changelog for all versions](https://plugins.svn.wordpress.org/procaptcha-wordpress/trunk/changelog.txt).
