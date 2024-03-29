<?php
/**
 * Protect class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\Passster;

use PROCAPTCHA\Helpers\PROCAPTCHA;

/**
 * Class Protect
 */
class Protect {

	/**
	 * Script handle.
	 */
	const HANDLE = 'procaptcha-passster';

	/**
	 * Verify action.
	 */
	const ACTION = 'procaptcha_passster';

	/**
	 * Verify nonce.
	 */
	const NONCE = 'procaptcha_passster_nonce';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 */
	private function init_hooks() {
		add_filter( 'do_shortcode_tag', [ $this, 'do_shortcode_tag' ], 10, 4 );
		add_action( 'wp_ajax_validate_input', [ $this, 'verify' ], 9 );
		add_action( 'wp_ajax_nopriv_validate_input', [ $this, 'verify' ], 9 );
		add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_scripts' ], 9 );
	}

	/**
	 * Filters the output created by a shortcode callback.
	 *
	 * @param string|mixed $output Shortcode output.
	 * @param string       $tag    Shortcode name.
	 * @param array|string $attr   Shortcode attributes array or empty string.
	 * @param array        $m      Regular expression match array.
	 *
	 * @return string|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function do_shortcode_tag( $output, string $tag, $attr, array $m ) {
		if ( 'passster' !== $tag ) {
			return $output;
		}

		$form_id = 0;

		if ( preg_match( '/data-area="(.+)"/i', $output, $m ) ) {
			$form_id = $m[1];
		}

		$args = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'auto'   => true,
			'id'     => [
				'source'  => PROCAPTCHA::get_class_source( __CLASS__ ),
				'form_id' => $form_id,
			],
		];

		$search  = '<button name="submit"';
		$replace = PROCAPTCHA::form( $args ) . $search;

		$output = (string) str_replace(
			$search,
			$replace,
			(string) $output
		);

		/** This action is documented in src/php/Sendinblue/Sendinblue.php */
		do_action( 'procap_auto_verify_register', $output );

		return $output;
	}

	/**
	 * Verify captcha.
	 *
	 * @param string $input Password input.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function verify( string $input ) {
		$error_message = procaptcha_verify_post( self::NONCE, self::ACTION );

		if ( null === $error_message ) {
			return;
		}

		echo wp_json_encode( [ 'error' => $error_message ] );
		exit;
	}

	/**
	 * Enqueue script.
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
			PROCAPTCHA_URL . "/assets/js/procaptcha-passster$min.js",
			[ 'jquery' ],
			PROCAPTCHA_VERSION,
			true
		);
	}
}
