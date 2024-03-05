<?php
/**
 * LostPassword class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\WC;

use PROCAPTCHA\Abstracts\LostPasswordBase;

/**
 * Class LostPassword
 *
 * This class uses verify hook in WP\LostPassword.
 */
class LostPassword extends LostPasswordBase {
	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_wc_lost_password';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_wc_lost_password_nonce';

	/**
	 * Add procaptcha action.
	 */
	const ADD_CAPTCHA_ACTION = 'woocommerce_lostpassword_form';

	/**
	 * $_POST key to check.
	 */
	const POST_KEY = 'wc_reset_password';

	/**
	 * $_POST value to check.
	 */
	const POST_VALUE = 'true';
}
