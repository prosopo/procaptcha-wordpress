<?php
/**
 * ProcaptchaHandler class file.
 *
 * @package procaptcha-wp
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedMethodInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\ElementorPro;

use Elementor\Controls_Stack;
use Elementor\Plugin;
use Elementor\Widget_Base;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;
use ElementorPro\Modules\Forms\Classes\Form_Record;
use ElementorPro\Modules\Forms\Module;
use Procaptcha\Helpers\Procaptcha;
use Procaptcha\Main;

/**
 * Class ProcaptchaHandler.
 */
class ProcaptchaHandler {

	const OPTION_NAME_SITE_KEY   = 'site_key';
	const OPTION_NAME_SECRET_KEY = 'secret_key';
	const OPTION_NAME_THEME      = 'theme';
	const OPTION_NAME_SIZE       = 'size';
	const FIELD_ID               = 'procaptcha';
	const HANDLE                 = 'procaptcha-elementor-pro';
	const ADMIN_HANDLE           = 'admin-elementor-pro';
	const PROCAPTCHA_HANDLE        = 'procaptcha';

	/**
	 * Main class instance.
	 *
	 * @var Main
	 */
	protected $main;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->main = procaptcha();

		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'after_enqueue_scripts' ] );
		add_action( 'elementor/init', [ $this, 'init' ] );

		add_action( 'wp_print_footer_scripts', [ $this, 'print_footer_scripts' ], 9 );
		add_action( 'wp_head', [ $this, 'print_inline_styles' ], 20 );
	}

	/**
	 * Enqueue elementor support script.
	 *
	 * @return void
	 */
	public function after_enqueue_scripts() {
		$min = procaptchamin_suffix();

		wp_enqueue_script(
			self::ADMIN_HANDLE,
			PROCAPTCHA_URL . "/assets/js/admin-elementor-pro$min.js",
			[ 'elementor-editor' ],
			PROCAPTCHA_VERSION,
			true
		);
	}

	/**
	 * Add hooks.
	 *
	 * @return void
	 */
	public function init() {
		$this->register_scripts();

		add_action( 'elementor_pro/forms/register/action', [ $this, 'register_action' ] );

		add_filter( 'elementor_pro/forms/field_types', [ $this, 'add_field_type' ] );
		add_action(
			'elementor/element/form/section_form_fields/after_section_end',
			[ $this, 'modify_controls' ],
			10,
			2
		);
		add_action(
			'elementor_pro/forms/render_field/' . static::get_procaptcha_name(),
			[ $this, 'render_field' ],
			10,
			3
		);
		add_filter( 'elementor_pro/forms/render/item', [ $this, 'filter_field_item' ] );
		add_filter( 'elementor_pro/editor/localize_settings', [ $this, 'localize_settings' ] );

		if ( static::is_enabled() ) {
			add_action( 'elementor_pro/forms/validation', [ $this, 'validation' ], 10, 2 );
			add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		}
	}

	/**
	 * Register action.
	 *
	 * @param Module $module Module.
	 *
	 * @return void
	 */
	public function register_action( Module $module ) {
		$module->add_component( self::FIELD_ID, $this );
	}

	/**
	 * Get procaptcha field name.
	 *
	 * @return string
	 */
	protected static function get_procaptcha_name(): string {
		return self::FIELD_ID;
	}

	/**
	 * Get a site key.
	 *
	 * @return array|string
	 */
	public static function get_site_key() {
		return procaptcha()->settings()->get( self::OPTION_NAME_SITE_KEY );
	}

	/**
	 * Get secret key.
	 *
	 * @return array|string
	 */
	public static function get_secret_key() {
		return procaptcha()->settings()->get( self::OPTION_NAME_SECRET_KEY );
	}

	/**
	 * Get procaptcha theme.
	 *
	 * @return array|string
	 */
	public static function get_procaptcha_theme() {
		return procaptcha()->settings()->get( self::OPTION_NAME_THEME );
	}

	/**
	 * Get procaptcha size.
	 *
	 * @return array|string
	 */
	public static function get_procaptcha_size() {
		return procaptcha()->settings()->get( self::OPTION_NAME_SIZE );
	}

	/**
	 * Get a setup message.
	 *
	 * @return string
	 */
	public static function get_setup_message(): string {
		return __( 'To use procaptcha, you need to add the Site and Secret keys.', 'procaptcha-wordpress' );
	}

	/**
	 * Is field enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled(): bool {
		return static::get_site_key() && static::get_secret_key();
	}

	/**
	 * Localize settings.
	 *
	 * @param array|mixed $settings Settings.
	 *
	 * @return array
	 */
	public function localize_settings( $settings ): array {
		$settings = (array) $settings;

		return array_replace_recursive(
			$settings,
			[
				'forms' => [
					static::get_procaptcha_name() => [
						'enabled'        => static::is_enabled(),
						'site_key'       => static::get_site_key(),
						'procaptcha_theme' => static::get_procaptcha_theme(),
						'procaptcha_size'  => static::get_procaptcha_size(),
						'setup_message'  => static::get_setup_message(),
					],
				],
			]
		);
	}

	/**
	 * Get a script handle.
	 *
	 * @return string
	 */
	protected static function get_script_handle(): string {
		return 'elementor-' . static::get_procaptcha_name() . '-api';
	}

	/**
	 * Register scripts.
	 */
	private function register_scripts() {
		$src = $this->main->get_api_src();
		$min = procaptchamin_suffix();

		wp_register_script(
			static::get_script_handle(),
			$src,
			[],
			PROCAPTCHA_VERSION,
			true
		);

		wp_register_script(
			self::PROCAPTCHA_HANDLE,
			PROCAPTCHA_URL . '/assets/js/apps/procaptcha.js',
			[],
			PROCAPTCHA_VERSION,
			true
		);

		wp_register_script(
			self::HANDLE,
			PROCAPTCHA_URL . "/assets/js/procaptcha-elementor-pro$min.js",
			[ 'jquery', self::PROCAPTCHA_HANDLE ],
			PROCAPTCHA_VERSION,
			true
		);
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$this->main->print_inline_styles();
		wp_enqueue_script( static::get_script_handle() );
		wp_enqueue_script( self::PROCAPTCHA_HANDLE );
		wp_enqueue_script( self::HANDLE );
	}

	/**
	 * Field validation.
	 *
	 * @param Form_Record  $record       Record.
	 * @param Ajax_Handler $ajax_handler Ajax handler.
	 *
	 * @return void
	 */
	public function validation( Form_Record $record, Ajax_Handler $ajax_handler ) {
		$fields = $record->get_field( [ 'type' => static::get_procaptcha_name() ] );

		if ( empty( $fields ) ) {
			return;
		}

		$field = current( $fields );

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$procaptcha_response = isset( $_POST['procaptcha-response'] ) ?
			filter_var( wp_unslash( $_POST['procaptcha-response'] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ) :
			'';
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$result = procaptcha_request_verify( $procaptcha_response );

		if ( null !== $result ) {
			$ajax_handler->add_error( $field['id'], $result );

			return;
		}

		// If success - remove the field form list (don't send it in emails etc.).
		$record->remove_field( $field['id'] );
	}

	/**
	 * Render field.
	 *
	 * @param array       $item       Item.
	 * @param int         $item_index Item index.
	 * @param Widget_Base $widget     Widget.
	 *
	 * @return void
	 */
	public function render_field( array $item, int $item_index, Widget_Base $widget ) {
		$procaptcha_html = '<div class="elementor-field" id="form-field-' . $item['custom_id'] . '">';

		$this->add_render_attributes( $item, $item_index, $widget );

		$data    = $widget->get_raw_data();
		$form_id = $data['settings']['form_id'] ?? 0;

		$args = [
			'id' => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => $form_id,
			],
		];

		$procaptcha_html .=
			'<div class="elementor-procaptcha">' .
			Procaptcha::form( $args ) .
			'</div>';

		$procaptcha_html .= '</div>';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $procaptcha_html;
	}

	/**
	 * Add render attributes.
	 *
	 * @param array       $item       Item.
	 * @param int         $item_index Item index.
	 * @param Widget_Base $widget     Widget.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected function add_render_attributes( array $item, int $item_index, Widget_Base $widget ) {
		$widget->add_render_attribute(
			[
				static::get_procaptcha_name() . $item_index => [
					'class'        => 'elementor-procaptcha',
					'data-sitekey' => static::get_site_key(),
					'data-theme'   => static::get_procaptcha_theme(),
					'data-size'    => static::get_procaptcha_size(),
				],
			]
		);
	}

	/**
	 * Add a field type.
	 *
	 * @param array|mixed $field_types Field types.
	 *
	 * @return array
	 */
	public function add_field_type( $field_types ): array {
		$field_types = (array) $field_types;

		$field_types[ self::FIELD_ID ] = __( 'procaptcha', 'elementor-pro' );

		return $field_types;
	}

	/**
	 * After section end.
	 *
	 * Fires after Elementor section ends in the editor panel.
	 *
	 * The dynamic portions of the hook name, `$stack_name` and `$section_id`, refer to the section name and section
	 * ID, respectively.
	 *
	 * @param Controls_Stack $controls_stack The controls stack.
	 * @param array          $args           Section arguments.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function modify_controls( Controls_Stack $controls_stack, array $args ) {
		$control_id   = 'form_fields';
		$control_data = Plugin::$instance->controls_manager->get_control_from_stack(
			$controls_stack->get_unique_name(),
			$control_id
		);

		$term = [
			'name'     => 'field_type',
			'operator' => '!in',
			'value'    => [ self::FIELD_ID ],
		];

		$control_data['fields']['width']['conditions']['terms'][]    = $term;
		$control_data['fields']['required']['conditions']['terms'][] = $term;

		Plugin::$instance->controls_manager->update_control_in_stack(
			$controls_stack,
			$control_id,
			$control_data,
			[ 'recursive' => true ]
		);
	}

	/**
	 * Filter field item/
	 *
	 * @param array|mixed $item Item.
	 *
	 * @return array
	 */
	public function filter_field_item( $item ): array {
		$item = (array) $item;

		if ( isset( $item['field_type'] ) && static::get_procaptcha_name() === $item['field_type'] ) {
			$item['field_label'] = false;
		}

		return $item;
	}

	/**
	 * Add the procaptcha Elementor Pro script to footer.
	 *
	 * @return void
	 */
	public function print_footer_scripts() {
		$min = procaptchamin_suffix();

		wp_enqueue_script(
			self::HANDLE,
			PROCAPTCHA_URL . "/assets/js/procaptcha-elementor-pro$min.js",
			[ 'jquery', Main::HANDLE ],
			PROCAPTCHA_VERSION,
			true
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
	.elementor-field-type-procaptcha .elementor-field {
		background: transparent !important;
	}

	.elementor-field-type-procaptcha .procaptcha {
		margin-bottom: unset;
	}
CSS;

		Procaptcha::css_display( $css );
	}
}
