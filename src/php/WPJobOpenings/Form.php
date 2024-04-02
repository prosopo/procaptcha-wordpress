<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\WPJobOpenings;

use Procaptcha\Helpers\Procaptcha;

/**
 * Class Form.
 */
class Form {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_wp_job_openings';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_wp_job_openings_nonce';

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
		add_action( 'before_awsm_application_form', [ $this, 'before_application_form' ] );
		add_action( 'after_awsm_application_form', [ $this, 'add_captcha' ] );
		add_action( 'awsm_job_application_submitting', [ $this, 'verify' ] );
	}

	/**
	 * Before application form.
	 *
	 * @param array|mixed $form_attrs Form attributes.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function before_application_form( $form_attrs ) {
		ob_start();
	}

	/**
	 * Add captcha.
	 *
	 * @param array|mixed $form_attrs Form attributes.
	 *
	 * @return void
	 */
	public function add_captcha( $form_attrs ) {
		$html = ob_get_clean();

		$args = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'id'     => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => $form_attrs['job_id'] ?? 0,
			],
		];

		$html = preg_replace(
			'#(<div class="awsm-job-form-group">\s*?<input type="submit")#',
			'<div class="awsm-job-form-group">' . Procaptcha::form( $args ) . "</div>\n$1",
			$html
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}

	/**
	 * Verify captcha.
	 */
	public function verify() {
		global $awsm_response;

		$error_message = procaptcha_verify_post( self::NONCE, self::ACTION );

		if ( null !== $error_message ) {
			$awsm_response['error'][] = esc_html( $error_message );
		}
	}
}
