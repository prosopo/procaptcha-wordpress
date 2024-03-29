<?php
/**
 * Base class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\WPForo;

use PROCAPTCHA\Helpers\PROCAPTCHA;

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
		add_action( static::ADD_CAPTCHA_HOOK, [ $this, 'add_captcha' ], 99 );
		add_filter( static::VERIFY_HOOK, [ $this, 'verify' ] );
		add_action( 'procap_print_procaptcha_scripts', [ $this, 'print_procaptcha_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Add captcha to the new topic form.
	 *
	 * @param array|int $topic Topic info.
	 */
	public function add_captcha( $topic ) {
		$form_id = 0;

		if ( current_action() === Reply::ADD_CAPTCHA_HOOK ) {
			$form_id = (int) $topic['topicid'];
		}

		if ( current_action() === NewTopic::ADD_CAPTCHA_HOOK ) {
			$form_id = 'new_topic';
		}

		$args = [
			'action' => static::ACTION,
			'name'   => static::NAME,
			'id'     => [
				'source'  => PROCAPTCHA::get_class_source( static::class ),
				'form_id' => $form_id,
			],
		];

		PROCAPTCHA::form_display( $args );
	}

	/**
	 * Verify new topic captcha.
	 *
	 * @param mixed $data Data.
	 *
	 * @return mixed|bool
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function verify( $data ) {
		$error_message = procaptcha_get_verify_message(
			static::NAME,
			static::ACTION
		);

		if ( null !== $error_message ) {
			WPF()->notice->add( $error_message, 'error' );

			return false;
		}

		return $data;
	}

	/**
	 * Filter print procaptcha scripts status and return true if WPForo template filter was used.
	 *
	 * @param bool|mixed $status Print scripts status.
	 *
	 * @return bool|mixed
	 */
	public function print_procaptcha_scripts( $status ) {
		return PROCAPTCHA::did_filter( 'wpforo_template' ) ? true : $status;
	}

	/**
	 * Enqueue WPForo script.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$min = procap_min_suffix();

		wp_enqueue_script(
			'procaptcha-wpforo',
			PROCAPTCHA_URL . "/assets/js/procaptcha-wpforo$min.js",
			[ 'jquery', 'wpforo-frontend-js', 'procaptcha' ],
			PROCAPTCHA_VERSION,
			true
		);
	}
}
