<?php
/**
 * Register class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\BuddyPress;

use Procaptcha\Helpers\Procaptcha;

/**
 * Class Register.
 */
class Register {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_bp_register';

	/**
	 * Nonce name.
	 */
	const NAME = 'procaptcha_bp_register_nonce';

	/**
	 * Register constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'bp_before_registration_submit_buttons', [ $this, 'add_captcha' ] );
		add_action( 'bp_signup_validate', [ $this, 'verify' ] );
	}

	/**
	 * Add captcha to the register form.
	 */
	public function add_captcha() {
		global $bp;

		if ( ! empty( $bp->signup->errors['procaptcha_response_verify'] ) ) {
			$output = '<div class="error">';

			$output .= $bp->signup->errors['procaptcha_response_verify'];
			$output .= '</div>';

			echo wp_kses_post( $output );
		}

		$args = [
			'action' => self::ACTION,
			'name'   => self::NAME,
			'id'     => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => 'register',
			],
		];

		Procaptcha::form_display( $args );
	}

	/**
	 * Verify register form captcha.
	 *
	 * @return bool
	 */
	public function verify(): bool {
		global $bp;

		$error_message = procaptcha_get_verify_message(
			self::NAME,
			self::ACTION
		);

		if ( null !== $error_message ) {
			$bp->signup->errors['procaptcha_response_verify'] = $error_message;

			return false;
		}

		return true;
	}
}
