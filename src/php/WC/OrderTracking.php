<?php
/**
 * OrderTracking class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\WC;

use PROCAPTCHA\Helpers\PROCAPTCHA;

/**
 * Class OrderTracking
 */
class OrderTracking {

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
		if ( 'woocommerce_order_tracking' !== $tag ) {
			return $output;
		}

		$args = [
			'action' => PROCAPTCHA_ACTION,
			'name'   => PROCAPTCHA_NONCE,
			'auto'   => true,
			'id'     => [
				'source'  => PROCAPTCHA::get_class_source( __CLASS__ ),
				'form_id' => 'order_tracking',
			],
		];

		$procap_form =
			'<div class="form-row"  style="margin-top: 2rem;">' .
			PROCAPTCHA::form( $args ) .
			'</div>';

		return (string) preg_replace(
			'/(<p class="form-row"><button type="submit"|<p class="form-actions">[\S\s]*?<button type="submit")/i',
			$procap_form . '$1',
			(string) $output,
			1
		);
	}
}
