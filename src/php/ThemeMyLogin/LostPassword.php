<?php
/**
 * LostPassword class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\ThemeMyLogin;

use PROCAPTCHA\Abstracts\LostPasswordBase;

/**
 * Class LostPassword
 */
class LostPassword extends LostPasswordBase {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_theme_my_login_lost_password';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_theme_my_login_lost_password_nonce';

	/**
	 * Add procaptcha action.
	 */
	const ADD_CAPTCHA_ACTION = 'lostpassword_form';

	/**
	 * $_POST key to check.
	 */
	const POST_KEY = 'submit';

	/**
	 * $_POST value to check.
	 */
	const POST_VALUE = null;

	/**
	 * Add captcha.
	 *
	 * @return void
	 */
	public function add_captcha() {
		if ( ! did_action( 'tml_render_form' ) ) {
			return;
		}

		parent::add_captcha();
	}
}
