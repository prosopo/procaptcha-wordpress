<?php
/**
 * Base class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\ColorlibCustomizer;

use Procaptcha\Helpers\Procaptcha;

/**
 * Class Login
 */
abstract class Base {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 */
	protected function init_hooks() {
		add_action( 'login_head', [ $this, 'login_head' ] );
	}

	/**
	 * Print styles to fit procaptcha widget to the login form.
	 *
	 * @return void
	 */
	public function login_head() {
		$procaptcha_size = procaptcha()->settings()->get( 'size' );

		if ( 'invisible' === $procaptcha_size ) {
			return;
		}

		Procaptcha::css_display( $this->get_style( $procaptcha_size ) );
	}

	/**
	 * Get style.
	 *
	 * @param string $procaptcha_size procap_ widget size.
	 *
	 * @return string
	 * @noinspection CssUnusedSymbol
	 */
	protected function get_style( string $procaptcha_size ): string {
		static $style_shown;

		if ( $style_shown ) {
			return '';
		}

		$style_shown = true;
		$css         = '';

		if ( 'normal' === $procaptcha_size ) {
			$css = <<<CSS
	.ml-container #login {
		min-width: 350px;
	}
CSS;
		}

		return $css;
	}
}
