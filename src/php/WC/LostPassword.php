<?php
/**
 * LostPassword class file.
 *
 * @package hcaptcha-wp
 */

namespace HCaptcha\WC;

use HCaptcha\Abstracts\LostPasswordBase;
use HCaptcha\Helpers\HCaptcha;

/**
 * Class LostPassword
 *
 * This class uses verify hook in WP\LostPassword.
 */
class LostPassword extends LostPasswordBase {

	/**
	 * Nonce action.
	 */
	const ACTION = 'hcaptcha_wc_lost_password';

	/**
	 * Nonce name.
	 */
	const NONCE = 'hcaptcha_wc_lost_password_nonce';

	/**
	 * Add hCaptcha action.
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

	/**
	 * Init hooks.
	 */
	protected function init_hooks() {
		parent::init_hooks();

		add_action( 'wp_head', [ $this, 'print_inline_styles' ], 20 );
	}

	/**
	 * Print inline styles.
	 *
	 * @return       void
	 * @noinspection CssUnusedSymbol
	 */
	public function print_inline_styles() {
		$css = <<<CSS
	.woocommerce-ResetPassword .procaptcha {
		margin-top: 0.5rem;
	}
CSS;

		HCaptcha::css_display( $css );
	}
}
