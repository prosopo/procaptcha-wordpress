<?php
/**
 * Checkout class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\WC;

use Procaptcha\Helpers\Procaptcha;

/**
 * Class Checkout
 */
class Checkout {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_wc_checkout';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_wc_checkout_nonce';

	/**
	 * Script handle.
	 */
	const HANDLE = 'procaptcha-wc-checkout';

	/**
	 * The procaptcha was added.
	 *
	 * @var bool
	 */
	private $captcha_added = false;

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
		add_action( 'woocommerce_review_order_before_submit', [ $this, 'add_captcha' ] );
		add_action( 'woocommerce_checkout_process', [ $this, 'verify' ] );
		add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_scripts' ], 9 );
	}

	/**
	 * Add captcha.
	 *
	 * @return void
	 */
	public function add_captcha() {
		$args = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'id'     => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => 'checkout',
			],
		];

		Procaptcha::form_display( $args );

		$this->captcha_added = true;
	}

	/**
	 * Verify checkout form.
	 *
	 * @return void
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function verify() {
		$error_message = procaptcha_get_verify_message(
			self::NONCE,
			self::ACTION
		);

		if ( null !== $error_message ) {
			wc_add_notice( $error_message, 'error' );
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! $this->captcha_added ) {
			return;
		}

		$min = procaptchamin_suffix();

		wp_enqueue_script(
			self::HANDLE,
			PROCAPTCHA_URL . "/assets/js/procaptcha-wc-checkout$min.js",
			[ 'jquery', 'procaptcha' ],
			PROCAPTCHA_VERSION,
			true
		);
	}
}
