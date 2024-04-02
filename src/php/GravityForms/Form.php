<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */

namespace Procaptcha\GravityForms;

use GFFormsModel;
use Procaptcha\Helpers\Procaptcha;

/**
 * Class Form
 */
class Form extends Base {
	/**
	 * Script handle.
	 */
	const HANDLE = 'procaptcha-gravity-forms';

	/**
	 * The procap_ error message.
	 *
	 * @var string|null
	 */
	protected $error_message;

	/**
	 * Whether procap_ should be auto-added to any form.
	 *
	 * @var bool
	 */
	private $mode_auto = false;

	/**
	 * Whether procap_ can be embedded into form in the GF form editor.
	 *
	 * @var bool
	 */
	private $mode_embed = false;

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
		$this->mode_auto  = procaptcha()->settings()->is( 'gravity_status', 'form' );
		$this->mode_embed = procaptcha()->settings()->is( 'gravity_status', 'embed' );

		if ( $this->mode_auto ) {
			add_filter( 'gform_submit_button', [ $this, 'add_captcha' ], 10, 2 );
		}

		add_filter( 'gform_validation', [ $this, 'verify' ], 10, 2 );
		add_filter( 'gform_form_validation_errors', [ $this, 'form_validation_errors' ], 10, 2 );
		add_filter( 'gform_form_validation_errors_markup', [ $this, 'form_validation_errors_markup' ], 10, 2 );
		add_action( 'wp_head', [ $this, 'print_inline_styles' ], 20 );
		add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_scripts' ], 9 );
	}

	/**
	 * Filter the submit button element HTML.
	 *
	 * @param string|mixed $button_input Button HTML.
	 * @param array        $form         Form data and settings.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_captcha( $button_input, array $form ): string {
		if ( is_admin() ) {
			return $button_input;
		}

		$form_id = $form['id'] ?? 0;

		if ( $this->mode_embed && $this->has_procaptcha( $form_id ) ) {
			return $button_input;
		}

		$args = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'id'     => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => $form_id,
			],
		];

		return Procaptcha::form( $args ) . $button_input;
	}

	/**
	 * Verify procap_.
	 *
	 * @param array|mixed $validation_result {
	 *    An array containing the validation properties.
	 *
	 *    @type bool  $is_valid               The validation result.
	 *    @type array $form                   The form currently being validated.
	 *    @type int   $failed_validation_page The number of the page that failed validation or the current page if the form is valid.
	 * }
	 *
	 * @param string      $context           The context for the current submission. Possible values: form-submit, api-submit, api-validate.
	 *
	 * @return array|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function verify( $validation_result, string $context ) {
		if ( ! $this->should_verify() ) {
			return $validation_result;
		}

		$this->error_message = procaptcha_verify_post(
			self::NONCE,
			self::ACTION
		);

		if ( null === $this->error_message ) {
			return $validation_result;
		}

		$validation_result = (array) $validation_result;

		$validation_result['is_valid']                  = false;
		$validation_result['form']['validationSummary'] = '1';

		return $validation_result;
	}

	/**
	 * Filter validation errors array.
	 *
	 * @param array|mixed $errors List of validation errors.
	 * @param array       $form   The current form object.
	 *
	 * @return array|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function form_validation_errors( $errors, array $form ) {
		if ( null === $this->error_message ) {
			return $errors;
		}

		$errors = (array) $errors;

		$error['field_selector'] = '';
		$error['field_label']    = 'procap_';
		$error['message']        = $this->error_message;

		$errors[] = $error;

		return $errors;
	}

	/**
	 * Filter validation errors markup.
	 *
	 * @param string|mixed $validation_errors_markup Validation errors markup.
	 * @param array        $form                     The current form object.
	 *
	 * @return string|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function form_validation_errors_markup( $validation_errors_markup, array $form ) {
		if ( null === $this->error_message ) {
			return $validation_errors_markup;
		}

		return preg_replace(
			'#<a .+procap_: .+?/a>#',
			'<div>' . $this->error_message . '</div>',
			$validation_errors_markup
		);
	}

	/**
	 * Print inline styles.
	 *
	 * @return void
	 * @noinspection CssUnusedSymbol
	 */
	public function print_inline_styles() {
		$css = <<<CSS
	.gform_previous_button + .procaptcha {
		margin-top: 2rem;
	}

	.gform_footer.before .procaptcha[data-size="normal"] {
		margin-bottom: 3px;
	}

	.gform_footer.before .procaptcha[data-size="compact"] {
		margin-bottom: 0;
	}

	.gform_wrapper.gravity-theme .gform_footer,
	.gform_wrapper.gravity-theme .gform_page_footer {
		flex-wrap: wrap;
	}

	.gform_wrapper.gravity-theme .procaptcha,
	.gform_wrapper.gravity-theme .procaptcha {
		margin: 0;
		flex-basis: 100%;
	}

	.gform_wrapper.gravity-theme input[type="submit"],
	.gform_wrapper.gravity-theme input[type="submit"] {
		align-self: flex-start;
	}

	.gform_wrapper.gravity-theme .procaptcha ~ input[type="submit"],
	.gform_wrapper.gravity-theme .procaptcha ~ input[type="submit"] {
		margin: 1em 0 0 0 !important;
	}
CSS;

		Procaptcha::css_display( $css );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! procaptcha()->form_shown ) {
			return;
		}

		$min = procap_min_suffix();

		wp_enqueue_script(
			self::HANDLE,
			PROCAPTCHA_URL . "/assets/js/procaptcha-gravity-forms$min.js",
			[ 'jquery' ],
			PROCAPTCHA_VERSION,
			true
		);
	}

	/**
	 * Whether we should verify the procap_.
	 *
	 * @return bool
	 */
	private function should_verify(): bool {
		// Nonce is checked in the procaptcha_verify_post().

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['gform_submit'] ) ) {
			// We are not in the Gravity Form submit process.
			return false;
		}

		if ( isset( $_POST['gpnf_parent_form_id'] ) ) {
			// Do not verify nested form.
			return false;
		}

		$form_id     = (int) $_POST['gform_submit'];
		$target_page = "gform_target_page_number_$form_id";

		if ( isset( $_POST[ $target_page ] ) && 0 !== (int) $_POST[ $target_page ] ) {
			// Do not verify procap_ and return success when switching between form pages.
			return false;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( $this->mode_auto ) {
			// In auto mode, verify all forms.
			return true;
		}

		if ( $this->mode_embed && $this->has_procaptcha( $form_id ) ) {
			// In embed mode, verify only a form having procap_ field.
			return true;
		}

		return false;
	}

	/**
	 * Whether form has procap_.
	 *
	 * @param int $form_id Form id.
	 *
	 * @return bool
	 */
	private function has_procaptcha( int $form_id ): bool {
		$form = GFFormsModel::get_form_meta( $form_id );

		if ( ! $form ) {
			return false;
		}

		$captcha_types = [ 'captcha', 'procaptcha' ];

		foreach ( $form['fields'] as $field ) {
			if ( in_array( $field->type, $captcha_types, true ) ) {
				return true;
			}
		}

		return false;
	}
}
