<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\SimpleDownloadMonitor;

use Procaptcha\Helpers\Procaptcha;

/**
 * Class Form.
 */
class Form {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_simple_download_monitor';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_simple_download_monitor_nonce';

	/**
	 * Script handle.
	 */
	const HANDLE = 'procaptcha-simple-download-monitor';

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
		add_filter( 'sdm_download_shortcode_output', [ $this, 'add_captcha' ], 10, 2 );
		add_action( 'init', [ $this, 'verify' ], 0 );
		add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_scripts' ], 9 );
	}


	/**
	 * Add procaptcha to a Simple Download Monitor form.
	 *
	 * @param string|mixed $output The shortcode output.
	 * @param array        $atts   The attributes.
	 *
	 * @return string
	 */
	public function add_captcha( $output, array $atts ): string {
		$search = '<div class="sdm_download_link">';
		$args   = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'id'     => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => $atts['id'],
			],
		];

		return str_replace(
			$search,
			Procaptcha::form( $args ) . $search,
			(string) $output
		);
	}

	/**
	 * Verify Simple Download Monitor captcha.
	 *
	 * @noinspection ForgottenDebugOutputInspection
	 */
	public function verify() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$smd_process_download = isset( $_REQUEST['smd_process_download'] ) ?
			sanitize_text_field( wp_unslash( $_REQUEST['smd_process_download'] ) ) :
			'';
		$sdm_process_download = isset( $_REQUEST['sdm_process_download'] ) ?
			sanitize_text_field( wp_unslash( $_REQUEST['sdm_process_download'] ) ) :
			'';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( '1' !== $smd_process_download && '1' !== $sdm_process_download ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$_POST['procaptcha-response'] = $_GET['procaptcha-response'] ?? '';
		$_POST[ self::NONCE ]        = $_GET[ self::NONCE ] ?? '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

		$error_message = procaptcha_verify_post( self::NONCE, self::ACTION );

		unset( $_POST['procaptcha-response'], $_POST[ self::NONCE ] );

		if ( null === $error_message ) {
			return;
		}

		wp_die(
			esc_html( $error_message ),
			'procaptcha',
			[
				'back_link' => true,
				'response'  => 403,
			]
		);
	}

	/**
	 * Enqueue MailPoet script.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! procaptcha()->form_shown ) {
			return;
		}

		$min = procaptchamin_suffix();

		wp_enqueue_script(
			self::HANDLE,
			PROCAPTCHA_URL . "/assets/js/procaptcha-simple-download-monitor$min.js",
			[ 'jquery' ],
			PROCAPTCHA_VERSION,
			true
		);
	}
}
