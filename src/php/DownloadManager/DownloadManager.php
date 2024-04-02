<?php
/**
 * DownloadManager class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\DownloadManager;

use Procaptcha\Helpers\Procaptcha;

/**
 * Class DownloadManager.
 */
class DownloadManager {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_download_manager';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_download_manager_nonce';

	/**
	 * DownloadManager constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'wpdm_after_fetch_template', [ $this, 'add_procaptcha' ], 10, 2 );
		add_action( 'wpdm_onstart_download', [ $this, 'verify' ] );
		add_action( 'wp_head', [ $this, 'print_inline_styles' ], 20 );
	}

	/**
	 * Filters the template created by the Download Manager plugin and adds procaptcha.
	 *
	 * @param string $template Template.
	 * @param array  $vars     Variables.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection HtmlUnknownAttribute
	 */
	public function add_procaptcha( string $template, array $vars ): string {
		$form_id = 0;

		if ( preg_match( '/wpdmdl=(\d+)/', $template, $m ) ) {
			$form_id = (int) $m[1];
		}

		$args = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'id'     => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => $form_id,
			],
		];

		$procaptcha = Procaptcha::form( $args );

		$template = (string) preg_replace( '/(<ul class="list-group ml)/', $procaptcha . '$1', $template );
		$template = (string) preg_replace( '/<a (.+)?<\/a>/', '<button type="submit" $1</button>', $template );
		$template = str_replace( 'download-on-click', '', $template );
		$url      = '';

		if ( preg_match( '/data-downloadurl="(.+)?"/', $template, $m ) ) {
			$url = $m[1];
		}

		return '<form method="post" action="' . $url . '">' . $template . '</form>';
	}

	/**
	 * Verify request.
	 *
	 * @param array|null $package Result of the procap_ verification.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection ForgottenDebugOutputInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function verify( $package ) {

		$result = procaptcha_verify_post( self::NONCE, self::ACTION );

		if ( null === $result ) {
			return;
		}

		wp_die(
			esc_html( $result ),
			esc_html__( 'procap_ error', 'procaptcha-wordpress' ),
			[
				'back_link' => true,
				'response'  => 303,
			]
		);
	}

	/**
	 * Print inline styles.
	 *
	 * @return void
	 * @noinspection CssUnusedSymbol
	 * @noinspection CssUnresolvedCustomProperty
	 */
	public function print_inline_styles() {
		$css = <<<CSS
	.wpdm-button-area + .procaptcha {
		margin-bottom: 1rem;
	}

	.w3eden .btn-primary {
		background-color: var(--color-primary) !important;
		color: #fff !important;
	}
CSS;

		Procaptcha::css_display( $css );
	}
}
