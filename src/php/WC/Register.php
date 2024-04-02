<?php
/**
 * Register class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\WC;

use Procaptcha\Helpers\Procaptcha;
use WP_Error;

/**
 * Class Register
 */
class Register {
	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_wc_register';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_wc_register_nonce';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 */
	private function init_hooks() {
		add_action( 'woocommerce_register_form', [ $this, 'add_captcha' ] );
		add_filter( 'woocommerce_process_registration_errors', [ $this, 'verify' ] );
		add_action( 'wp_head', [ $this, 'print_inline_styles' ], 20 );
	}

	/**
	 * Add captcha.
	 *
	 * @return void
	 */
	public function add_captcha() {
		$args = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'id'     => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => 'register',
			],
		];

		Procaptcha::form_display( $args );
	}

	/**
	 * Verify register form.
	 *
	 * @param WP_Error|mixed $validation_error Validation error.
	 *
	 * @return WP_Error|mixed
	 */
	public function verify( $validation_error ) {
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
	.woocommerce-form-register .procaptcha {
		margin-top: 2rem;
	}
CSS;

		Procaptcha::css_display( $css );
	}
}
