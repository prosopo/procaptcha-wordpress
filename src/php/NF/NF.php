<?php
/**
 * NF form class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\NF;

use PROCAPTCHA\Helpers\PROCAPTCHA;
use PROCAPTCHA\Main;

/**
 * Class NF
 * Support Ninja Forms.
 */
class NF {

	/**
	 * Script handle.
	 */
	const HANDLE = 'procaptcha-nf';

	/**
	 * Admin script handle.
	 */
	const ADMIN_HANDLE = 'admin-nf';

	/**
	 * Form id.
	 *
	 * @var int
	 */
	private $form_id = 0;

	/**
	 * Templates dir.
	 *
	 * @var string
	 */
	private $templates_dir;

	/**
	 * NF constructor.
	 */
	public function __construct() {
		$this->templates_dir = __DIR__ . '/templates/';

		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 */
	public function init_hooks() {
		add_action( 'toplevel_page_ninja-forms', [ $this, 'admin_template' ], 11 );
		add_action( 'nf_admin_enqueue_scripts', [ $this, 'nf_admin_enqueue_scripts' ] );
		add_filter( 'ninja_forms_register_fields', [ $this, 'register_fields' ] );
		add_action( 'ninja_forms_loaded', [ $this, 'place_procaptcha_before_recaptcha_field' ] );
		add_filter( 'ninja_forms_field_template_file_paths', [ $this, 'template_file_paths' ] );
		add_action( 'nf_get_form_id', [ $this, 'set_form_id' ] );
		add_filter( 'ninja_forms_localize_field_procaptcha-for-ninja-forms', [ $this, 'localize_field' ] );
		add_action( 'wp_print_footer_scripts', [ $this, 'nf_captcha_script' ], 9 );
	}

	/**
	 * Display template on form edit page.
	 *
	 * @return void
	 */
	public function admin_template() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['form_id'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$template = file_get_contents( $this->templates_dir . 'fields-procaptcha.html' );

		// Fix bug in Ninja forms.
		// For template script id, they expect field->_name in admin, but field->_type on frontend.
		// It works for NF fields as all fields have _name === _type.

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo str_replace(
			'tmpl-nf-field-procaptcha',
			'tmpl-nf-field-procaptcha-for-ninja-forms',
			$template
		);
	}

	/**
	 * Add procaptcha to the field data.
	 *
	 * @return void
	 */
	public function nf_admin_enqueue_scripts() {
		global $wp_scripts;

		// Add procaptcha to the preloaded form data.
		$data = $wp_scripts->registered['nf-builder']->extra['data'];

		if ( ! preg_match( '/var nfDashInlineVars = (.+);/', $data, $m ) ) {
			return;
		}

		$vars  = json_decode( $m[1], true );
		$found = false;

		foreach ( $vars['preloadedFormData']['fields'] as & $field ) {
			if ( 'procaptcha-for-ninja-forms' === $field['type'] ) {
				$found             = true;
				$search            = 'class="procaptcha"';
				$field['procaptcha'] = str_replace(
					$search,
					$search . ' style="z-index: 2;"',
					$this->get_procaptcha( (int) $field['id'] )
				);
				break;
			}
		}

		unset( $field );

		if ( $found ) {
			$data = str_replace( $m[1], wp_json_encode( $vars ), $data );

			$wp_scripts->registered['nf-builder']->extra['data'] = $data;
		}

		// Enqueue admin script.
		$min = procap_min_suffix();

		wp_enqueue_script(
			self::ADMIN_HANDLE,
			PROCAPTCHA_URL . "/assets/js/admin-nf$min.js",
			[],
			PROCAPTCHA_VERSION,
			true
		);

		wp_localize_script(
			self::ADMIN_HANDLE,
			'PROCAPTCHAAdminNFObject',
			[
				'onlyOnePROCAPTCHAAllowed' => __( 'Only one procaptcha field allowed.', 'procaptcha-wordpress' ),
			]
		);
	}

	/**
	 * Filter ninja_forms_register_fields.
	 *
	 * @param array|mixed $fields Fields.
	 *
	 * @return array
	 */
	public function register_fields( $fields ): array {
		$fields = (array) $fields;

		$fields['procaptcha-for-ninja-forms'] = new Field();

		return $fields;
	}

	/**
	 * Place procaptcha field before recaptcha field.
	 *
	 * @return void
	 */
	public function place_procaptcha_before_recaptcha_field() {
		$fields = Ninja_Forms()->fields;
		$index  = array_search( 'recaptcha', array_keys( $fields ), true );

		if ( false === $index ) {
			return;
		}

		$procaptcha_key   = 'procaptcha-for-ninja-forms';
		$procaptcha_value = $fields[ $procaptcha_key ];

		unset( $fields[ $procaptcha_key ] );

		Ninja_Forms()->fields = array_merge(
			array_slice( $fields, 0, $index ),
			[ $procaptcha_key => $procaptcha_value ],
			array_slice( $fields, $index )
		);
	}

	/**
	 * Add a template file path.
	 *
	 * @param array|mixed $paths Paths.
	 *
	 * @return array
	 */
	public function template_file_paths( $paths ): array {
		$paths = (array) $paths;

		$paths[] = $this->templates_dir;

		return $paths;
	}

	/**
	 * Get form id.
	 *
	 * @param int $form_id Form id.
	 *
	 * @return void
	 */
	public function set_form_id( int $form_id ) {
		$this->form_id = $form_id;
	}

	/**
	 * Filter ninja_forms_localize_field_procaptcha-for-ninja-forms.
	 *
	 * @param array|mixed $field Field.
	 *
	 * @return array
	 */
	public function localize_field( $field ): array {
		$field = (array) $field;

		$field['settings']['procaptcha'] = $field['settings']['procaptcha'] ?? $this->get_procaptcha( (int) $field['id'] );

		return $field;
	}

	/**
	 * Get procaptcha.
	 *
	 * @param int $field_id Field id.
	 *
	 * @return string
	 */
	private function get_procaptcha( int $field_id ): string {
		$procaptcha_id = uniqid( 'procaptcha-nf-', true );

		// Nonce is checked by Ninja forms.
		$args = [
			'id' => [
				'source'  => PROCAPTCHA::get_class_source( __CLASS__ ),
				'form_id' => $this->form_id,
			],
		];

		$procaptcha = PROCAPTCHA::form( $args );

		return str_replace(
			'<div',
			'<div id="' . $procaptcha_id . '" data-fieldId="' . $field_id . '"',
			$procaptcha
		);
	}

	/**
	 * Enqueue script.
	 *
	 * @return void
	 */
	public function nf_captcha_script() {
		$min = procap_min_suffix();

		wp_enqueue_script(
			self::HANDLE,
			PROCAPTCHA_URL . "/assets/js/procaptcha-nf$min.js",
			[ 'jquery', Main::HANDLE, 'nf-front-end', 'nf-front-end-deps' ],
			PROCAPTCHA_VERSION,
			true
		);
	}
}
