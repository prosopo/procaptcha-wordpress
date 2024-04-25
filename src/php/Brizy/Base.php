<?php
/**
 * Base class file.
 *
 * @package hcaptcha-wp
 */

//phpcs:ignore Generic.Commenting.DocComment.MissingShort
/**
 * @noinspection PhpUndefinedClassInspection
 */

namespace HCaptcha\Brizy;

use Brizy_Editor_Project;
use HCaptcha\Helpers\HCaptcha;
use WP_Post;

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
		add_filter( static::ADD_CAPTCHA_HOOK, [ $this, 'add_captcha' ], 10, 4 );
		add_filter( static::VERIFY_HOOK, [ $this, 'verify' ] );
		add_action( 'wp_head', [ $this, 'print_inline_styles' ], 20 );
	}

	/**
	 * Add captcha to the form.
	 *
	 * @param string|mixed         $content Content of the current post.
	 * @param Brizy_Editor_Project $project Brizy project.
	 * @param WP_Post              $post    Post.
	 * @param string               $type    Type of the content.
	 *
	 * @return       string|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_captcha( $content, Brizy_Editor_Project $project, WP_Post $post, string $type = '' ) {
		if ( 'body' !== $type ) {
			return $content;
		}

		$args = [
			'action' => static::ACTION,
			'name'   => static::NAME,
			'id'     => [
				'source'  => HCaptcha::get_class_source( static::class ),
				'form_id' => 'form',
			],
		];

		$search  = '<div class="brz-forms2 brz-forms2__item brz-forms2__item-button"';
		$replace =
		'<div class="brz-forms2 brz-forms2__item">' .
		HCaptcha::form( $args ) .
		'</div>' .
		$search;

		return str_replace( $search, $replace, (string) $content );
	}

	/**
	 * Verify captcha.
	 *
	 * @param mixed $form Validate fields.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function verify( $form ) {
     // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$data              = isset( $_POST['data'] ) ? sanitize_text_field( wp_unslash( $_POST['data'] ) ) : '';
		$data_arr          = json_decode( $data, true );
		$hcaptcha_response = '';

		foreach ( $data_arr as $item ) {
			if ( ! isset( $item['name'], $item['value'] ) ) {
				continue;
			}

			if ( 'g-recaptcha-response' === $item['name'] || 'procaptcha-response' === $item['name'] ) {
				$hcaptcha_response = $item['value'];
			}

			if ( 'hcaptcha-widget-id' === $item['name'] ) {
				$_POST[ HCaptcha::HCAPTCHA_WIDGET_ID ] = $item['value'];
			}
		}

		$error_message = hcaptcha_request_verify( $hcaptcha_response );

		if ( null !== $error_message ) {
			wp_send_json_error(
				[
					'code'    => 400,
					'message' => $error_message,
				],
				200
			);
		}

		return $form;
	}

	/**
	 * Print inline styles.
	 *
	 * @return       void
	 * @noinspection CssUnusedSymbol
	 */
	public function print_inline_styles() {
		static $style_shown;

		if ( $style_shown ) {
			return;
		}

		$style_shown = true;

		$css = <<<CSS
	.brz-forms2.brz-forms2__item .procaptcha {
		margin-bottom: 0;
	}
CSS;

		HCaptcha::css_display( $css );
	}
}
