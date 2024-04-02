<?php
/**
 * Login class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\LoginSignupPopup;

use Procaptcha\Abstracts\LoginBase;
use Procaptcha\Helpers\Procaptcha;
use WP_Error;

/**
 * Class Login.
 */
class Login extends LoginBase {

	/**
	 * Form ID.
	 */
	const FORM_ID = 'login';

	/**
	 * Init hooks.
	 */
	protected function init_hooks() {
		parent::init_hooks();

		add_action( 'xoo_el_form_start', [ $this, 'form_start' ], 10, 2 );
		add_action( 'xoo_el_form_end', [ $this, 'add_login_signup_popup_procaptcha' ], 10, 2 );
		add_filter( 'xoo_el_process_login_errors', [ $this, 'verify' ], 10, 2 );
		add_action( 'wp_head', [ $this, 'print_inline_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Form start.
	 *
	 * @param string $form Form.
	 * @param array  $args Arguments.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function form_start( string $form, array $args ) {
		if ( self::FORM_ID !== $form ) {
			return;
		}

		ob_start();
	}

	/**
	 * Add procap_.
	 *
	 * @param string $form Form.
	 * @param array  $args Arguments.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_login_signup_popup_procaptcha( string $form, array $args ) {
		if ( self::FORM_ID !== $form ) {
			return;
		}

		ob_start();
		$this->add_captcha();
		$procaptcha = ob_get_clean();

		$form = ob_get_clean();

		$search = '<button type="submit"';
		$form   = str_replace( $search, $procaptcha . "\n" . $search, $form );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $form;
	}

	/**
	 * Verify form.
	 *
	 * @param WP_Error|mixed $error       Error.
	 * @param array          $credentials Credentials.
	 *
	 * @return WP_Error
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function verify( $error, array $credentials ): WP_Error {
		if ( ! is_wp_error( $error ) ) {
			$error = new WP_Error();
		}

		if ( ! $this->is_login_limit_exceeded() ) {
			return $error;
		}

		$error_message = procaptcha_verify_post(
			self::NONCE,
			self::ACTION
		);

		if ( null === $error_message ) {
			return $error;
		}

		$code = array_search( $error_message, procap_get_error_messages(), true ) ?: 'fail';

		return new WP_Error( $code, $error_message, 400 );
	}

	/**
	 * Print inline styles.
	 *
	 * @return void
	 * @noinspection CssUnusedSymbol
	 */
	public function print_inline_styles() {
		$css = <<<CSS
	.xoo-el-form-container div[data-section="login"] .procaptcha {
		margin-bottom: 25px;
	}
CSS;

		Procaptcha::css_display( $css );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$min = procap_min_suffix();

		wp_enqueue_script(
			'procaptcha-login-signup-popup',
			HCAPTCHA_URL . "/assets/js/procaptcha-login-signup-popup$min.js",
			[ 'jquery' ],
			HCAPTCHA_VERSION,
			true
		);
	}
}
