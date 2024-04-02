<?php
/**
 * Plugin procaptcha
 *
 * @package              procaptcha-wp
 * @author               procaptcha
 * @license              GPL-2.0-or-later
 * @wordpress-plugin
 *
 * Plugin Name:          procaptcha for WordPress
 * Plugin URI:           https://www.procaptcha.io/
 * Description:          procaptcha keeps out bots and spam while putting privacy first. It is a drop-in replacement for reCAPTCHA.
 * Version:              3.10.1
 * Requires at least:    5.1
 * Requires PHP:         7.0
 * Author:               procaptcha
 * Author URI:           https://www.procaptcha.io/
 * License:              GPL v2 or later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          procaptcha-wordpress
 * Domain Path:          /languages/
 *
 * WC requires at least: 3.0
 * WC tested up to:      8.6
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpParamsInspection */

use Procaptcha\Main;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	// @codeCoverageIgnoreStart
	exit;
	// @codeCoverageIgnoreEnd
}

/**
 * Plugin version.
 */
const PROCAPTCHA_VERSION = '3.10.1';

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

require_once PROCAPTCHA_PATH . '/vendor/autoload.php';

require PROCAPTCHA_INC . '/request.php';
require PROCAPTCHA_INC . '/functions.php';

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
