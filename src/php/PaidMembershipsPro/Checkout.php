<?php
/**
 * Checkout class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\PaidMembershipsPro;

use Procaptcha\Helpers\Procaptcha;

/**
 * Class Checkout.
 */
class Checkout {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_pmpro_checkout';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_pmpro_checkout_nonce';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 */
	protected function init_hooks() {
		add_action( 'pmpro_checkout_before_submit_button', [ $this, 'add_captcha' ] );
		add_action( 'pmpro_checkout_after_parameters_set', [ $this, 'verify' ] );
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
				'form_id' => 'checkout',
			],
		];

		Procaptcha::form_display( $args );
	}

	/**
	 * Verify login form.
	 *
	 * @return void
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function verify() {
		global $pmpro_msg, $pmpro_msgt;

		if ( ! pmpro_was_checkout_form_submitted() ) {
			return;
		}

		$error_message = procaptcha_verify_post(
			self::NONCE,
			self::ACTION
		);

		if ( null === $error_message ) {
			return;
		}

		$pmpro_msg  = $error_message;
		$pmpro_msgt = 'pmpro_error';
	}
}
