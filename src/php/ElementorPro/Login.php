<?php
/**
 * Login class file.
 *
 * @package procaptcha-wp
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\ElementorPro;

use Elementor\Element_Base;
use Procaptcha\Abstracts\LoginBase;
use Procaptcha\Helpers\Procaptcha;

/**
 * Class Login.
 */
class Login extends LoginBase {

	/**
	 * Init hooks.
	 */
	protected function init_hooks() {
		parent::init_hooks();

		add_action( 'elementor/frontend/widget/before_render', [ $this, 'before_render' ] );
		add_action( 'elementor/frontend/widget/after_render', [ $this, 'add_elementor_login_procaptcha' ] );

		add_action( 'wp_head', [ $this, 'print_inline_styles' ] );
	}

	/**
	 * Before frontend element render.
	 *
	 * @param Element_Base $element The element.
	 *
	 * @return void
	 */
	public function before_render( Element_Base $element ) {
		if ( ! is_a( $element, \ElementorPro\Modules\Forms\Widgets\Login::class ) ) {
			return;
		}

		ob_start();
	}

	/**
	 * After frontend element render.
	 *
	 * @param Element_Base $element The element.
	 *
	 * @return void
	 */
	public function add_elementor_login_procaptcha( Element_Base $element ) {
		if ( ! is_a( $element, \ElementorPro\Modules\Forms\Widgets\Login::class ) ) {
			return;
		}

		$form = (string) ob_get_clean();

		if ( ! $this->is_login_limit_exceeded() ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $form;

			return;
		}

		$procaptcha = '';

		// Check the login status, because class is always loading when Elementor Pro is active.
		if ( procaptcha()->settings()->is( 'elementor_pro_status', 'login' ) ) {
			ob_start();
			$this->add_captcha();

			$procaptcha = (string) ob_get_clean();
			$procaptcha = '<div class="elementor-field-group elementor-column elementor-col-100">' . $procaptcha . '</div>';
		}

		ob_start();
		do_action( 'procap_signature' );
		$signatures = (string) ob_get_clean();

		$pattern     = '/(<div class="elementor-field-group.+<button type="submit")/s';
		$replacement = $procaptcha . $signatures . "\n$1";
		$form        = preg_replace( $pattern, $replacement, $form );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $form;
	}

	/**
	 * Print inline styles.
	 *
	 * @return void
	 * @noinspection CssUnusedSymbol
	 */
	public function print_inline_styles() {
		$css = <<<CSS
	.elementor-widget-login .procaptcha {
		margin-bottom: 0;
	}
CSS;

		Procaptcha::css_display( $css );
	}
}
