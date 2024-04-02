<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\Subscriber;

use Procaptcha\Helpers\Procaptcha;

/**
 * Class Form.
 */
class Form {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_subscriber_form';

	/**
	 * Nonce name.
	 */
	const NAME = 'procaptcha_subscriber_form_nonce';

	/**
	 * Form constructor.
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
		add_filter( 'sbscrbr_add_field', [ $this, 'add_captcha' ] );
		add_filter( 'sbscrbr_check', [ $this, 'verify' ] );
	}

	/**
	 * Add captcha to the subscriber form.
	 *
	 * @param string|mixed $content Subscriber form content.
	 *
	 * @return string
	 */
	public function add_captcha( $content ): string {
		$args = [
			'action' => self::ACTION,
			'name'   => self::NAME,
			'id'     => [
				'source'  => Procaptcha::get_class_source( static::class ),
				'form_id' => 'form',
			],
		];

		return $content . Procaptcha::form( $args );
	}

	/**
	 * Verify subscriber captcha.
	 *
	 * @param bool|mixed $check_result Check result.
	 *
	 * @return bool|string|mixed
	 * @noinspection NullCoalescingOperatorCanBeUsedInspection
	 */
	public function verify( $check_result ) {
		$error_message = procaptcha_get_verify_message( self::NAME, self::ACTION );

		if ( null !== $error_message ) {
			return $error_message;
		}

		return $check_result;
	}
}
