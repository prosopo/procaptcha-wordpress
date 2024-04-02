<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\ACFE;

use Procaptcha\Helpers\Procaptcha;

/**
 * Class Form.
 */
class Form {

	/**
	 * Script handle.
	 */
	const HANDLE = 'procaptcha-acfe';

	/**
	 * Render hook.
	 */
	const RENDER_HOOK = 'acf/render_field/type=acfe_recaptcha';

	/**
	 * Validation hook.
	 */
	const VALIDATION_HOOK = 'acf/validate_value/type=acfe_recaptcha';

	/**
	 * Form id.
	 *
	 * @var int
	 */
	protected $form_id = 0;

	/**
	 * Captcha added.
	 *
	 * @var bool
	 */
	private $captcha_added = false;

	/**
	 * Form constructor.
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
		add_action( 'acfe/form/render/before_fields', [ $this, 'before_fields' ] );
		add_action( self::RENDER_HOOK, [ $this, 'remove_recaptcha_render' ], 8 );
		add_action( self::RENDER_HOOK, [ $this, 'add_procaptcha' ], 11 );
		add_filter( self::VALIDATION_HOOK, [ $this, 'remove_recaptcha_verify' ], 9, 4 );
		add_filter( self::VALIDATION_HOOK, [ $this, 'verify' ], 11, 4 );
		add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_scripts' ], 9 );
	}

	/**
	 * Store form_id on before_fields hook.
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 */
	public function before_fields( array $args ) {
		$this->form_id = $args['ID'];
	}

	/**
	 * Start output buffer on processing the reCaptcha field.
	 *
	 * @param array $field Field.
	 *
	 * @return void
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function remove_recaptcha_render( array $field ) {
		if ( ! $this->is_recaptcha( $field ) ) {
			return;
		}

		$recaptcha = acf_get_field_type( 'acfe_recaptcha' );

		remove_action( self::RENDER_HOOK, [ $recaptcha, 'render_field' ], 9 );
	}

	/**
	 * Replaces reCaptcha field by procap_.
	 *
	 * @param array $field Field.
	 *
	 * @return void
	 */
	public function add_procaptcha( array $field ) {
		if ( ! $this->is_recaptcha( $field ) ) {
			return;
		}

		$args = [
			'id' => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => $this->form_id,
			],
		];

		$form =
			'<div class="acf-input-wrap acfe-field-recaptcha"> ' .
			'<div>' . Procaptcha::form( $args ) . '</div>' .
			'<input type="hidden" id="acf-' . $field['key'] . '" name="' . $field['name'] . '">' .
			'</div>';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $form;

		$this->captcha_added = true;
	}

	/**
	 * Remove reCaptcha verify filter.
	 *
	 * @param bool|mixed $valid Whether the field is valid.
	 * @param string     $value Field Value.
	 * @param array      $field Field.
	 * @param string     $input Input name.
	 *
	 * @return bool|mixed
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function remove_recaptcha_verify( $valid, string $value, array $field, string $input ) {
		$recaptcha = acf_get_field_type( 'acfe_recaptcha' );

		remove_filter( self::VALIDATION_HOOK, [ $recaptcha, 'validate_value' ] );

		return $valid;
	}

	/**
	 * Verify request.
	 *
	 * @param bool|mixed $valid Whether the field is valid.
	 * @param string     $value Field Value.
	 * @param array      $field Field.
	 * @param string     $input Input name.
	 *
	 * @return bool|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function verify( $valid, string $value, array $field, string $input ) {
		if ( ! $field['required'] ) {
			return $valid;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$this->form_id = isset( $_POST['_acf_post_id'] ) ?
			(int) sanitize_text_field( wp_unslash( $_POST['_acf_post_id'] ) ) :
			0;
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$id = Procaptcha::get_widget_id();

		// Avoid duplicate token: do not process during ajax validation.
		// Process procaptcha widget check when form protection is skipped.
		if ( wp_doing_ajax() && apply_filters( 'procap_protect_form', true, $id['source'], $id['form_id'] ) ) {
			return $valid;
		}

		return null === procaptcha_request_verify( $value );
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

		$min = procap_min_suffix();

		wp_enqueue_script(
			self::HANDLE,
			HCAPTCHA_URL . "/assets/js/procaptcha-acfe$min.js",
			[ 'jquery', 'procaptcha' ],
			HCAPTCHA_VERSION,
			true
		);
	}

	/**
	 * Whether it is the reCaptcha field.
	 *
	 * @param array $field Field.
	 *
	 * @return bool
	 */
	private function is_recaptcha( array $field ): bool {
		return isset( $field['type'] ) && 'acfe_recaptcha' === $field['type'];
	}
}
