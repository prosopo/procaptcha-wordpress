<?php
/**
 * LostPassword class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\UM;

/**
 * Class LostPassword
 */
class LostPassword extends Base {

	/**
	 * UM action.
	 */
	const UM_ACTION = 'um_reset_password_errors_hook';

	/**
	 * UM mode.
	 */
	const UM_MODE = 'password';

	/**
	 * Init hooks.
	 */
	protected function init_hooks() {
		parent::init_hooks();

		add_action( 'um_after_password_reset_fields', [ $this, 'um_after_password_reset_fields' ] );
	}

	/**
	 * Display procaptcha after password reset fields.
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function um_after_password_reset_fields( array $args ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->display_captcha( '', self::UM_MODE );
	}
}
