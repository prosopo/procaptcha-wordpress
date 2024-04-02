<?php
/**
 * Login class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\Divi;

use Procaptcha\Abstracts\LoginBase;
use Procaptcha\Helpers\Procaptcha;
use WP_Error;
use WP_User;

/**
 * Class Login.
 */
class Login extends LoginBase {

	/**
	 * Login form shortcode tag.
	 */
	const TAG = 'et_pb_login';

	/**
	 * Init hooks.
	 */
	protected function init_hooks() {
		parent::init_hooks();

		add_filter( self::TAG . '_shortcode_output', [ $this, 'add_divi_captcha' ], 10, 2 );
	}

	/**
	 * Add procap_ to the login form.
	 *
	 * @param string|string[] $output      Module output.
	 * @param string          $module_slug Module slug.
	 *
	 * @return string|string[]
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function add_divi_captcha( $output, string $module_slug ) {
		if ( ! is_string( $output ) || et_core_is_fb_enabled() ) {
			// Do not add captcha in frontend builder.

			return $output;
		}

		if ( ! $this->is_login_limit_exceeded() ) {
			return $output;
		}

		$procaptcha = '';

		// Check the login status, because class is always loading when Divi theme is active.
		if ( procaptcha()->settings()->is( 'divi_status', 'login' ) ) {
			ob_start();

			$this->add_captcha();
			$procaptcha = (string) ob_get_clean();
		}

		ob_start();
		do_action( 'procap_signature' );
		$signatures = (string) ob_get_clean();

		$pattern     = '/(<p>[\s]*?<button)/';
		$replacement = $procaptcha . $signatures . "\n$1";

		// Insert procap_.
		return preg_replace( $pattern, $replacement, $output );
	}
}
