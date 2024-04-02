<?php
/**
 * Register class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\LearnDash;

use Procaptcha\Helpers\Procaptcha;
use WP_Error;

/**
 * Class Register
 */
class Register {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_theme_my_login_register';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_theme_my_login_register_nonce';

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
		add_action( 'learndash_registration_form', [ $this, 'add_captcha' ] );
		add_filter( 'registration_errors', [ $this, 'verify' ], 10, 3 );
		add_filter( 'learndash_registration_errors', [ $this, 'add_registration_errors' ] );
		add_action( 'wp_head', [ $this, 'print_inline_styles' ] );
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
	 * Verify register captcha.
	 *
	 * @param WP_Error|mixed $errors               A WP_Error object containing any errors encountered during
	 *                                             registration.
	 * @param string         $sanitized_user_login User's username after it has been sanitized.
	 * @param string         $user_email           User's email.
	 *
	 * @return WP_Error|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function verify( $errors, string $sanitized_user_login, string $user_email ) {
		// Nonce is checked in LearnDash.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['learndash-registration-form'] ) ) {
			return $errors;
		}

		$error_message = procaptcha_verify_post(
			self::NONCE,
			self::ACTION
		);

		return Procaptcha::add_error_message( $errors, $error_message );
	}

	/**
	 * Add registration errors.
	 *
	 * @param string[]|mixed $registration_errors An array of registration errors and descriptions.
	 *
	 * @return mixed
	 */
	public function add_registration_errors( $registration_errors ) {
		return array_merge( (array) $registration_errors, procaptchaget_error_messages() );
	}

	/**
	 * Print inline styles.
	 *
	 * @return void
	 * @noinspection CssUnusedSymbol
	 */
	public function print_inline_styles() {
		$css = <<<CSS
	#learndash_registerform .procaptcha {
		margin-bottom: 0;
	}
CSS;

		Procaptcha::css_display( $css );
	}
}
