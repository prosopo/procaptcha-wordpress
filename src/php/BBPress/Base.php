<?php
/**
 * Base class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\BBPress;

use Procaptcha\Helpers\Procaptcha;

/**
 * Class Base.
 */
abstract class Base {

	/**
	 * Base constructor.
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
		add_action( static::ADD_CAPTCHA_HOOK, [ $this, 'add_captcha' ] );
		add_action( static::VERIFY_HOOK, [ $this, 'verify' ] );
	}

	/**
	 * Add captcha to the form.
	 *
	 * @return void
	 */
	public function add_captcha() {
		$form_id = str_replace( 'procaptcha_bbp_', '', static::ACTION );
		$args    = [
			'action' => static::ACTION,
			'name'   => static::NAME,
			'id'     => [
				'source'  => Procaptcha::get_class_source( static::class ),
				'form_id' => $form_id,
			],
		];

		Procaptcha::form_display( $args );
	}

	/**
	 * Verify captcha.
	 *
	 * @return bool
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function verify(): bool {
		$error_message = procaptcha_get_verify_message( static::NAME, static::ACTION );

		if ( null !== $error_message ) {
			bbp_add_error( 'procaptchaerror', $error_message );

			return false;
		}

		return true;
	}
}
