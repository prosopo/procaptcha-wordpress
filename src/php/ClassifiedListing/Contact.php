<?php
/**
 * Contact class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\ClassifiedListing;

use Procaptcha\Helpers\Procaptcha;
use WP_Error;

/**
 * Class Contact.
 */
class Contact {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_classified_listing_contact';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_classified_listing_contact_nonce';

	/**
	 * Class constructor.
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
		add_action( 'rtcl_before_template_part', [ $this, 'before_template_part' ], 10, 3 );
		add_action( 'rtcl_after_template_part', [ $this, 'after_template_part' ], 10, 3 );
		add_action( 'rtcl_listing_seller_contact_form_validation', [ $this, 'verify' ], 10, 2 );
	}

	/**
	 * Start output buffer before template part.
	 *
	 * @param string           $template_name Template name.
	 * @param string           $located       Location.
	 * @param array|null|mixed $template_args Arguments.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function before_template_part( string $template_name, string $located, $template_args ) {
		if ( 'listing/email-to-seller-form' !== $template_name ) {
			return;
		}

		ob_start();
	}

	/**
	 * Stop output buffer after template part and add captcha.
	 *
	 * @param string           $template_name Template name.
	 * @param string           $located       Location.
	 * @param array|null|mixed $template_args Arguments.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function after_template_part( string $template_name, string $located, $template_args ) {
		if ( 'listing/email-to-seller-form' !== $template_name ) {
			return;
		}

		$template = ob_get_clean();

		$args = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'id'     => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => 'contact',
			],
		];

		$search   = '<button type="submit"';
		$replace  = Procaptcha::form( $args ) . $search;
		$template = str_replace( $search, $replace, $template );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $template;
	}

	/**
	 * Verify a contact form.
	 *
	 * @param WP_Error $error Error.
	 * @param array    $data  Form data.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection UnusedFunctionResultInspection
	 */
	public function verify( WP_Error $error, array $data ) {
		$error_message = procaptcha_verify_post(
			static::NONCE,
			static::ACTION
		);

		Procaptcha::add_error_message( $error, $error_message );
	}
}
