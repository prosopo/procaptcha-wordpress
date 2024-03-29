<?php
/**
 * Base class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\ColorlibCustomizer;

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

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_style( $procaptcha_size );
	}

	/**
	 * Get style.
	 *
	 * @param string $procaptcha_size procaptcha widget size.
	 *
	 * @return string
	 */
	abstract protected function get_style( string $procaptcha_size ): string;
}
