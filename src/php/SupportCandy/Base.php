<?php
/**
 * Base class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\SupportCandy;

use PROCAPTCHA\Helpers\PROCAPTCHA;

/**
 * Class Base.
 */
abstract class Base {

	/**
	 * Whether supportcandy shortcode was used.
	 *
	 * @var bool
	 */
	private $did_support_candy_shortcode_tag_filter = false;

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
		add_action( 'wp_ajax_' . static::VERIFY_HOOK, [ $this, 'verify' ], 9 );
		add_action( 'wp_ajax_nopriv_' . static::VERIFY_HOOK, [ $this, 'verify' ], 9 );
		add_filter( 'do_shortcode_tag', [ $this, 'support_candy_shortcode_tag' ], 10, 4 );
		add_action( 'procap_print_procaptcha_scripts', [ $this, 'print_procaptcha_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Add captcha to the form.
	 *
	 * @return void
	 */
	public function add_captcha() {
		$args = [
			'action' => static::ACTION,
			'name'   => static::NAME,
			'id'     => [
				'source'  => PROCAPTCHA::get_class_source( static::class ),
				'form_id' => 'form',
			],
		];

		PROCAPTCHA::form_display( $args );
	}

	/**
	 * Verify captcha.
	 *
	 * @return void
	 */
	public function verify() {
		$error_message = procaptcha_get_verify_message(
			static::NAME,
			static::ACTION
		);

		if ( null !== $error_message ) {
			wp_send_json_error( $error_message, 400 );
		}
	}

	/**
	 * Catch Support Candy do shortcode tag filter.
	 *
	 * @param string|mixed $output Shortcode output.
	 * @param string       $tag    Shortcode name.
	 * @param array|string $attr   Shortcode attributes array or empty string.
	 * @param array        $m      Regular expression match array.
	 *
	 * @return string|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function support_candy_shortcode_tag( $output, string $tag, $attr, array $m ) {
		if ( 'supportcandy' === $tag ) {
			$this->did_support_candy_shortcode_tag_filter = true;
		}

		return $output;
	}

	/**
	 * Filter print procaptcha scripts status and return true if SupportCandy shortcode was used.
	 *
	 * @param bool|mixed $status Print scripts status.
	 *
	 * @return bool|mixed
	 */
	public function print_procaptcha_scripts( $status ) {
		return $this->did_support_candy_shortcode_tag_filter ? true : $status;
	}

	/**
	 * Enqueue Support Candy script.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$min = procap_min_suffix();

		wp_enqueue_script(
			'procaptcha-support-candy',
			PROCAPTCHA_URL . "/assets/js/procaptcha-support-candy$min.js",
			[ 'jquery', 'procaptcha' ],
			PROCAPTCHA_VERSION,
			true
		);
	}
}
