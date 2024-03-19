<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */

namespace PROCAPTCHA\FormidableForms;

use FrmAppHelper;
use PROCAPTCHA\Helpers\PROCAPTCHA;

/**
 * Class Form.
 */
class Form {

	/**
	 * Verify action.
	 */
	const ACTION = 'procaptcha_formidable_forms';

	/**
	 * Verify nonce.
	 */
	const NONCE = 'procaptcha_formidable_forms_nonce';

	/**
	 * The procaptcha field id.
	 *
	 * @var int|string
	 */
	private $procaptcha_field_id;

	/**
	 * Class constructor.
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
		add_filter( 'transient_frm_options', [ $this, 'get_transient' ], 10, 2 );
		add_filter( 'frm_replace_shortcodes', [ $this, 'add_captcha' ], 10, 3 );
		//add_filter( 'frm_is_field_hidden', [ $this, 'prevent_native_validation' ], 10, 3 );
		add_filter( 'frm_validate_entry', [ $this, 'verify' ], 10, 3 );
		add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_scripts' ], 9 );
	}

	/**
	 * Use this plugin settings for procaptcha in Formidable Forms.
	 *
	 * @param mixed  $value     Value of transient.
	 * @param string $transient Transient name.
	 *
	 * @return mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function get_transient( $value, string $transient ) {
		if (
			! $value ||
			( isset( $value->active_captcha ) && 'procaptcha' !== $value->active_captcha )
		) {
			return $value;
		}

		$settings                = procaptcha()->settings();
		$value->procaptcha_pubkey  = $settings->get_site_key();
		

		return $value;
	}

	/**
	 * Filter field html created and add procaptcha.
	 *
	 * @param string|mixed $html  Html code of the field.
	 * @param array        $field Field.
	 * @param array        $atts  Attributes.
	 *
	 * @return string|mixed
	 */
	public function add_captcha( $html, array $field, array $atts ) {
	

		if ( 'captcha' !== $field['type'] ) {
			return $html;
		}
		
		$frm_settings = FrmAppHelper::get_settings();
		

		// if ( 'recaptcha' === $frm_settings->active_captcha ) {
			
		// 	return $html;
		// }

		// <div id="field_5l59" class="procaptcha" data-sitekey="ead4f33b-cd8a-49fb-aa16-51683d9cffc8"></div>
		//print_r($html);

		// if ( ! preg_match( '#<div id="(.+)" class="procaptcha" .+></div>#', (string) $html, $m ) ) {
		// 	die('testing');
		// 	return $html;
		// }

		//list( $captcha_div, $div_id ) = $m;

		$args = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'id'     => [
				'source'  => PROCAPTCHA::get_class_source( static::class ),
				'form_id' => (int) $atts['form']->id,
			],
		];

		$class = 'class="procaptcha"';
		$form  = PROCAPTCHA::form( $args );

		return $form;
	}

	/**
	 * Prevent native validation.
	 *
	 * @param bool|mixed $is_field_hidden Whether the field is hidden.
	 * @param stdClass   $field           Field.
	 * @param array      $post            wp_unslash( $_POST ) content.
	 *
	 * @return bool|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function prevent_native_validation( $is_field_hidden, stdClass $field, array $post ): bool {
		if ( 'captcha' !== $field->type ) {
			return $is_field_hidden;
		}

		$frm_settings = FrmAppHelper::get_settings();

		if ( 'recaptcha' === $frm_settings->active_captcha ) {
			return $is_field_hidden;
		}

		$this->procaptcha_field_id = $field->id;

		// Prevent validation of procaptcha in Formidable Forms.
		return true;
	}

	/**
	 * Verify.
	 *
	 * @param array|mixed $errors        Errors data.
	 * @param array       $values        Value data of the form.
	 * @param array       $validate_args Custom arguments. Contains `exclude` and `posted_fields`.
	 *
	 * @return array|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function verify( $errors, array $values, array $validate_args ) {
		$error_message = procaptcha_verify_post(
			self::NONCE,
			self::ACTION
		);

		if ( null === $error_message ) {
			return $errors;
		}

		$errors = (array) $errors;

		$field_id                      = $this->procaptcha_field_id ?: 1;
		$errors[ 'field' . $field_id ] = $error_message;

		return $errors;
	}

	/**
	 * Dequeue procaptcha script by Formidable Forms.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_dequeue_script( 'captcha-api' );
		wp_deregister_script( 'captcha-api' );
	}
}
