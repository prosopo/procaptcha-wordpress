<?php
/**
 * Login class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\WC;

use Procaptcha\Abstracts\LoginBase;
use Procaptcha\Helpers\Procaptcha;
use WP_Error;

/**
 * Class Login
 */
class Login extends LoginBase {

	/**
	 * Init hooks.
	 */
	protected function init_hooks() {
		parent::init_hooks();

		add_action( 'woocommerce_login_form', [ $this, 'add_captcha' ] );
		add_filter( 'woocommerce_process_login_errors', [ $this, 'verify' ] );
		add_action( 'wp_head', [ $this, 'print_inline_styles' ], 20 );
	}

	/**
	 * Verify login form.
	 *
	 * @param WP_Error|mixed $validation_error Validation error.
	 *
	 * @return WP_Error|mixed
	 */
	public function verify( $validation_error ) {
		if ( ! doing_filter( 'woocommerce_process_login_errors' ) ) {
			return $validation_error;
		}

		if ( ! $this->is_login_limit_exceeded() ) {
			return $validation_error;
		}

		$error_message = procaptcha_get_verify_message(
			self::NONCE,
			self::ACTION
		);

		if ( null === $error_message ) {
			return $validation_error;
		}

		if ( ! is_wp_error( $validation_error ) ) {
			$validation_error = new WP_Error();
		}

		$validation_error->add( 'procaptcha_error', $error_message );

		return $validation_error;
	}

	/**
	 * Print inline styles.
	 *
	 * @return void
	 * @noinspection CssUnusedSymbol
	 */
	public function print_inline_styles() {
		$css = <<<CSS
	.woocommerce-form-login .procaptcha {
		margin-top: 2rem;
	}
CSS;

		Procaptcha::css_display( $css );
	}
}
