<?php
/**
 * Subscribe class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\WPDiscuz;

use PROCAPTCHA\Helpers\PROCAPTCHA;

/**
 * Class Subscribe.
 */
class Subscribe extends Base {

	/**
	 * Add hooks.
	 *
	 * @return void
	 */
	protected function init_hooks() {
		parent::init_hooks();

		add_action( 'wpdiscuz_after_subscription_form', [ $this, 'add_procaptcha' ], 10, 3 );
		add_action( 'wp_ajax_wpdAddSubscription', [ $this, 'verify' ], 9 );
		add_action( 'wp_ajax_nopriv_wpdAddSubscription', [ $this, 'verify' ], 9 );
	}

	/**
	 * Replaces reCaptcha field by procaptcha in wpDiscuz form.
	 *
	 * @return void
	 */
	public function add_procaptcha() {
		global $post;

		$args = [
			'id' => [
				'source'  => PROCAPTCHA::get_class_source( static::class ),
				'form_id' => $post->ID ?? 0,
			],
		];

		PROCAPTCHA::form_display( $args );
	}

	/**
	 * Verify request.
	 *
	 * @return void
	 */
	public function verify() {
		// Nonce is checked by wpDiscuz.

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$procaptcha_response = isset( $_POST['procaptcha-response'] ) ?
			filter_var( wp_unslash( $_POST['procaptcha-response'] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ) :
			'';

		$result = procaptcha_request_verify( $procaptcha_response );

		unset( $_POST['procaptcha-response'], $_POST['g-recaptcha-response'] );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( null === $result ) {
			return;
		}

		wp_send_json_error( $result );
	}
}
