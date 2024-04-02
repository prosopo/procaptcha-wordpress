<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\HTMLForms;

use Procaptcha\Helpers\Procaptcha;

/**
 * Class Form
 */
class Form {

	/**
	 * Nonce action.
	 */
	const ACTION = 'html_forms_form';

	/**
	 * Nonce name.
	 */
	const NONCE = 'html_forms_form_nonce';

	/**
	 * The procap_ general error code.
	 */
	const HCAPTCHA_ERROR = 'procaptcha_error';

	/**
	 * Error message.
	 *
	 * @var string|null
	 */
	private $error_message;

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
		add_filter( 'hf_form_html', [ $this, 'add_captcha' ], 10, 2 );
		add_action( 'hf_admin_output_form_tab_fields', [ $this, 'add_to_fields' ] );
		add_filter( 'hf_validate_form_request_size', '__return_false' );
		add_filter( 'hf_validate_form', [ $this, 'verify' ], 10, 3 );
		add_filter( 'wp_insert_post_data', [ $this, 'insert_post_data' ], 10, 4 );
		add_filter( 'hf_form_message_' . self::HCAPTCHA_ERROR, [ $this, 'get_message' ] );
		add_action( 'wp_head', [ $this, 'print_inline_styles' ], 20 );
	}

	/**
	 * Filter the submit button element HTML.
	 *
	 * @param string|mixed     $html Button HTML.
	 * @param \HTML_Forms\Form $form Form data and settings.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_captcha( $html, \HTML_Forms\Form $form ): string {
		$form_id = (int) ( $form->ID ?? 0 );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$preview_id = isset( $_GET['hf_preview_form'] ) ?
			(int) sanitize_text_field( wp_unslash( $_GET['hf_preview_form'] ) ) :
			0;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( $preview_id === $form_id ) {
			ob_start();
			$this->print_inline_styles();
			$html = ob_get_clean() . $html;
		}

		$args = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'id'     => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => $form_id,
			],
		];

		return (string) preg_replace(
			'/(<p.*?>\s*?<input\s*?type="submit")/',
			Procaptcha::form( $args ) . "\n$1",
			$html
		);
	}

	/**
	 * Add procap_ to fields.
	 *
	 * @param \HTML_Forms\Form $form Form.
	 *
	 * @return void
	 */
	public function add_to_fields( \HTML_Forms\Form $form ) {
		if ( false !== strpos( $form->markup, 'class="procaptcha"' ) ) {
			return;
		}

		$form->markup = $this->add_captcha( $form->markup, $form );
	}

	/**
	 * Verify procap_.
	 *
	 * @param string|mixed     $error_code Error code.
	 * @param \HTML_Forms\Form $form       Form.
	 * @param array            $data       Form data.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function verify( $error_code, \HTML_Forms\Form $form, array $data ): string {
		$error_code = (string) $error_code;

		$this->error_message = procaptcha_verify_post(
			self::NONCE,
			self::ACTION
		);

		if ( null !== $this->error_message ) {
			return self::HCAPTCHA_ERROR;
		}

		return $error_code;
	}

	/**
	 * Filter inserted post data.
	 * Remove <div class="procaptcha"> form the content.
	 *
	 * @param array|mixed $data                An array of slashed, sanitized, and processed post data.
	 * @param array       $postarr             An array of sanitized (and slashed) but otherwise unmodified post data.
	 * @param array       $unsanitized_postarr An array of slashed yet *unsanitized* and unprocessed post data as
	 *                                         originally passed to wp_insert_post().
	 * @param bool        $update              Whether this is an existing post being updated.
	 *
	 * @return array
	 * @noinspection RegExpRedundantEscape
	 */
	public function insert_post_data( $data, array $postarr, array $unsanitized_postarr, bool $update ): array {
		$data = (array) $data;

		if ( 'html-form' !== $postarr['post_type'] ) {
			return $data;
		}

		$data['post_content'] = preg_replace(
			[
				'#\s*<div\s*?class=\\\"procaptcha\\\"[\s\S]*?</div>#',
				'#<input\s*?type=\\\"hidden\\\"\s*?id=\\\"html_forms_form_nonce\\\"[\s\S]*?/>#',
				'#<input\s*?type=\\\"hidden\\\"\s*?name=\\\"_wp_http_referer\\\"[\s\S]*?/>#',
			],
			[ '', '', '' ],
			$data['post_content']
		);

		return $data;
	}

	/**
	 * Get the error message.
	 *
	 * @param string $error_code Error code.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function get_message( string $error_code ) {

		return $this->error_message;
	}

	/**
	 * Print inline styles.
	 *
	 * @return void
	 * @noinspection CssUnusedSymbol
	 */
	public function print_inline_styles() {
		$css = <<<CSS
	#form-preview .procaptcha {
		margin-bottom: 2rem;
	}

	.hf-fields-wrap .procaptcha {
		margin-top: 2rem;
	}
CSS;

		Procaptcha::css_display( $css );
	}
}
