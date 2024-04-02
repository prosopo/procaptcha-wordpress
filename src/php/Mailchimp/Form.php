<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */

namespace Procaptcha\Mailchimp;

use Procaptcha\Helpers\Procaptcha;
use MC4WP_Form;
use MC4WP_Form_Element;

/**
 * Class Form.
 */
class Form {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_mailchimp';

	/**
	 * Nonce name.
	 */
	const NAME = 'procaptcha_mailchimp_nonce';

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
		add_filter( 'mc4wp_form_messages', [ $this, 'add_procaptchaerror_messages' ], 10, 2 );
		add_filter( 'mc4wp_form_content', [ $this, 'add_captcha' ], 20, 3 );
		add_filter( 'mc4wp_form_errors', [ $this, 'verify' ], 10, 2 );
	}

	/**
	 * Add procaptcha error messages to MailChimp.
	 *
	 * @param array|mixed $messages Messages.
	 * @param MC4WP_Form  $form     Form.
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_procaptchaerror_messages( $messages, MC4WP_Form $form ): array {
		$messages = (array) $messages;

		foreach ( procaptchaget_error_messages() as $error_code => $error_message ) {
			$messages[ $error_code ] = [
				'type' => 'error',
				'text' => $error_message,
			];
		}

		return $messages;
	}

	/**
	 * Add procaptcha to MailChimp form.
	 *
	 * @param string|mixed       $content Content.
	 * @param MC4WP_Form         $form    Form.
	 * @param MC4WP_Form_Element $element Element.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_captcha( $content, MC4WP_Form $form, MC4WP_Form_Element $element ): string {
		$args = [
			'action' => self::ACTION,
			'name'   => self::NAME,
			'id'     => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => $form->ID,
			],
		];

		return preg_replace(
			'/(<input .*?type="submit")/',
			Procaptcha::form( $args ) . '$1',
			(string) $content
		);
	}

	/**
	 * Verify MailChimp captcha.
	 *
	 * @param array|mixed $errors Errors.
	 * @param MC4WP_Form  $form   Form.
	 *
	 * @return array|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function verify( $errors, MC4WP_Form $form ) {
		$error_message = procaptcha_verify_post( self::NAME, self::ACTION );

		if ( null !== $error_message ) {
			$error_code = array_search( $error_message, procaptchaget_error_messages(), true ) ?: 'empty';
			$errors     = (array) $errors;
			$errors[]   = $error_code;
		}

		return $errors;
	}
}
