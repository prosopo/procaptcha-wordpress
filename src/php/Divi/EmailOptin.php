<?php
/**
 * EmailOptin class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\Divi;

use PROCAPTCHA\Helpers\PROCAPTCHA;

/**
 * Class EmailOptin.
 */
class EmailOptin {
	/**
	 * Script handle.
	 */
	const HANDLE = 'procaptcha-divi-email-optin';

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_divi_email_optin';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_divi_email_optin_nonce';

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
		add_filter( 'et_pb_signup_form_field_html_submit_button', [ $this, 'add_captcha' ], 10, 2 );
		add_action( 'wp_ajax_et_pb_submit_subscribe_form', [ $this, 'verify' ], 9 );
		add_action( 'wp_ajax_nopriv_et_pb_submit_subscribe_form', [ $this, 'verify' ], 9 );
		add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_scripts' ], 9 );
	}

	/**
	 * Add procaptcha to the email optin form.
	 *
	 * @param string|mixed $html              Submit button html.
	 * @param string       $single_name_field Whether a single name field is being used.
	 *                                        Only applicable when "$field" is 'name'.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_captcha( $html, string $single_name_field ): string {
		$args = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'id'     => [
				'source'  => PROCAPTCHA::get_class_source( __CLASS__ ),
				'form_id' => 'email_optin',
			],
		];

		$search  = '<p class="et_pb_newsletter_button_wrap">';
		$replace = PROCAPTCHA::form( $args ) . "\n" . $search;

		// Insert procaptcha.
		return str_replace( $search, $replace, (string) $html );
	}

	/**
	 * Verify email optin form.
	 *
	 * @return void
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function verify() {
		$error_message = procaptcha_get_verify_message_html(
			self::NONCE,
			self::ACTION
		);

		if ( null === $error_message ) {
			return;
		}

		et_core_die( esc_html( $error_message ) );
	}

	/**
	 * Enqueue Email Optin script.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! procaptcha()->form_shown ) {
			return;
		}

		$min = procap_min_suffix();

		wp_enqueue_script(
			self::HANDLE,
			PROCAPTCHA_URL . "/assets/js/procaptcha-divi-email-optin$min.js",
			[ 'jquery' ],
			PROCAPTCHA_VERSION,
			true
		);
	}
}
