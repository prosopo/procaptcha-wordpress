<?php
/**
 * PasswordProtected class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\WP;

use Procaptcha\Helpers\Procaptcha;
use WP_Post;

/**
 * Class PasswordProtected.
 */
class PasswordProtected {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_password_protected';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_password_protected_nonce';

	/**
	 * PasswordProtected constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_filter( 'the_password_form', [ $this, 'add_captcha' ], PHP_INT_MAX, 2 );
		add_action( 'login_form_postpass', [ $this, 'verify' ] );
	}

	/**
	 * Filters the template created by the Download Manager plugin and adds procaptcha.
	 *
	 * @param string|mixed $output The password form HTML output.
	 * @param WP_Post      $post   Post object.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_captcha( $output, WP_Post $post ): string {
		$args = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'id'     => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => 'password_protected',
			],
		];

		$procaptcha = Procaptcha::form( $args );

		return (string) preg_replace( '/(<\/form>)/', $procaptcha . '$1', (string) $output );
	}

	/**
	 * Verify request.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection ForgottenDebugOutputInspection
	 */
	public function verify() {
		$result = procaptcha_verify_post( self::NONCE, self::ACTION );

		if ( null === $result ) {
			return;
		}

		wp_die(
			esc_html( $result ),
			'procap_',
			[
				'back_link' => true,
				'response'  => 303,
			]
		);
	}
}
