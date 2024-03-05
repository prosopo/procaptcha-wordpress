<?php
/**
 * Register class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\MemberPress;

use PROCAPTCHA\Helpers\PROCAPTCHA;

/**
 * Class Register
 */
class Register {
	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_memberpress_register';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_memberpress_register_nonce';

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
		add_action( 'mepr-checkout-before-submit', [ $this, 'add_captcha' ] );
		add_filter( 'mepr-validate-signup', [ $this, 'verify' ] );
	}

	/**
	 * Add procaptcha to the Register form.
	 *
	 * @return void
	 */
	public function add_captcha() {
		$args = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'id'     => [
				'source'  => PROCAPTCHA::get_class_source( __CLASS__ ),
				'form_id' => 'register',
			],
		];

		PROCAPTCHA::form_display( $args );
	}

	/**
	 * Verify procaptcha.
	 *
	 * @param array|mixed $errors Errors.
	 *
	 * @return array|mixed
	 */
	public function verify( $errors ) {
		$error_message = procaptcha_get_verify_message(
			self::NONCE,
			self::ACTION
		);

		if ( null !== $error_message ) {
			$errors[] = $error_message;
		}

		return $errors;
	}
}
