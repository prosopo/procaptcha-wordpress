<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedFunctionInspection */

namespace Procaptcha\WPForms;

use Procaptcha\Helpers\Procaptcha;

/**
 * Class Form.
 */
class Form {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_wpforms';

	/**
	 * Nonce name.
	 */
	const NAME = 'procaptcha_wpforms_nonce';

	/**
	 * Whether procap_ should be auto-added to any form.
	 *
	 * @var bool
	 */
	private $mode_auto = false;

	/**
	 * Whether procap_ can be embedded into form in the WPForms form editor.
	 * WPForms settings are blocked in this case.
	 *
	 * @var bool
	 */
	private $mode_embed = false;

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
	protected function init_hooks() {
		$this->mode_auto  = procaptcha()->settings()->is( 'wpforms_status', 'form' );
		$this->mode_embed =
			procaptcha()->settings()->is( 'wpforms_status', 'embed' ) &&
			$this->is_wpforms_provider_procaptcha();

		if ( ! $this->mode_auto && ! $this->mode_embed ) {
			return;
		}

		if ( $this->mode_embed ) {
			add_filter( 'wpforms_admin_settings_captcha_enqueues_disable', [ $this, 'wpforms_admin_settings_captcha_enqueues_disable' ] );
			add_filter( 'procap_print_procaptcha_scripts', [ $this, 'procap_print_procaptcha_scripts' ] );
			add_filter( 'wpforms_settings_fields', [ $this, 'wpforms_settings_fields' ], 10, 2 );
		}

		add_action( 'wp_head', [ $this, 'print_inline_styles' ], 20 );
		add_action( 'wpforms_wp_footer', [ $this, 'block_assets_recaptcha' ], 0 );

		add_action( 'wpforms_frontend_output', [ $this, 'wpforms_frontend_output' ], 19, 5 );
		add_filter( 'wpforms_process_bypass_captcha', '__return_true' );
		add_action( 'wpforms_process', [ $this, 'verify' ], 10, 3 );
	}

	/**
	 * Action that fires during form entry processing after initial field validation.
	 *
	 * @link         https://wpforms.com/developers/wpforms_process/
	 *
	 * @param array $fields    Sanitized entry field: values/properties.
	 * @param array $entry     Original $_POST global.
	 * @param array $form_data Form data and settings.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function verify( array $fields, array $entry, array $form_data ) {
		if ( ! $this->process_procaptcha( $form_data ) ) {
			return;
		}

		$wpforms_error_message = '';

		if ( ! $this->mode_embed && $this->form_has_procaptcha( $form_data ) ) {
			$this->use_wpforms_settings();

			$wpforms_error_message = wpforms_setting( 'procaptcha-fail-msg' );
		}

		$error_message = procaptcha_get_verify_message(
			self::NAME,
			self::ACTION
		);

		if ( null !== $error_message ) {
			wpforms()->get( 'process' )->errors[ $form_data['id'] ]['footer'] = $wpforms_error_message ?: $error_message;
		}
	}

	/**
	 * Print inline styles.
	 *
	 * @return void
	 * @noinspection CssUnusedSymbol
	 */
	public function print_inline_styles() {
		$css = <<<CSS
	div.wpforms-container-full .wpforms-form .procaptcha {
		position: relative;
		display: block;
		margin-bottom: 0;
		padding: 0;
		clear: both;
	}

	div.wpforms-container-full .wpforms-form .procaptcha[data-size="normal"] {
		width: 303px;
		height: 78px;
	}
	
	div.wpforms-container-full .wpforms-form .procaptcha[data-size="compact"] {
		width: 164px;
		height: 144px;
	}
	
	div.wpforms-container-full .wpforms-form .procaptcha[data-size="invisible"] {
		display: none;
	}

	div.wpforms-container-full .wpforms-form .procaptcha iframe {
		position: relative;
	}
CSS;

		Procaptcha::css_display( $css );
	}

	/**
	 * Filter procap_ settings' fields and disable them.
	 *
	 * @param array  $fields Fields.
	 * @param string $view   View name.
	 *
	 * @return array
	 */
	public function wpforms_settings_fields( array $fields, string $view ): array {
		if ( 'captcha' !== $view ) {
			return $fields;
		}

		$inputs      = [
			'procaptcha-site-key',
			'procaptcha-secret-key',
			'procaptcha-fail-msg',
			'recaptcha-noconflict',
		];
		$search      = [
			'class="wpforms-setting-field',
			'<input ',
		];
		$replace     = [
			'style="opacity: 0.4;" ' . $search[0],
			$search[1] . 'disabled ',
		];
		$notice      = Procaptcha::get_procaptcha_plugin_notice();
		$label       = $notice['label'];
		$description = $notice['description'];

		foreach ( $inputs as $input ) {
			if ( ! isset( $fields[ $input ] ) ) {
				continue;
			}

			$fields[ $input ] = str_replace( $search, $replace, $fields[ $input ] );
		}

		if ( isset( $fields['procaptcha-heading'] ) ) {
			$notice_content = <<<HTML
<div
		id="wpforms-setting-row-procaptcha-heading"
		class="wpforms-setting-row wpforms-setting-row-content wpforms-clear section-heading specific-note">
	<span class="wpforms-setting-field">
		<div class="wpforms-specific-note-wrap">
			<div class="wpforms-specific-note-lightbulb">
				<svg viewBox="0 0 14 20">
					<path d="M3.75 17.97c0 .12 0 .23.08.35l.97 1.4c.12.2.32.28.51.28H8.4c.2 0 .39-.08.5-.27l.98-1.41c.04-.12.08-.23.08-.35v-1.72H3.75v1.72Zm3.13-5.47c.66 0 1.25-.55 1.25-1.25 0-.66-.6-1.25-1.26-1.25-.7 0-1.25.59-1.25 1.25 0 .7.55 1.25 1.25 1.25Zm0-12.5A6.83 6.83 0 0 0 0 6.88c0 1.75.63 3.32 1.68 4.53.66.74 1.68 2.3 2.03 3.59H5.6c0-.16 0-.35-.08-.55-.2-.7-.86-2.5-2.42-4.25a5.19 5.19 0 0 1-1.21-3.32c-.04-2.86 2.3-5 5-5 2.73 0 5 2.26 5 5 0 1.2-.47 2.38-1.26 3.32a11.72 11.72 0 0 0-2.42 4.25c-.07.2-.07.35-.07.55H10a10.56 10.56 0 0 1 2.03-3.6A6.85 6.85 0 0 0 6.88 0Zm-.4 8.75h.75c.3 0 .58-.23.62-.55l.5-3.75a.66.66 0 0 0-.62-.7H5.98a.66.66 0 0 0-.63.7l.5 3.75c.05.32.32.55.63.55Z"></path>
				</svg>
			</div>
			<div class="wpforms-specific-note-content">
				<p><strong>$label</strong></p>
				<p>$description</p>
			</div>
		</div>
	</span>
</div>
HTML;

			$fields['procaptcha-heading'] .= $notice_content;
		}

		if ( isset( $fields['captcha-preview'] ) ) {
			$fields['captcha-preview'] = preg_replace(
				'#<div class="wpforms-captcha wpforms-captcha-procaptcha".+?</div>#',
				Procaptcha::form(),
				$fields['captcha-preview']
			);
		}

		return $fields;
	}

	/**
	 * Filter whether to print procap_ scripts.
	 *
	 * @param bool|mixed $status Status.
	 *
	 * @return bool
	 */
	public function procap_print_procaptcha_scripts( $status ): bool {
		return $this->is_wpforms_procaptcha_settings_page() || $status;
	}

	/**
	 * Disable enqueuing wpforms procap_.
	 *
	 * @param bool|mixed $status Status.
	 *
	 * @return bool
	 */
	public function wpforms_admin_settings_captcha_enqueues_disable( $status ): bool {
		return $this->is_wpforms_procaptcha_settings_page() || $status;
	}

	/**
	 * Block recaptcha assets on frontend.
	 *
	 * @return void
	 */
	public function block_assets_recaptcha() {
		if ( ! $this->is_wpforms_provider_procaptcha() ) {
			return;
		}

		$captcha = wpforms()->get( 'captcha' );

		if ( ! $captcha ) {
			// @codeCoverageIgnoreStart
			return;
			// @codeCoverageIgnoreEnd
		}

		remove_action( 'wpforms_wp_footer', [ $captcha, 'assets_recaptcha' ] );
	}

	/**
	 * Output embedded procap_.
	 *
	 * @param array|mixed $form_data   Form data and settings.
	 * @param null        $deprecated  Deprecated in v1.3.7, previously was $form object.
	 * @param bool        $title       Whether to display form title.
	 * @param bool        $description Whether to display form description.
	 * @param array       $errors      List of all errors filled in WPForms_Process::process().
	 *
	 * @noinspection HtmlUnknownAttribute
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function wpforms_frontend_output( $form_data, $deprecated, bool $title, bool $description, array $errors ) {
		$form_data = (array) $form_data;

		if ( ! $this->process_procaptcha( $form_data ) ) {
			return;
		}

		if ( $this->mode_embed ) {
			$captcha = wpforms()->get( 'captcha' );

			if ( ! $captcha ) {
				// @codeCoverageIgnoreStart
				return;
				// @codeCoverageIgnoreEnd
			}

			// Block native WPForms procap_ output.
			remove_action( 'wpforms_frontend_output', [ $captcha, 'recaptcha' ], 20 );

			$this->show_procaptcha( $form_data );

			return;
		}

		if ( $this->mode_auto ) {
			$this->show_procaptcha( $form_data );
		}
	}

	/**
	 * Show procap_.
	 *
	 * @param array $form_data Form data and settings.
	 *
	 * @return void
	 * @noinspection HtmlUnknownAttribute
	 */
	private function show_procaptcha( array $form_data ) {
		$frontend_obj = wpforms()->get( 'frontend' );

		if ( ! $frontend_obj ) {
			// @codeCoverageIgnoreStart
			return;
			// @codeCoverageIgnoreEnd
		}

		$args = [
			'action' => self::ACTION,
			'name'   => self::NAME,
			'id'     => [
				'source'  => Procaptcha::get_class_source( static::class ),
				'form_id' => (int) $form_data['id'],
			],
		];

		if ( ! $this->mode_embed && $this->form_has_procaptcha( $form_data ) ) {
			$this->use_wpforms_settings();
		}

		printf(
			'<div class="wpforms-recaptcha-container wpforms-is-procaptcha" %s>',
			$frontend_obj->pages ? 'style="display:none;"' : ''
		);

		Procaptcha::form_display( $args );

		echo '</div>';
	}

	/**
	 * Whether form has procap_.
	 *
	 * @param array $form_data Form data and settings.
	 *
	 * @return bool
	 */
	private function form_has_procaptcha( array $form_data ): bool {
		$captcha_settings = wpforms_get_captcha_settings();
		$provider         = $captcha_settings['provider'] ?? '';

		if ( 'procaptcha' !== $provider ) {
			return false;
		}

		// Check that the CAPTCHA is configured for the specific form.
		$recaptcha = $form_data['settings']['recaptcha'] ?? '';

		return '1' === $recaptcha;
	}

	/**
	 * Check if the current page is wpforms captcha settings page and the current provider is procap_.
	 *
	 * @return bool
	 */
	private function is_wpforms_procaptcha_settings_page(): bool {
		if ( ! function_exists( 'get_current_screen' ) || ! is_admin() ) {
			return false;
		}

		$screen = get_current_screen();
		$id     = $screen->id ?? '';

		if ( 'wpforms_page_wpforms-settings' !== $id ) {
			return false;
		}

		return $this->is_wpforms_provider_procaptcha();
	}

	/**
	 * Check if the current captcha provider is procap_.
	 *
	 * @return bool
	 */
	private function is_wpforms_provider_procaptcha(): bool {
		$captcha_settings = wpforms_get_captcha_settings();
		$provider         = $captcha_settings['provider'] ?? '';

		return 'procaptcha' === $provider;
	}

	/**
	 * Process procap_ in the form.
	 * Returns true if form has procap_ or procap_ will be auto-added.
	 *
	 * @param array $form_data Form data.
	 *
	 * @return bool
	 */
	protected function process_procaptcha( array $form_data ): bool {
		return (
			$this->mode_auto ||
			( $this->mode_embed && $this->form_has_procaptcha( $form_data ) )
		);
	}

	/**
	 * Use WPForms settings for procap_.
	 *
	 * @return void
	 */
	private function use_wpforms_settings() {
		$captcha_settings = wpforms_get_captcha_settings();
		$site_key         = $captcha_settings['site_key'] ?? '';
		$secret_key       = $captcha_settings['secret_key'] ?? '';

		add_filter(
			'procap_site_key',
			static function () use ( $site_key ) {
				return $site_key;
			}
		);

		add_filter(
			'procap_secret_key',
			static function () use ( $secret_key ) {
				return $secret_key;
			}
		);

		add_filter(
			'procap_theme',
			static function () {
				return 'light';
			}
		);
	}
}
