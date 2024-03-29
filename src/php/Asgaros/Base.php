<?php
/**
 * Base class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\Asgaros;

use PROCAPTCHA\Helpers\PROCAPTCHA;

/**
 * Class Base.
 */
abstract class Base {

	/**
	 * Base constructor.
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
		add_filter( static::ADD_CAPTCHA_HOOK, [ $this, 'add_captcha' ], 10, 4 );
		add_filter( static::VERIFY_HOOK, [ $this, 'verify' ] );
	}

	/**
	 * Add captcha to the new topic form.
	 *
	 * @param string|mixed $output Shortcode output.
	 * @param string       $tag    Shortcode name.
	 * @param array|string $attr   Shortcode attributes array or empty string.
	 * @param array        $m      Regular expression match array.
	 *
	 * @return string|mixed
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection RegExpUnnecessaryNonCapturingGroup
	 */
	public function add_captcha( $output, string $tag, $attr, array $m ) {
		if ( 'forum' !== $tag ) {
			return $output;
		}

		$form_id = isset( $attr['id'] ) ? (int) $attr['id'] : 0;
		$search  = '<div class="editor-row editor-row-submit">';
		$args    = [
			'action' => static::ACTION,
			'name'   => static::NAME,
			'id'     => [
				'source'  => PROCAPTCHA::get_class_source( static::class ),
				'form_id' => $form_id,
			],
		];

		return str_replace(
			$search,
			'<div class="editor-row editor-row-procaptcha">' .
			'<div class="right">' .
			PROCAPTCHA::form( $args ) .
			'</div>' .
			'</div>' .
			$search,
			(string) $output
		);
	}

	/**
	 * Verify new topic captcha.
	 *
	 * @param bool|mixed $verified Verified.
	 *
	 * @return bool|mixed
	 */
	public function verify( $verified ) {
		global $asgarosforum;

		$error_message = procaptcha_get_verify_message(
			static::NAME,
			static::ACTION
		);

		if ( null !== $error_message ) {
			$asgarosforum->add_notice( $error_message );

			return false;
		}

		return $verified;
	}
}
