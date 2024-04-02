<?php
/**
 * LostPasswordBase class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\Abstracts;

use Procaptcha\Helpers\Procaptcha;
use WP_Error;

/**
 * Class LostPasswordBase
 */
abstract class LostPasswordBase {

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
		add_action( static::ADD_CAPTCHA_ACTION, [ $this, 'add_captcha' ] );
		add_action( 'lostpassword_post', [ $this, 'verify' ] );
	}

	/**
	 * Add captcha.
	 *
	 * @return void
	 */
	public function add_captcha() {
		$args = [
			'action' => static::ACTION,
			'name'   => static::NONCE,
			'id'     => [
				'source'  => Procaptcha::get_class_source( static::class ),
				'form_id' => 'lost_password',
			],
		];

		Procaptcha::form_display( $args );
	}

	/**
	 * Verify a lost password form.
	 *
	 * @param WP_Error|mixed $errors Error.
	 *
	 * @return void
	 * @noinspection UnusedFunctionResultInspection
	 */
	public function verify( $errors ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$post_value = isset( $_POST[ static::POST_KEY ] ) ?
			sanitize_text_field( wp_unslash( $_POST[ static::POST_KEY ] ) ) :
			'';

		if (
			( ! isset( $_POST[ static::POST_KEY ] ) ) ||
			( static::POST_VALUE && static::POST_VALUE !== $post_value )
		) {
			// This class cannot handle a submitted lost password form.
			return;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$error_message = procaptcha_verify_post(
			static::NONCE,
			static::ACTION
		);

		Procaptcha::add_error_message( $errors, $error_message );
	}
}
