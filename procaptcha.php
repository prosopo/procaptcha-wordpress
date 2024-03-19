<?php
/**
 * Plugin prosopoCaptcha
 *
 * @package              procaptcha-wp
 * @author               proCaptcha
 * @license              GPL-2.0-or-later
 * @wordpress-plugin
 *
 * Plugin Name:          ProCaptcha for WordPress
 * Plugin URI:           https://www.prosopo.io/
 * Description:          Say goodbye to intrusive, centralised CAPTCHA solutions. Prosopo CAPTCHA offers robust bot protection without compromising user privacy.
 * Version:              1.0.0
 * Requires at least:    5.0
 * Requires PHP:         7.0
 * Author:               proCaptcha
 * Author URI:           https://www.prosopo.io/
 *
 * WC requires at least: 3.0
 * WC tested up to:      8.2
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpParamsInspection */

use PROCaptcha\Main;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	// @codeCoverageIgnoreStart
	exit;
	// @codeCoverageIgnoreEnd
}

/**
 * Plugin version.
 */
const PROCAPTCHA_VERSION = '1.0.0';

/**
 * Path to the plugin dir.
 */
const PROCAPTCHA_PATH = __DIR__;

/**
 * Path to the plugin dir.
 */
const PROCAPTCHA_INC = PROCAPTCHA_PATH . '/src/php/includes';

/**
 * Plugin dir url.
 */
define( 'PROCAPTCHA_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Main plugin file.
 */
const PROCAPTCHA_FILE = __FILE__;

/**
 * Default nonce action.
 */
const PROCAPTCHA_ACTION = 'procaptcha_action';

/**
 * Default nonce name.
 */
const PROCAPTCHA_NONCE = 'procaptcha_nonce';


require PROCAPTCHA_INC . '/request.php';
require PROCAPTCHA_INC . '/functions.php';


$vendorDir = dirname(PROCAPTCHA_PATH);
$baseDir = PROCAPTCHA_PATH;

$allfiles = [
    $baseDir . '/src/php/ACFE/Form.php',
    $baseDir . '/src/php/Abstracts/LoginBase.php',
    $baseDir . '/src/php/Abstracts/LostPasswordBase.php',
    $baseDir . '/src/php/Settings/Abstracts/SettingsBase.php',
    $baseDir . '/src/php/Settings/Abstracts/SettingsInterface.php',
    $baseDir . '/src/php/Admin/Notifications.php',
    $baseDir . '/src/php/Asgaros/Base.php',
    $baseDir . '/src/php/Asgaros/Form.php',
    $baseDir . '/src/php/AutoVerify/AutoVerify.php',
    $baseDir . '/src/php/Avada/Form.php',
    $baseDir . '/src/php/BBPress/Base.php',
    $baseDir . '/src/php/BBPress/NewTopic.php',
    $baseDir . '/src/php/BBPress/Reply.php',
    $baseDir . '/src/php/BackInStockNotifier/Form.php',
    $baseDir . '/src/php/BeaverBuilder/Base.php',
    $baseDir . '/src/php/BeaverBuilder/Contact.php',
    $baseDir . '/src/php/BeaverBuilder/Login.php',
    $baseDir . '/src/php/Brizy/Base.php',
    $baseDir . '/src/php/Brizy/Form.php',
    $baseDir . '/src/php/BuddyPress/CreateGroup.php',
    $baseDir . '/src/php/BuddyPress/Register.php',
    $baseDir . '/src/php/CF7/CF7.php', 
    $baseDir . '/src/php/ClassifiedListing/Contact.php',
    $baseDir . '/src/php/ClassifiedListing/Login.php',
    $baseDir . '/src/php/ClassifiedListing/LostPassword.php',
    $baseDir . '/src/php/ClassifiedListing/Register.php',
    $baseDir . '/src/php/ColorlibCustomizer/Base.php',
    $baseDir . '/src/php/ColorlibCustomizer/Login.php',
    $baseDir . '/src/php/ColorlibCustomizer/LostPassword.php',
    $baseDir . '/src/php/ColorlibCustomizer/Register.php',
    $baseDir . '/src/php/DelayedScript/DelayedScript.php',
    $baseDir . '/src/php/Divi/Comment.php',
    $baseDir . '/src/php/Divi/Contact.php',
    $baseDir . '/src/php/Divi/EmailOptin.php',
    $baseDir . '/src/php/Divi/Fix.php',
    $baseDir . '/src/php/Divi/Login.php',
    $baseDir . '/src/php/DownloadManager/DownloadManager.php',
    $baseDir . '/src/php/EasyDigitalDownloads/Checkout.php',
    $baseDir . '/src/php/EasyDigitalDownloads/Login.php',
    $baseDir . '/src/php/EasyDigitalDownloads/LostPassword.php',
    $baseDir . '/src/php/EasyDigitalDownloads/Register.php',
    $baseDir . '/src/php/ElementorPro/PCaptchaHandler.php',
    $baseDir . '/src/php/FluentForm/Form.php',
    $baseDir . '/src/php/FormidableForms/Form.php',
    $baseDir . '/src/php/Forminator/Form.php',
    $baseDir . '/src/php/GiveWP/Base.php',
    $baseDir . '/src/php/GiveWP/Form.php',
    $baseDir . '/src/php/HTMLForms/Form.php',
    $baseDir . '/src/php/Helpers/ProCaptcha.php',
    $baseDir . '/src/php/Helpers/Request.php',
    $baseDir . '/src/php/Jetpack/JetpackBase.php',
    $baseDir . '/src/php/Jetpack/JetpackForm.php',
    $baseDir . '/src/php/Kadence/AdvancedBlockParser.php',
    $baseDir . '/src/php/Kadence/AdvancedForm.php',
    $baseDir . '/src/php/Kadence/Form.php',
    $baseDir . '/src/php/LearnDash/Login.php',
    $baseDir . '/src/php/LearnDash/LostPassword.php',
    $baseDir . '/src/php/LearnDash/Register.php',
    $baseDir . '/src/php/MailPoet/Form.php',
    $baseDir . '/src/php/Mailchimp/Form.php',
    $baseDir . '/src/php/Main.php',
    $baseDir . '/src/php/MemberPress/Login.php',
    $baseDir . '/src/php/MemberPress/Register.php',
    $baseDir . '/src/php/Migrations/Migrations.php',
    $baseDir . '/src/php/Otter/Form.php',
    $baseDir . '/src/php/PaidMembershipsPro/Checkout.php',
    $baseDir . '/src/php/PaidMembershipsPro/Login.php',
    $baseDir . '/src/php/Passster/Protect.php',
    $baseDir . '/src/php/ProfileBuilder/Login.php',
    $baseDir . '/src/php/ProfileBuilder/LostPassword.php',
    $baseDir . '/src/php/ProfileBuilder/Register.php',
    $baseDir . '/src/php/Quform/Quform.php',
    $baseDir . '/src/php/Sendinblue/Sendinblue.php',
    $baseDir . '/src/php/Settings/PluginSettingsBase.php',
    $baseDir . '/src/php/Settings/General.php',
    $baseDir . '/src/php/Settings/Integrations.php',
    $baseDir . '/src/php/Settings/Settings.php',
    $baseDir . '/src/php/Settings/SystemInfo.php',
    $baseDir . '/src/php/SimpleBasicContactForm/Form.php',
    $baseDir . '/src/php/SimpleDownloadMonitor/Form.php',
    $baseDir . '/src/php/Subscriber/Form.php',
    $baseDir . '/src/php/SupportCandy/Base.php',
    $baseDir . '/src/php/SupportCandy/Form.php',
    $baseDir . '/src/php/ThemeMyLogin/Login.php',
    $baseDir . '/src/php/ThemeMyLogin/LostPassword.php',
    $baseDir . '/src/php/ThemeMyLogin/Register.php',
    $baseDir . '/src/php/UM/Base.php',
    $baseDir . '/src/php/UM/Login.php',
    $baseDir . '/src/php/UM/LostPassword.php',
    $baseDir . '/src/php/UM/Register.php',
    $baseDir . '/src/php/UsersWP/Common.php',
    $baseDir . '/src/php/UsersWP/ForgotPassword.php',
    $baseDir . '/src/php/UsersWP/Login.php',
    $baseDir . '/src/php/UsersWP/Register.php',
    $baseDir . '/src/php/WCWishlists/CreateList.php',
    $baseDir . '/src/php/WC/Checkout.php',
    $baseDir . '/src/php/WC/Login.php',
    $baseDir . '/src/php/WC/LostPassword.php',
    $baseDir . '/src/php/WC/OrderTracking.php',
    $baseDir . '/src/php/WC/Register.php',
    $baseDir . '/src/php/WPDiscuz/Base.php',
    $baseDir . '/src/php/WPDiscuz/Comment.php',
    $baseDir . '/src/php/WPDiscuz/Subscribe.php',
    $baseDir . '/src/php/WPForms/Form.php',
    $baseDir . '/src/php/WPForo/Base.php',
    $baseDir . '/src/php/WPForo/NewTopic.php',
    $baseDir . '/src/php/WPForo/Reply.php',
    $baseDir . '/src/php/WPJobOpenings/Form.php',
    $baseDir . '/src/php/WP/Comment.php',
    $baseDir . '/src/php/WP/Login.php',
    $baseDir . '/src/php/WP/LostPassword.php',
    $baseDir . '/src/php/WP/PasswordProtected.php',
    $baseDir . '/src/php/WP/Register.php',
    $baseDir . '/src/php/Wordfence/General.php',
    $baseDir . '/src/php/NF/Field.php',
    $baseDir . '/src/php/NF/NF.php',
    $baseDir . '/src/php/GravityForms/Base.php',
    $baseDir . '/src/php/GravityForms/Form.php',
  //  $baseDir . '/src/php/GravityForms/Field.php',

];





foreach($allfiles as $file){
    require $file;
}




/**
 * Get procaptcha Main class instance.
 *
 * @return Main
 */
function procaptcha(): Main {
	static $procaptcha;

	if ( ! $procaptcha ) {
		// @codeCoverageIgnoreStart
		$procaptcha = new Main();
		// @codeCoverageIgnoreEnd
	}

	return $procaptcha;
}

procaptcha()->init();

