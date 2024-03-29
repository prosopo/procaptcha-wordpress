<?php
/**
 * Base class file.
 *
 * @package procaptcha-wp
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */

namespace PROCAPTCHA\BeaverBuilder;

use FLBuilderModule;
use PROCAPTCHA\Abstracts\LoginBase;
use PROCAPTCHA\Helpers\PROCAPTCHA;

/**
 * Class Base.
 */
abstract class Base extends LoginBase {
	/**
	 * Script handle.
	 */
	const HANDLE = 'procaptcha-beaver-builder';

	/**
	 * Add hooks.
	 *
	 * @return void
	 */
	protected function init_hooks() {
		add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_scripts' ], 9 );
	}

	/**
	 * Add procaptcha.
	 *
	 * @param string                $out    Button html.
	 * @param FLBuilderModule|mixed $module Button module.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected function add_procap_form( string $out, $module ): string {
		$form_id = false !== strpos( static::ACTION, 'login' ) ? 'login' : 'contact';
		$args    = [
			'action' => static::ACTION,
			'name'   => static::NONCE,
			'id'     => [
				'source'  => PROCAPTCHA::get_class_source( static::class ),
				'form_id' => $form_id,
			],
		];

		$procaptcha =
			'<div class="fl-input-group fl-procaptcha">' .
			PROCAPTCHA::form( $args ) .
			'</div>';

		$button_pattern = '<div class="fl-button-wrap';

		return str_replace( $button_pattern, $procaptcha . $button_pattern, $out );
	}

	/**
	 * Enqueue Beaver Builder script.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! procaptcha()->form_shown ) {
			return;
		}

		$min = procap_min_suffix();

		wp_enqueue_script(
			self::HANDLE,
			PROCAPTCHA_URL . "/assets/js/procaptcha-beaver-builder$min.js",
			[ 'jquery' ],
			PROCAPTCHA_VERSION,
			true
		);
	}
}
