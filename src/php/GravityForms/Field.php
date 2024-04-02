<?php
/**
 * Field class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\GravityForms;

use GF_Field;
use GF_Fields;
use Exception;
use GFCommon;
use GFForms;
use GFFormsModel;
use Procaptcha\Helpers\Procaptcha;

/**
 * Class Field.
 */
class Field extends GF_Field {

	/**
	 * Admin script handle.
	 */
	const ADMIN_HANDLE = 'admin-gravity-forms';

	/**
	 * Editor screen id.
	 */
	const EDITOR_SCREEN_ID = 'toplevel_page_gf_edit_forms';

	/**
	 * Field type.
	 *
	 * @var string
	 */
	public $type = 'procaptcha';

	/**
	 * Constructor.
	 *
	 * @param array $data Data.
	 */
	public function __construct( $data = [] ) {
		parent::__construct( $data );

		$this->init();
	}

	/**
	 * Init class.
	 *
	 * @return void
	 */
	private function init() {
		if ( ! procaptcha()->settings()->is( 'gravity_status', 'embed' ) ) {
			return;
		}

		$this->label = 'procaptcha';

		try {
			GF_Fields::register( $this );
			// @codeCoverageIgnoreStart
		} catch ( Exception $e ) {
			return;
			// @codeCoverageIgnoreEnd
		}

		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_filter( 'gform_field_groups_form_editor', [ $this, 'add_to_field_groups' ] );
		add_filter( 'gform_duplicate_field_link', [ $this, 'disable_duplication' ] );
		add_action( 'admin_print_footer_scripts-' . self::EDITOR_SCREEN_ID, [ $this, 'enqueue_admin_script' ] );
		add_action( 'procaptchaprint_procaptcha_scripts', [ $this, 'print_procaptcha_scripts' ] );
	}

	/**
	 * Add procaptcha field to field groups.
	 *
	 * @param array $field_groups Field groups.
	 *
	 * @return array
	 */
	public function add_to_field_groups( array $field_groups ): array {
		$field_groups['advanced_fields']['fields'][] = [
			'data-type' => 'procaptcha',
			'value'     => 'procaptcha',
		];

		return $field_groups;
	}

	/**
	 * Get form editor field title.
	 *
	 * @return string
	 */
	public function get_form_editor_field_title(): string {
		return esc_attr( 'procaptcha' );
	}

	/**
	 * Returns the field's form editor description.
	 *
	 * @return string
	 */
	public function get_form_editor_field_description(): string {
		return (
			esc_attr__(
				'Adds a procaptcha field to your form to help protect your website from spam and bot abuse.',
				'procaptcha-wordpress'
			) .
			' ' .
			esc_attr__(
				'procaptcha settings must be modified on the procaptcha plugin General settings page.',
				'procaptcha-wordpress'
			)
		);
	}

	/**
	 * Returns the field's form editor icon.
	 *
	 * This could be an icon url or a gform-icon class.
	 *
	 * @return string
	 */
	public function get_form_editor_field_icon(): string {
		return PROCAPTCHA_URL . '/assets/images/procaptcha-icon-black-and-white.svg';
	}

	/**
	 * Get field settings.
	 *
	 * @return array
	 */
	public function get_form_editor_field_settings(): array {
		return [
			'label_placement_setting',
			'description_setting',
			'css_class_setting',
		];
	}

	/**
	 * Get field input.
	 *
	 * @param array $form  Form.
	 * @param mixed $value Value.
	 * @param mixed $entry Entry.
	 *
	 * @return string
	 * @noinspection PhpCastIsUnnecessaryInspection
	 */
	public function get_field_input( $form, $value = '', $entry = null ): string {
		$form_id         = (int) $form['id'];
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();
		$id              = (int) $this->id;
		$field_id        = $is_entry_detail || $is_form_editor || 0 === $form_id ? "input_$id" : 'input_' . $form_id . "_$id";
		$procaptcha_size   = procaptcha()->settings()->get( 'size' );
		$tabindex        = GFCommon::$tab_index > 0 ? GFCommon::$tab_index++ : 0;
		$tabindex        = 'invisible' === $procaptcha_size ? -1 : $tabindex;
		$search          = 'class="procaptcha"';

		$args = [
			'action' => Base::ACTION,
			'name'   => Base::NONCE,
			'id'     => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => $form_id,
			],
		];

		return str_replace(
			$search,
			$search . ' id="' . $field_id . '" data-tabindex="' . $tabindex . '"',
			Procaptcha::form( $args )
		);
	}

	/**
	 * Disable procaptcha field duplication.
	 *
	 * @param string $duplicate_field_link Duplicate link.
	 *
	 * @return string
	 */
	public function disable_duplication( string $duplicate_field_link ): string {
		$action = rgpost( 'action' );

		if ( 'rg_add_field' === $action ) {
			$field = json_decode( rgpost( 'field' ), false );
		} else {
			if ( ! preg_match( "/id='gfield_duplicate_(.*)?'/", $duplicate_field_link, $m ) ) {
				return $duplicate_field_link;
			}

			$form_id  = GFForms::get( 'id' );
			$field_id = $m[1];
			$field    = GFFormsModel::get_field( $form_id, $field_id );
		}

		$type = $field->type ?? '';

		return 'procaptcha' === $type ? '' : $duplicate_field_link;
	}

	/**
	 * Enqueue admin script.
	 *
	 * @return void
	 */
	public function enqueue_admin_script() {
		$min = procaptchamin_suffix();

		wp_enqueue_script(
			self::ADMIN_HANDLE,
			PROCAPTCHA_URL . "/assets/js/admin-gravity-forms$min.js",
			[],
			PROCAPTCHA_VERSION,
			true
		);

		wp_localize_script(
			self::ADMIN_HANDLE,
			'ProcaptchaGravityFormsObject',
			[
				'onlyOne' => __( 'Only one procaptcha field can be added to the form.', 'procaptcha-wordpress' ),
			]
		);
	}

	/**
	 * Print procaptcha script on form edit page.
	 *
	 * @param bool|mixed $status Current print status.
	 *
	 * @return bool
	 */
	public function print_procaptcha_scripts( $status ): bool {
		if ( ! function_exists( 'get_current_screen' ) ) {
			// @codeCoverageIgnoreStart
			return $status;
			// @codeCoverageIgnoreEnd
		}

		$screen    = get_current_screen();
		$screen_id = $screen->id ?? '';

		if ( self::EDITOR_SCREEN_ID === $screen_id ) {
			return true;
		}

		return $status;
	}
}
