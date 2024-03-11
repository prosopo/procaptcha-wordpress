<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\WPForms;

use PROCAPTCHA\Helpers\PROCAPTCHA;

/**
 * Class Form.
 */
class Form {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_wpforms';

	/**
	 * Nonce name.
	 */
	const NAME = 'procaptcha_wpforms_nonce';

	/**
	 * Form constructor.
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
		add_action( 'wpforms_display_field_after', [ $this, 'add_captcha' ] );
		add_action( 'wpforms_process', [ $this, 'verify' ], 10, 3 );
	}

	/**
	 * Action that fires immediately before the submit button element is displayed.
	 *
	 * @link         https://wpforms.com/developers/wpforms_display_field_after/
	 *
	 * @param array|mixed $form_data Form data and settings.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_captcha( $form_data ) {
		$args = [
			'action' => self::ACTION,
			'name'   => self::NAME,
			'id'     => [
				'source'  => PROCAPTCHA::get_class_source( static::class ),
				'form_id' => (int) $form_data['id'],
			],
		];
        
		PROCAPTCHA::form_display( $args );
		
	}

	/**
	 * Action that fires during form entry processing after initial field validation.
	 *
	 * @link         https://wpforms.com/developers/wpforms_process/
	 *
	 * @param array $fields    Sanitized entry field: values/properties.
	 * @param array $entry     Original $_POST global.
	 * @param array $form_data Form data and settings.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function verify( array $fields, array $entry, array $form_data ) {
		print_r($fields);
		print_r($entry);
		print_r($form_data);
		$error_message = procaptcha_get_verify_message(
			self::NAME,
			self::ACTION
		);
		print_r($error_message);
		die('testing');

		if ( null !== $error_message ) {
			wpforms()->get( 'process' )->errors[ $form_data['id'] ]['footer'] = $error_message;
		}
	}
}
