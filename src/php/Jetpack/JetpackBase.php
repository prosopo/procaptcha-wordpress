<?php
/**
 * Jetpack class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\Jetpack;

use Procaptcha\Helpers\Procaptcha;
use WP_Error;

/**
 * Class Jetpack
 */
abstract class JetpackBase {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_jetpack';

	/**
	 * Nonce name.
	 */
	const NAME = 'procaptcha_jetpack_nonce';

	/**
	 * Error message.
	 *
	 * @var string|null
	 */
	protected $error_message;

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
		add_filter( 'the_content', [ $this, 'add_captcha' ] );
		add_filter( 'widget_text', [ $this, 'add_captcha' ], 0 );

		add_filter( 'widget_text', 'shortcode_unautop' );
		add_filter( 'widget_text', 'do_shortcode' );

		add_filter( 'jetpack_contact_form_is_spam', [ $this, 'verify' ], 100, 2 );

		add_action( 'wp_head', [ $this, 'print_inline_styles' ] );
	}

	/**
	 * Add procaptcha to a Jetpack form.
	 *
	 * @param string|mixed $content Content.
	 *
	 * @return string
	 */
	abstract public function add_captcha( $content ): string;

	/**
	 * Verify procaptcha answer from the Jetpack Contact Form.
	 *
	 * @param bool|mixed $is_spam Is spam.
	 *
	 * @return bool|WP_Error|mixed
	 */
	public function verify( $is_spam = false ) {
		$this->error_message = procaptcha_get_verify_message(
			static::NAME,
			static::ACTION
		);

		if ( null === $this->error_message ) {
			return $is_spam;
		}

		$error = new WP_Error();
		$error->add( 'invalid_procaptcha', $this->error_message );
		add_filter( 'procaptchaprocaptcha_content', [ $this, 'error_message' ] );

		return $error;
	}

	/**
	 * Print error message.
	 *
	 * @param string|mixed $procaptcha_content Content of procaptcha.
	 *
	 * @return string|mixed
	 */
	public function error_message( $procaptcha_content = '' ) {
		if ( null === $this->error_message ) {
			return $procaptcha_content;
		}

		$message = <<< HTML
<div class="contact-form__input-error">
	<span class="contact-form__warning-icon">
		<span class="visually-hidden">Warning.</span>
		<i aria-hidden="true"></i>
	</span>
	<span>$this->error_message</span>
</div>
HTML;

		return $procaptcha_content . $message;
	}

	/**
	 * Print inline styles.
	 *
	 * @return void
	 * @noinspection CssUnusedSymbol CssUnusedSymbol.
	 */
	public function print_inline_styles() {
		$css = <<<CSS
	form.contact-form .grunion-field-wrap .procaptcha,
	form.wp-block-jetpack-contact-form .grunion-field-wrap .procaptcha {
		margin-bottom: 0;
	}
CSS;

		Procaptcha::css_display( $css );
	}
}
