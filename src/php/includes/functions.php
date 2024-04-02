<?php
/**
 * Functions file.
 *
 * @package procaptcha-wp
 */

use Procaptcha\Helpers\Procaptcha;

/**
 * Get procaptcha form.
 *
 * @param string $action Action name for wp_nonce_field.
 * @param string $name   Nonce name for wp_nonce_field.
 * @param bool   $auto   This form has to be auto-verified.
 *
 * @return string
 * @deprecated 2.7.0 Use \Procaptcha\Helpers\Procaptcha::form()
 */
function procaptchaform( string $action = '', string $name = '', bool $auto = false ): string {
	// @codeCoverageIgnoreStart
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	_deprecated_function( __FUNCTION__, '2.7.0', Procaptcha::class . '::form()' );

	$args = [
		'action' => $action,
		'name'   => $name,
		'auto'   => $auto,
	];

	return Procaptcha::form( $args );
	// @codeCoverageIgnoreEnd
}

/**
 * Display procaptcha form.
 *
 * @param string $action Action name for wp_nonce_field.
 * @param string $name   Nonce name for wp_nonce_field.
 * @param bool   $auto   This form has to be auto-verified.
 *
 * @deprecated 2.7.0 Use \Procaptcha\Helpers\Procaptcha::form_display()
 */
function procaptchaform_display( string $action = '', string $name = '', bool $auto = false ) {
	// @codeCoverageIgnoreStart
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	_deprecated_function( __FUNCTION__, '2.7.0', Procaptcha::class . '::form_display()' );

	$args = [
		'action' => $action,
		'name'   => $name,
		'auto'   => $auto,
	];

	Procaptcha::form_display( $args );
	// @codeCoverageIgnoreEnd
}

/**
 * Display procaptcha shortcode.
 *
 * @param array|string $atts procaptcha shortcode attributes.
 *
 * @return string
 */
function procaptchashortcode( $atts ): string {
	/**
	 * Do not set the default size here.
	 * If size is not normal|compact|invisible, it will be taken from plugin settings in Procaptcha::form().
	 */
	$atts = shortcode_atts(
		[
			'action'  => PROCAPTCHA_ACTION,
			'name'    => PROCAPTCHA_NONCE,
			'auto'    => false,
			'force'   => false,
			'size'    => '',
			'id'      => [],
			'protect' => true,
		],
		$atts
	);

	/**
	 * Filters the content of the procaptcha form.
	 *
	 * @param string $form The procaptcha form.
	 */
	return (string) apply_filters( 'procaptchaprocaptcha_content', Procaptcha::form( $atts ) );
}

add_shortcode( 'procaptcha', 'procaptchashortcode' );

// @codeCoverageIgnoreStart
if ( ! function_exists( 'wp_doing_ajax' ) ) :
	/**
	 * Determines whether the current request is a WordPress Ajax request.
	 *
	 * @since 4.7.0
	 *
	 * @return bool True if it's a WordPress Ajax request, false otherwise.
	 */
	function wp_doing_ajax() {
		/**
		 * Filters whether the current request is a WordPress Ajax request.
		 *
		 * @since 4.7.0
		 *
		 * @param bool $wp_doing_ajax Whether the current request is a WordPress Ajax request.
		 */
		return apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX );
	}
endif;
// @codeCoverageIgnoreEnd

/**
 * Get min suffix.
 *
 * @return string
 */
function procaptchamin_suffix(): string {
	return defined( 'SCRIPT_DEBUG' ) && constant( 'SCRIPT_DEBUG' ) ? '' : '.min';
}
