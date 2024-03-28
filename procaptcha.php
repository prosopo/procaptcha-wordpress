<?php
/**
 * Plugin prosopo/procaptcha-wordpress
 *
 * @package              procaptcha-wordpress
 * @author               prosopo
 * @license              GPL-2.0-or-later
 * @wordpress-plugin
 *
 * Plugin Name:          Procaptcha WordPress
 * Plugin URI:           https://www.prosopo.io/
 * Description:          Say goodbye to intrusive, centralised CAPTCHA solutions. Prosopo Procaptcha offers robust bot protection without compromising user privacy.
 * Version:              1.0.0
 * Requires at least:    5.0
 * Requires PHP:         7.0
 * Author:               Prosopo
 * Author URI:           https://www.prosopo.io/
 *
 * WC requires at least: 3.0
 * WC tested up to:      8.2
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpParamsInspection */

use PROCAPTCHA\Main;

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

