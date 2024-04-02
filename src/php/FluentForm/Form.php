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

namespace Procaptcha\FluentForm;

use FluentForm\App\Models\Form as FluentForm;
use FluentForm\App\Modules\Form\FormFieldsParser;
use Procaptcha\Helpers\Procaptcha;
use Procaptcha\Main;
use stdClass;

/**
 * Class Form
 */
class Form {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_fluentform';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_fluentform_nonce';

	/**
	 * Script handle.
	 */
	const HANDLE = 'procaptcha-fluentform';

	/**
	 * Admin script handle.
	 */
	const ADMIN_HANDLE = 'admin-fluentform';

	/**
	 * Script localization object.
	 */
	const OBJECT = 'ProcaptchaFluentFormObject';

	/**
	 * Conversational form id.
	 *
	 * @var int
	 */
	private $form_id;

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
		add_filter( 'fluentform/rendering_field_html_procaptcha', [ $this, 'render_field_procaptcha' ], 10, 3 );
		add_action( 'fluentform/render_item_submit_button', [ $this, 'add_captcha' ], 9, 2 );
		add_action( 'fluentform/validation_errors', [ $this, 'verify' ], 10, 4 );
		add_filter( 'fluentform/rendering_form', [ $this, 'fluentform_rendering_form_filter' ] );
		add_filter( 'fluentform/has_procaptcha', [ $this, 'fluentform_has_procaptcha' ] );
		add_filter( 'procaptchaprint_procaptcha_scripts', [ $this, 'print_procaptcha_scripts' ] );
		add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_scripts' ], 9 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'wp_head', [ $this, 'print_inline_styles' ], 20 );
	}

	/**
	 * Replace Fluent Forms procaptcha field.
	 * Works for embedded procaptcha field.
	 *
	 * @param string|mixed $html The procaptcha field HTML.
	 * @param array        $data Field data.
	 * @param stdClass     $form Form.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function render_field_procaptcha( $html, array $data, stdClass $form ): string {
		$this->form_id = (int) $form->id;

		return $this->get_procaptcha_wrapped();
	}

	/**
	 * Insert procaptcha before the 'submit' button.
	 * Works for auto-added procaptcha.
	 *
	 * @param array    $submit_button Form data and settings.
	 * @param stdClass $form          Form data and settings.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_captcha( array $submit_button, stdClass $form ) {
		// Do not add if the form has its own procaptcha.
		if ( $this->has_own_procaptcha( $form ) ) {
			return;
		}

		$this->form_id = (int) $form->id;

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_procaptcha_wrapped();
	}

	/**
	 * Filter errors during form validation.
	 *
	 * @param array      $errors Errors.
	 * @param array      $data   Sanitized entry fields.
	 * @param FluentForm $form   Form data and settings.
	 * @param array      $fields Form fields.
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function verify( array $errors, array $data, FluentForm $form, array $fields ): array {
		remove_filter( 'pre_http_request', [ $this, 'pre_http_request' ] );

		$procaptcha_response           = $data['procaptcha-response'] ?? '';
		$_POST['procaptcha-widget-id'] = $data['procaptcha-widget-id'] ?? '';
		$error_message               = procaptcha_request_verify( $procaptcha_response );

		if ( null !== $error_message ) {
			$errors['procaptcha-response'] = [ $error_message ];
		}

		return $errors;
	}

	/**
	 * Filter print procaptcha scripts status and return true, so, always run procaptcha scripts.
	 * Form can have own procaptcha field, or we add procaptcha automatically.
	 *
	 * @param bool|mixed $status Print scripts status.
	 *
	 * @return bool
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function print_procaptcha_scripts( $status ): bool {
		// Remove an API script by Fluent Forms, having the 'procaptcha' handle.
		wp_dequeue_script( 'procaptcha' );
		wp_deregister_script( 'procaptcha' );

		return true;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		global $wp_scripts;

		$fluent_forms_conversational_script = 'fluent_forms_conversational_form';

		// Proceed with conversational form only.
		if ( ! wp_script_is( $fluent_forms_conversational_script ) ) {
			return;
		}

		$min = procaptchamin_suffix();

		wp_enqueue_script(
			self::HANDLE,
			PROCAPTCHA_URL . "/assets/js/procaptcha-fluentform$min.js",
			[ Main::HANDLE ],
			PROCAPTCHA_VERSION,
			true
		);

		wp_localize_script(
			self::HANDLE,
			self::OBJECT,
			[
				'id'  => 'fluent_forms_conversational_form',
				'url' => $wp_scripts->registered[ $fluent_forms_conversational_script ]->src,
			]
		);

		// Print localization data of conversational script.
		$wp_scripts->print_extra_script( $fluent_forms_conversational_script );

		// Remove a localization script. We will launch it from our HANDLE script on procaptchaLoaded event.
		wp_dequeue_script( $fluent_forms_conversational_script );
		wp_deregister_script( $fluent_forms_conversational_script );

		$form = $this->get_captcha();
		$form = str_replace(
			[
				'class="procaptcha"',
				'class="procaptcha-widget-id"',
			],
			[
				'class="procaptcha-hidden" style="display: none;"',
				'class="procaptcha-hidden procaptcha-widget-id"',
			],
			$form
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $form;
	}

	/**
	 * Enqueue script in admin.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		if ( ! $this->is_fluent_forms_admin_page() ) {
			return;
		}

		$min = procaptchamin_suffix();

		wp_enqueue_script(
			self::ADMIN_HANDLE,
			constant( 'PROCAPTCHA_URL' ) . "/assets/js/admin-fluentform$min.js",
			[ 'jquery' ],
			constant( 'PROCAPTCHA_VERSION' ),
			true
		);

		$notice = Procaptcha::get_procaptcha_plugin_notice();

		wp_localize_script(
			self::ADMIN_HANDLE,
			self::OBJECT,
			[
				'noticeLabel'       => $notice['label'],
				'noticeDescription' => $notice['description'],
			]
		);

		wp_enqueue_style(
			self::ADMIN_HANDLE,
			constant( 'PROCAPTCHA_URL' ) . "/assets/css/admin-fluentform$min.css",
			[],
			constant( 'PROCAPTCHA_VERSION' )
		);
	}

	/**
	 * Whether we are on the Fluent Forms admin pages.
	 *
	 * @return bool
	 */
	private function is_fluent_forms_admin_page(): bool {
		if ( ! is_admin() ) {
			return false;
		}

		$screen = get_current_screen();

		if ( ! $screen ) {
			return false;
		}

		$fluent_forms_admin_pages = [
			'fluent-forms_page_fluent_forms_settings',
		];

		return in_array( $screen->id, $fluent_forms_admin_pages, true );
	}

	/**
	 * Fluentform load form assets hook.
	 *
	 * @param stdClass|mixed $form Form.
	 *
	 * @return stdClass|mixed
	 */
	public function fluentform_rendering_form_filter( $form ) {
		if ( ! $form instanceof stdClass ) {
			return $form;
		}

		$this->form_id = (int) $form->id;

		return $form;
	}

	/**
	 * Do not allow auto-adding of procaptcha by Fluent form plugin. We do it by ourselves.
	 *
	 * @return false
	 */
	public function fluentform_has_procaptcha(): bool {
		add_filter( 'pre_http_request', [ $this, 'pre_http_request' ], 10, 3 );
		return false;
	}

	/**
	 * Filter http request to block procaptcha validation by Fluent Forms plugin.
	 *
	 * @param false|array|WP_Error $response    A preemptive return value of an HTTP request. Default false.
	 * @param array                $parsed_args HTTP request arguments.
	 * @param string               $url         The request URL.
	 *
	 * @return false|array|WP_Error
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function pre_http_request( $response, array $parsed_args, string $url ) {
		$verify_url     = procaptcha()->get_verify_url();
		$old_verify_url = str_replace( 'api.', '', $verify_url );
		$api_urls       = [
			$verify_url,
			$old_verify_url,
		];

		if ( ! in_array( $url, $api_urls, true ) ) {
			return $response;
		}

		return [
			'body'     => '{"success":true}',
			'response' =>
				[
					'code'    => 200,
					'message' => 'OK',
				],
		];
	}

	/**
	 * Print inline styles.
	 *
	 * @return void
	 * @noinspection CssUnusedSymbol
	 */
	public function print_inline_styles() {
		$css = <<<CSS
	.frm-fluent-form .procaptcha {
		line-height: 0;
		margin-bottom: 0;
	}
CSS;

		Procaptcha::css_display( $css );
	}


	/**
	 * Whether the form has its own procaptcha set in admin.
	 *
	 * @param FluentForm|stdClass $form Form data and settings.
	 *
	 * @return bool
	 */
	protected function has_own_procaptcha( $form ): bool {
		FormFieldsParser::resetData();

		if ( FormFieldsParser::hasElement( $form, 'procaptcha' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get procaptcha.
	 *
	 * @return string
	 */
	private function get_captcha(): string {
		$args = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'id'     => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => $this->form_id,
			],
		];

		return Procaptcha::form( $args );
	}

	/**
	 * Get procaptcha wrapped as Fluent Forms field.
	 *
	 * @return string
	 */
	private function get_procaptcha_wrapped(): string {
		ob_start();

		?>
		<div class="ff-el-group">
			<div class="ff-el-input--content">
				<div data-fluent_id="1" name="procaptcha-response">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $this->get_captcha();
					?>
				</div>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}
}
