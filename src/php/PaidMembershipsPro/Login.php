<?php
/**
 * Login class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\PaidMembershipsPro;

use Procaptcha\Abstracts\LoginBase;
use Procaptcha\Helpers\Procaptcha;
use WP_Error;
use WP_User;

/**
 * Class Login.
 */
class Login extends LoginBase {

	/**
	 * Init hooks.
	 */
	protected function init_hooks() {
		parent::init_hooks();

		add_filter( 'pmpro_pages_shortcode_login', [ $this, 'add_pmpro_captcha' ] );
	}

	/**
	 * Add captcha.
	 *
	 * @param string|mixed $content Content of the PMPro login page.
	 *
	 * @return string|mixed
	 */
	public function add_pmpro_captcha( $content ) {
		if ( ! $this->is_login_limit_exceeded() ) {
			return $content;
		}

		$content = (string) $content;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ?
			sanitize_text_field( wp_unslash( $_GET['action'] ) ) :
			'';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$error_messages = procaptchaget_error_messages();

		if ( array_key_exists( $action, $error_messages ) ) {
			$search        = '<div class="pmpro_login_wrap">';
			$error_message = '<div class="pmpro_message pmpro_error">' . $error_messages[ $action ] . '</div>';
			$content       = str_replace( $search, $error_message . $search, $content );
		}

		$procaptcha = '';

		// Check the login status because class is always loading when PMPro is active.
		if ( procaptcha()->settings()->is( 'paid_memberships_pro_status', 'login' ) ) {
			ob_start();
			$this->add_captcha();

			$procaptcha = (string) ob_get_clean();
		}

		ob_start();
		do_action( 'procaptchasignature' );
		$signatures = (string) ob_get_clean();

		$search = '<p class="login-submit">';

		return str_replace( $search, $procaptcha . $signatures . $search, $content );
	}
}
