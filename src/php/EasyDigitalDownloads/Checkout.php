<?php
/**
 * Checkout class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\EasyDigitalDownloads;

use Procaptcha\Helpers\Procaptcha;

/**
 * Class Checkout.
 */
class Checkout {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_easy_digital_downloads_register';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_easy_digital_downloads_register_nonce';

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
		add_action( 'edd_purchase_form_before_submit', [ $this, 'add_captcha' ] );
		add_filter( 'edd_errors', [ $this, 'verify' ] );
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
	 * Verify checkout form.
	 *
	 * @param array|mixed $errors Errors.
	 *
	 * @return array|mixed
	 */
	public function verify( $errors ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$post_value = isset( $_POST['action'] ) ?
			sanitize_text_field( wp_unslash( $_POST['action'] ) ) :
			'';

		if ( 'edd_process_checkout' !== $post_value ) {
			return $errors;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$error_message = procaptcha_verify_post(
			self::NONCE,
			self::ACTION
		);

		if ( null === $error_message ) {
			return $errors;
		}

		$code = array_search( $error_message, procaptchaget_error_messages(), true ) ?: 'fail';

		$errors          = $errors ? (array) $errors : [];
		$errors[ $code ] = $error_message;

		return $errors;
	}
}
