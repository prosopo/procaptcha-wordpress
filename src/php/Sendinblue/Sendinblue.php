<?php
/**
 * Sendinblue class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\Sendinblue;

use Procaptcha\Helpers\Procaptcha;

/**
 * Class Sendinblue.
 */
class Sendinblue {

	/**
	 * Sendinblue constructor.
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
		add_filter( 'do_shortcode_tag', [ $this, 'add_procaptcha' ], 10, 4 );
		add_filter( 'procaptchaverify_request', [ $this, 'verify_request' ], 10, 2 );
	}

	/**
	 * Filters the output created by a shortcode callback and adds procaptcha.
	 *
	 * @param string|mixed $output Shortcode output.
	 * @param string       $tag    Shortcode name.
	 * @param array|string $attr   Shortcode attributes array or empty string.
	 * @param array        $m      Regular expression match array.
	 *
	 * @return string|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_procaptcha( $output, string $tag, $attr, array $m ) {
		if ( 'sibwp_form' !== $tag ) {
			return $output;
		}

		$args = [
			'action' => PROCAPTCHA_ACTION,
			'name'   => PROCAPTCHA_NONCE,
			'auto'   => true,
			'id'     => [
				'source'  => Procaptcha::get_class_source( static::class ),
				'form_id' => (int) $attr['id'],
			],
		];

		$procaptcha = Procaptcha::form( $args );

		$output = (string) preg_replace(
			'/(<input type="submit"|<button .*?type="submit".*?>)/',
			$procaptcha . '$1',
			(string) $output
		);

		/**
		 * Filters the HTML containing a form to register it for auto-verification.
		 *
		 * @param string $html HTML content.
		 */
		do_action( 'procaptchaauto_verify_register', $output );

		return $output;
	}

	/**
	 * Verify request filter.
	 *
	 * @param string|null $result      Result of the procaptcha verification.
	 * @param array       $error_codes Error codes.
	 *
	 * @return string|null
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function verify_request( $result, array $error_codes ) {
		// Nonce is checked in the procaptcha_verify_post().

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['sib_form_action'] ) ) {
			// We are not in the Sendinblue submit request.
			return $result;
		}

		if ( null !== $result ) {
			wp_send_json(
				[
					'status' => 'failure',
					'msg'    => [ 'errorMsg' => $result ],
				]
			);
		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return $result;
	}
}
