<?php
/**
 * Login class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\MemberPress;

use Procaptcha\Abstracts\LoginBase;
use Procaptcha\Helpers\Procaptcha;
use WP_Error;
use WP_User;

/**
 * Class Login
 */
class Login extends LoginBase {
	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_memberpress_login';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_memberpress_login_nonce';

	/**
	 * Init hooks.
	 */
	protected function init_hooks() {
		parent::init_hooks();

		add_action( 'mepr-login-form-before-submit', [ $this, 'add_captcha' ] );
		add_filter( 'wp_authenticate_user', [ $this, 'verify' ], 10, 2 );
	}

	/**
	 * Verify a login form.
	 *
	 * @since        1.0
	 *
	 * @param WP_User|WP_Error $user     WP_User or WP_Error object
	 *                                   if a previous callback failed authentication.
	 * @param string           $password Password to check against the user.
	 *
	 * @return WP_User|WP_Error
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function verify( $user, string $password ) {
		if ( ! Procaptcha::did_filter( 'mepr-validate-login' ) ) {
			return $user;
		}

		if ( ! $this->is_login_limit_exceeded() ) {
			return $user;
		}

		$error_message = procaptcha_get_verify_message_html(
			self::NONCE,
			self::ACTION
		);

		if ( null === $error_message ) {
			return $user;
		}

		return new WP_Error( 'invalid_procaptcha', $error_message, 400 );
	}
}
