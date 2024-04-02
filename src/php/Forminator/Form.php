<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */

namespace Procaptcha\Forminator;

use Forminator_CForm_Front;
use Forminator_Front_Action;
use Procaptcha\Helpers\Procaptcha;
use Quform_Element_Page;
use Quform_Form;

/**
 * Class Form.
 */
class Form {

	/**
	 * Verify action.
	 */
	const ACTION = 'procaptcha_forminator';

	/**
	 * Verify nonce.
	 */
	const NONCE = 'procaptcha_forminator_nonce';

	/**
	 * Script handle.
	 */
	const HANDLE = 'procaptcha-forminator';

	/**
	 * Admin script handle.
	 */
	const ADMIN_HANDLE = 'admin-forminator';

	/**
	 * Script localization object.
	 */
	const OBJECT = 'ProcaptchaForminatorObject';

	/**
	 * Form id.
	 *
	 * @var int
	 */
	private $form_id = 0;

	/**
	 * Form has procaptcha field.
	 *
	 * @var bool
	 */
	private $has_procaptcha_field;

	/**
	 * Quform constructor.
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
		add_action( 'forminator_before_form_render', [ $this, 'before_form_render' ], 10, 5 );
		add_filter( 'forminator_render_button_markup', [ $this, 'add_procaptcha' ], 10, 2 );
		add_filter( 'forminator_cform_form_is_submittable', [ $this, 'verify' ], 10, 3 );

		add_action( 'procaptchaprint_procaptcha_scripts', [ $this, 'print_procaptcha_scripts' ] );

		add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_scripts' ], 9 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		add_filter( 'forminator_field_markup', [ $this, 'replace_procaptcha_field' ], 10, 3 );
	}

	/**
	 * Get form id before render.
	 *
	 * @param int|mixed $id            Form id.
	 * @param string    $form_type     Form type.
	 * @param int       $post_id       Post id.
	 * @param array     $form_fields   Form fields.
	 * @param array     $form_settings Form settings.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function before_form_render( $id, string $form_type, int $post_id, array $form_fields, array $form_settings ) {
		$this->has_procaptcha_field = $this->has_procaptcha_field( $form_fields );
		$this->form_id            = $id;
	}

	/**
	 * Add procaptcha.
	 *
	 * @param string|mixed $html   Shortcode output.
	 * @param string       $button Shortcode name.
	 *
	 * @return string|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_procaptcha( $html, string $button ) {
		if ( $this->has_procaptcha_field ) {
			return $html;
		}

		return str_replace( '<button ', $this->get_procaptcha() . '<button ', (string) $html );
	}

	/**
	 * Verify.
	 *
	 * @param array|mixed $can_show      Can show the form.
	 * @param int         $id            Form id.
	 * @param array       $form_settings Form settings.
	 *
	 * @return array|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function verify( $can_show, int $id, array $form_settings ) {
		$module_object = Forminator_Front_Action::$module_object;

		foreach ( $module_object->fields as $key => $field ) {
			if ( isset( $field->raw['captcha_provider'] ) && 'procaptcha' === $field->raw['captcha_provider'] ) {
				// Remove procaptcha field from the form to prevent it from verifying by Forminator.
				unset( $module_object->fields[ $key ] );
				break;
			}
		}

		$error_message = procaptcha_get_verify_message( self::NONCE, self::ACTION );

		if ( null !== $error_message ) {
			return [
				'can_submit' => false,
				'error'      => $error_message,
			];
		}

		return $can_show;
	}

	/**
	 * Filter print procaptcha scripts status and return true on Forminator form wizard page.
	 *
	 * @param bool|mixed $status Print scripts status.
	 *
	 * @return bool|mixed
	 */
	public function print_procaptcha_scripts( $status ) {
		$forminator_api_handle = 'forminator-procaptcha';

		wp_dequeue_script( $forminator_api_handle );
		wp_deregister_script( $forminator_api_handle );

		if ( $this->has_procaptcha_field ) {
			return true;
		}

		$is_forminator_wizard_page = $this->is_forminator_admin_page();

		return $is_forminator_wizard_page ? true : $status;
	}

	/**
	 * Enqueue frontend scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! procaptcha()->form_shown ) {
			return;
		}

		$min = procaptchamin_suffix();

		wp_enqueue_script(
			self::HANDLE,
			PROCAPTCHA_URL . "/assets/js/procaptcha-forminator$min.js",
			[],
			PROCAPTCHA_VERSION,
			true
		);
	}

	/**
	 * Enqueue script in admin.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		if ( ! $this->is_forminator_admin_page() ) {
			return;
		}

		$min = procaptchamin_suffix();

		wp_enqueue_script(
			self::ADMIN_HANDLE,
			constant( 'PROCAPTCHA_URL' ) . "/assets/js/admin-forminator$min.js",
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
			constant( 'PROCAPTCHA_URL' ) . "/assets/css/admin-forminator$min.css",
			[],
			constant( 'PROCAPTCHA_VERSION' )
		);
	}

	/**
	 * Replace Forminator procaptcha field.
	 *
	 * @param string|mixed           $html           Field html.
	 * @param array                  $field          Field.
	 * @param Forminator_CForm_Front $front_instance Forminator_CForm_Front instance.
	 *
	 * @return string|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function replace_procaptcha_field( $html, array $field, Forminator_CForm_Front $front_instance ) {
		if ( ! $this->is_procaptcha_field( $field ) ) {
			return $html;
		}

		return $this->get_procaptcha();
	}

	/**
	 * Get procaptcha.
	 *
	 * @return string
	 */
	private function get_procaptcha(): string {
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
	 * Whether we are on the Forminator admin pages.
	 *
	 * @return bool
	 */
	private function is_forminator_admin_page(): bool {
		if ( ! is_admin() ) {
			return false;
		}

		$screen = get_current_screen();

		if ( ! $screen ) {
			return false;
		}

		$forminator_admin_pages = [
			'forminator_page_forminator-cform',
			'forminator_page_forminator-cform-wizard',
			'forminator_page_forminator-settings',
		];

		return in_array( $screen->id, $forminator_admin_pages, true );
	}

	/**
	 * Whether the field is procaptcha field.
	 *
	 * @param array $field Field.
	 *
	 * @return bool
	 */
	private function is_procaptcha_field( array $field ): bool {
		return ( 'captcha' === $field['type'] && 'procaptcha' === $field['captcha_provider'] );
	}

	/**
	 * Whether for has its own procaptcha field.
	 *
	 * @param array $form_fields Form fields.
	 *
	 * @return bool
	 */
	private function has_procaptcha_field( array $form_fields ): bool {
		foreach ( $form_fields as $form_field ) {
			if ( $this->is_procaptcha_field( $form_field ) ) {
				return true;
			}
		}

		return false;
	}
}
