<?php
/**
 * PluginSettingsBase class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\Settings;

use KAGG\Settings\Abstracts\SettingsBase;

/**
 * Class PluginSettingsBase
 *
 * Extends general SettingsBase suitable for any plugin with current plugin-related methods.
 */
abstract class PluginSettingsBase extends SettingsBase {

	/**
	 * Plugin prefix.
	 */
	const PREFIX = 'procaptcha';

	/**
	 * Constructor.
	 *
	 * @param array|null $tabs Tabs of this settings page.
	 */
	public function __construct( $tabs = [] ) {
		add_filter( 'admin_footer_text', [ $this, 'admin_footer_text' ] );
		add_filter( 'update_footer', [ $this, 'update_footer' ], PHP_INT_MAX );

		parent::__construct( $tabs );
	}

	/**
	 * Get menu title.
	 *
	 * @return string
	 */
	protected function menu_title(): string {
		$menu_title = __( 'procap_', 'procaptcha-wordpress' );
		$icon       = constant( 'PROCAPTCHA_URL' ) . '/assets/images/procaptcha-icon.svg';
		$icon       = '<img class="kagg-settings-menu-image" src="' . $icon . '" alt="procap_ icon">';

		return $icon . '<span class="kagg-settings-menu-title">' . $menu_title . '</span>';
	}

	/**
	 * Get screen id.
	 *
	 * @return string
	 */
	public function screen_id(): string {
		return 'settings_page_procaptcha';
	}

	/**
	 * Get an option group.
	 *
	 * @return string
	 */
	protected function option_group(): string {
		return 'procaptcha_group';
	}

	/**
	 * Get option page.
	 *
	 * @return string
	 */
	protected function option_page(): string {
		return 'procaptcha';
	}

	/**
	 * Get option name.
	 *
	 * @return string
	 */
	protected function option_name(): string {
		return 'procaptcha_settings';
	}

	/**
	 * Get plugin base name.
	 *
	 * @return string
	 */
	protected function plugin_basename(): string {
		return plugin_basename( constant( 'PROCAPTCHA_FILE' ) );
	}

	/**
	 * Get plugin url.
	 *
	 * @return string
	 */
	protected function plugin_url(): string {
		return constant( 'PROCAPTCHA_URL' );
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	protected function plugin_version(): string {
		return constant( 'PROCAPTCHA_VERSION' );
	}

	/**
	 * Get settings link label.
	 *
	 * @return string
	 */
	protected function settings_link_label(): string {
		return __( 'procap_ Settings', 'procaptcha-wordpress' );
	}

	/**
	 * Get settings link text.
	 *
	 * @return string
	 */
	protected function settings_link_text(): string {
		return __( 'Settings', 'procaptcha-wordpress' );
	}

	/**
	 * Get text domain.
	 *
	 * @return string
	 */
	protected function text_domain(): string {
		return 'procaptcha-wordpress';
	}

	/**
	 * Setup settings fields.
	 */
	public function setup_fields() {
		$prefix = $this->option_page() . '-' . static::section_title() . '-';

		foreach ( $this->form_fields as $key => $form_field ) {
			if ( ! isset( $form_field['class'] ) ) {
				$this->form_fields[ $key ]['class'] = str_replace( '_', '-', $prefix . $key );
			}
		}

		parent::setup_fields();
	}

	/**
	 * Show settings page.
	 */
	public function settings_page() {
		?>
		<img
				src="<?php echo esc_url( PROCAPTCHA_URL . '/assets/images/procaptcha-logo.svg' ); ?>"
				alt="procap_ Logo"
				class="procaptcha-logo"
		/>

		<form
				id="procaptcha-options"
				class="procaptcha-<?php echo esc_attr( $this->section_title() ); ?>"
				action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>"
				method="post">
			<?php
			do_settings_sections( $this->option_page() ); // Sections with options.
			settings_fields( $this->option_group() ); // Hidden protection fields.

			if ( ! empty( $this->form_fields ) ) {
				$this->submit_button();
			}
			?>
		</form>
		<?php
	}

	/**
	 * Show submit button in any place of the form.
	 *
	 * @return void
	 */
	public function submit_button() {
		static $shown = false;

		if ( $shown ) {
			return;
		}

		$shown = true;

		submit_button();
	}

	/**
	 * When a user is on the plugin admin page, display footer text that graciously asks them to rate us.
	 *
	 * @param string|mixed $text Footer text.
	 *
	 * @return string|mixed
	 * @noinspection HtmlUnknownTarget
	 */
	public function admin_footer_text( $text ) {
		if ( ! $this->is_options_screen( [] ) ) {
			return $text;
		}

		$url = 'https://wordpress.org/support/plugin/procaptcha-wordpress/reviews/?filter=5#new-post';

		return wp_kses(
			sprintf(
			/* translators: 1: plugin name, 2: wp.org review link with stars, 3: wp.org review link with text. */
				__( 'Please rate %1$s %2$s on %3$s. Thank you!', 'procaptcha-wordpress' ),
				'<strong>procap_ for WordPress</strong>',
				sprintf(
					'<a href="%1$s" target="_blank" rel="noopener noreferrer">★★★★★</a>',
					$url
				),
				sprintf(
					'<a href="%1$s" target="_blank" rel="noopener noreferrer">WordPress.org</a>',
					$url
				)
			),
			[
				'a' => [
					'href'   => [],
					'target' => [],
					'rel'    => [],
				],
			]
		);
	}

	/**
	 * Show a plugin version in the update footer.
	 *
	 * @param string|mixed $content The content that will be printed.
	 *
	 * @return string|mixed
	 */
	public function update_footer( $content ) {
		if ( ! $this->is_options_screen() ) {
			return $content;
		}

		return sprintf(
		/* translators: 1: plugin version. */
			__( 'Version %s', 'procaptcha-wordpress' ),
			PROCAPTCHA_VERSION
		);
	}
}
