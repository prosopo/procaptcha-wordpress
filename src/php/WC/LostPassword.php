<?php
/**
 * LostPassword class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\WC;

use Procaptcha\Abstracts\LostPasswordBase;
use Procaptcha\Helpers\Procaptcha;

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
	 * @return void
	 * @noinspection CssUnusedSymbol
	 */
	public function print_inline_styles() {
		$css = <<<CSS
	.woocommerce-ResetPassword .procaptcha {
		margin-top: 0.5rem;
	}
CSS;

		Procaptcha::css_display( $css );
	}
}
