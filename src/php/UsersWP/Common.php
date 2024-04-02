<?php
/**
 * Common class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\UsersWP;

/**
 * Common methods for UsersWP classes.
 */
class Common {

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {
		$min = procap_min_suffix();

		wp_enqueue_script(
			'procaptcha-users-wp',
			PROCAPTCHA_URL . "/assets/js/procaptcha-users-wp$min.js",
			[ 'jquery', 'procaptcha' ],
			PROCAPTCHA_VERSION,
			true
		);
	}
}
