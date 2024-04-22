<?php
/**
 * General class file.
 *
 * @package hcaptcha-wp
 */

namespace HCaptcha\Settings;

use HCaptcha\Admin\Notifications;
use HCaptcha\Helpers\HCaptcha;
use HCaptcha\Main;
use KAGG\Settings\Abstracts\SettingsBase;

/**
 * Class General
 *
 * Settings page "General".
 */
class General extends PluginSettingsBase {

	/**
	 * Dialog scripts and style handle.
	 */
	const DIALOG_HANDLE = 'kagg-dialog';

	/**
	 * Admin script handle.
	 */
	const HANDLE = 'hcaptcha-general';

	/**
	 * Script localization object.
	 */
	const OBJECT = 'HCaptchaGeneralObject';

	/**
	 * Check config ajax action.
	 */
	const CHECK_CONFIG_ACTION = 'hcaptcha-general-check-config';

	/**
	 * Toggle section ajax action.
	 */
	const TOGGLE_SECTION_ACTION = 'hcaptcha-general-toggle-section';

	/**
	 * Keys section id.
	 */
	const SECTION_KEYS = 'keys';

	/**
	 * Appearance section id.
	 */
	const SECTION_APPEARANCE = 'appearance';

	/**
	 * Custom section id.
	 */
	const SECTION_CUSTOM = 'custom';

	/**
	 * Enterprise section id.
	 */
	const SECTION_ENTERPRISE = 'enterprise';

	/**
	 * Other section id.
	 */
	const SECTION_OTHER = 'other';

	/**
	 * Statistics section id.
	 */
	const SECTION_STATISTICS = 'statistics';

	/**
	 * Live mode.
	 */
	const MODE_LIVE = 'live';

	/**
	 * Test publisher mode.
	 */
	const MODE_TEST_PUBLISHER = 'test:publisher';

	/**
	 * Test enterprise safe end user mode.
	 */
	const MODE_TEST_ENTERPRISE_SAFE_END_USER = 'test:enterprise_safe_end_user';

	/**
	 * Test enterprise bot detected mode.
	 */
	const MODE_TEST_ENTERPRISE_BOT_DETECTED = 'test:enterprise_bot_detected';

	/**
	 * Test publisher mode site key.
	 */
	const MODE_TEST_PUBLISHER_SITE_KEY = '10000000-ffff-ffff-ffff-000000000001';

	/**
	 * Test enterprise safe end user mode site key.
	 */
	const MODE_TEST_ENTERPRISE_SAFE_END_USER_SITE_KEY = '20000000-ffff-ffff-ffff-000000000002';

	/**
	 * Test enterprise bot detected mode site key.
	 */
	const MODE_TEST_ENTERPRISE_BOT_DETECTED_SITE_KEY = '30000000-ffff-ffff-ffff-000000000003';

	/**
	 * User settings meta.
	 */
	const USER_SETTINGS_META = 'hcaptcha_user_settings';

	/**
	 * Notifications class instance.
	 *
	 * @var Notifications
	 */
	protected $notifications;

	/**
	 * Get page title.
	 *
	 * @return string
	 */
	protected function page_title(): string {
		return __( 'General', 'procaptcha-wordpress' );
	}

	/**
	 * Get section title.
	 *
	 * @return string
	 */
	protected function section_title(): string {
		return 'general';
	}

	/**
	 * Init class hooks.
	 */
	protected function init_hooks() {
		parent::init_hooks();

		$hcaptcha = hcaptcha();

		// Current class loaded early on plugins_loaded. Init Notifications later, when Settings class is ready.
		add_action( 'plugins_loaded', [ $this, 'init_notifications' ] );
		add_action( 'admin_head', [ $hcaptcha, 'print_inline_styles' ] );
		add_action( 'admin_print_footer_scripts', [ $hcaptcha, 'print_footer_scripts' ], 0 );

		add_filter( 'kagg_settings_fields', [ $this, 'settings_fields' ] );
		add_action( 'wp_ajax_' . self::CHECK_CONFIG_ACTION, [ $this, 'check_config' ] );
		add_action( 'wp_ajax_' . self::TOGGLE_SECTION_ACTION, [ $this, 'toggle_section' ] );

		add_filter( 'pre_update_option_' . $this->option_name(), [ $this, 'maybe_send_stats' ], 20, 2 );
	}

	/**
	 * Init notifications.
	 *
	 * @return void
	 */
	public function init_notifications() {
		$this->notifications = new Notifications();
		$this->notifications->init();
	}

	/**
	 * Init form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = [
			'site_key'             => [
				'label'        => __( 'Site Key', 'procaptcha-wordpress' ),
				'type'         => 'text',
				'autocomplete' => 'nickname',
				'lp_ignore'    => 'true',
				'section'      => self::SECTION_KEYS,
			],
			'secret_key'           => [
				'label'   => __( 'Secret Key', 'procaptcha-wordpress' ),
				'type'    => 'password',
				'section' => self::SECTION_KEYS,
			],
			'sample_hcaptcha'      => [
				'label'   => __( 'Active hCaptcha to Check Site Config', 'procaptcha-wordpress' ),
				'type'    => 'hcaptcha',
				'section' => self::SECTION_KEYS,
			],
			'check_config'         => [
				'label'   => __( 'Check Site Config', 'procaptcha-wordpress' ),
				'type'    => 'button',
				'text'    => __( 'Check', 'procaptcha-wordpress' ),
				'section' => self::SECTION_KEYS,
			],
			'reset_notifications'  => [
				'label'   => __( 'Reset Notifications', 'procaptcha-wordpress' ),
				'type'    => 'button',
				'text'    => __( 'Reset', 'procaptcha-wordpress' ),
				'section' => self::SECTION_KEYS,
			],
			'theme'                => [
				'label'   => __( 'Theme', 'procaptcha-wordpress' ),
				'type'    => 'select',
				'section' => self::SECTION_APPEARANCE,
				'options' => [
					'light' => __( 'Light', 'procaptcha-wordpress' ),
					'dark'  => __( 'Dark', 'procaptcha-wordpress' ),
					'auto'  => __( 'Auto', 'procaptcha-wordpress' ),
				],
				'helper'  => __( 'Select hCaptcha theme.', 'procaptcha-wordpress' ),
			],
			'size'                 => [
				'label'   => __( 'Size', 'procaptcha-wordpress' ),
				'type'    => 'select',
				'section' => self::SECTION_APPEARANCE,
				'options' => [
					'normal'    => __( 'Normal', 'procaptcha-wordpress' ),
					'compact'   => __( 'Compact', 'procaptcha-wordpress' ),
					'invisible' => __( 'Invisible', 'procaptcha-wordpress' ),
				],
				'helper'  => __( 'Select hCaptcha size.', 'procaptcha-wordpress' ),
			],
			'language'             => [
				'label'   => __( 'Language', 'procaptcha-wordpress' ),
				'type'    => 'select',
				'section' => self::SECTION_APPEARANCE,
				'options' => [
					''      => __( '--- Auto-Detect ---', 'procaptcha-wordpress' ),
					'af'    => __( 'Afrikaans', 'procaptcha-wordpress' ),
					'sq'    => __( 'Albanian', 'procaptcha-wordpress' ),
					'am'    => __( 'Amharic', 'procaptcha-wordpress' ),
					'ar'    => __( 'Arabic', 'procaptcha-wordpress' ),
					'hy'    => __( 'Armenian', 'procaptcha-wordpress' ),
					'az'    => __( 'Azerbaijani', 'procaptcha-wordpress' ),
					'eu'    => __( 'Basque', 'procaptcha-wordpress' ),
					'be'    => __( 'Belarusian', 'procaptcha-wordpress' ),
					'bn'    => __( 'Bengali', 'procaptcha-wordpress' ),
					'bg'    => __( 'Bulgarian', 'procaptcha-wordpress' ),
					'bs'    => __( 'Bosnian', 'procaptcha-wordpress' ),
					'my'    => __( 'Burmese', 'procaptcha-wordpress' ),
					'ca'    => __( 'Catalan', 'procaptcha-wordpress' ),
					'ceb'   => __( 'Cebuano', 'procaptcha-wordpress' ),
					'zh'    => __( 'Chinese', 'procaptcha-wordpress' ),
					'zh-CN' => __( 'Chinese Simplified', 'procaptcha-wordpress' ),
					'zh-TW' => __( 'Chinese Traditional', 'procaptcha-wordpress' ),
					'co'    => __( 'Corsican', 'procaptcha-wordpress' ),
					'hr'    => __( 'Croatian', 'procaptcha-wordpress' ),
					'cs'    => __( 'Czech', 'procaptcha-wordpress' ),
					'da'    => __( 'Danish', 'procaptcha-wordpress' ),
					'nl'    => __( 'Dutch', 'procaptcha-wordpress' ),
					'en'    => __( 'English', 'procaptcha-wordpress' ),
					'eo'    => __( 'Esperanto', 'procaptcha-wordpress' ),
					'et'    => __( 'Estonian', 'procaptcha-wordpress' ),
					'fa'    => __( 'Persian', 'procaptcha-wordpress' ),
					'fi'    => __( 'Finnish', 'procaptcha-wordpress' ),
					'fr'    => __( 'French', 'procaptcha-wordpress' ),
					'fy'    => __( 'Frisian', 'procaptcha-wordpress' ),
					'gd'    => __( 'Gaelic', 'procaptcha-wordpress' ),
					'gl'    => __( 'Galician', 'procaptcha-wordpress' ),
					'ka'    => __( 'Georgian', 'procaptcha-wordpress' ),
					'de'    => __( 'German', 'procaptcha-wordpress' ),
					'el'    => __( 'Greek', 'procaptcha-wordpress' ),
					'gu'    => __( 'Gujarati', 'procaptcha-wordpress' ),
					'ht'    => __( 'Haitian', 'procaptcha-wordpress' ),
					'ha'    => __( 'Hausa', 'procaptcha-wordpress' ),
					'haw'   => __( 'Hawaiian', 'procaptcha-wordpress' ),
					'he'    => __( 'Hebrew', 'procaptcha-wordpress' ),
					'hi'    => __( 'Hindi', 'procaptcha-wordpress' ),
					'hmn'   => __( 'Hmong', 'procaptcha-wordpress' ),
					'hu'    => __( 'Hungarian', 'procaptcha-wordpress' ),
					'is'    => __( 'Icelandic', 'procaptcha-wordpress' ),
					'ig'    => __( 'Igbo', 'procaptcha-wordpress' ),
					'id'    => __( 'Indonesian', 'procaptcha-wordpress' ),
					'ga'    => __( 'Irish', 'procaptcha-wordpress' ),
					'it'    => __( 'Italian', 'procaptcha-wordpress' ),
					'ja'    => __( 'Japanese', 'procaptcha-wordpress' ),
					'jw'    => __( 'Javanese', 'procaptcha-wordpress' ),
					'kn'    => __( 'Kannada', 'procaptcha-wordpress' ),
					'kk'    => __( 'Kazakh', 'procaptcha-wordpress' ),
					'km'    => __( 'Khmer', 'procaptcha-wordpress' ),
					'rw'    => __( 'Kinyarwanda', 'procaptcha-wordpress' ),
					'ky'    => __( 'Kirghiz', 'procaptcha-wordpress' ),
					'ko'    => __( 'Korean', 'procaptcha-wordpress' ),
					'ku'    => __( 'Kurdish', 'procaptcha-wordpress' ),
					'lo'    => __( 'Lao', 'procaptcha-wordpress' ),
					'la'    => __( 'Latin', 'procaptcha-wordpress' ),
					'lv'    => __( 'Latvian', 'procaptcha-wordpress' ),
					'lt'    => __( 'Lithuanian', 'procaptcha-wordpress' ),
					'lb'    => __( 'Luxembourgish', 'procaptcha-wordpress' ),
					'mk'    => __( 'Macedonian', 'procaptcha-wordpress' ),
					'mg'    => __( 'Malagasy', 'procaptcha-wordpress' ),
					'ms'    => __( 'Malay', 'procaptcha-wordpress' ),
					'ml'    => __( 'Malayalam', 'procaptcha-wordpress' ),
					'mt'    => __( 'Maltese', 'procaptcha-wordpress' ),
					'mi'    => __( 'Maori', 'procaptcha-wordpress' ),
					'mr'    => __( 'Marathi', 'procaptcha-wordpress' ),
					'mn'    => __( 'Mongolian', 'procaptcha-wordpress' ),
					'ne'    => __( 'Nepali', 'procaptcha-wordpress' ),
					'no'    => __( 'Norwegian', 'procaptcha-wordpress' ),
					'ny'    => __( 'Nyanja', 'procaptcha-wordpress' ),
					'or'    => __( 'Oriya', 'procaptcha-wordpress' ),
					'pl'    => __( 'Polish', 'procaptcha-wordpress' ),
					'pt'    => __( 'Portuguese', 'procaptcha-wordpress' ),
					'ps'    => __( 'Pashto', 'procaptcha-wordpress' ),
					'pa'    => __( 'Punjabi', 'procaptcha-wordpress' ),
					'ro'    => __( 'Romanian', 'procaptcha-wordpress' ),
					'ru'    => __( 'Russian', 'procaptcha-wordpress' ),
					'sm'    => __( 'Samoan', 'procaptcha-wordpress' ),
					'sn'    => __( 'Shona', 'procaptcha-wordpress' ),
					'sd'    => __( 'Sindhi', 'procaptcha-wordpress' ),
					'si'    => __( 'Sinhala', 'procaptcha-wordpress' ),
					'sr'    => __( 'Serbian', 'procaptcha-wordpress' ),
					'sk'    => __( 'Slovak', 'procaptcha-wordpress' ),
					'sl'    => __( 'Slovenian', 'procaptcha-wordpress' ),
					'so'    => __( 'Somali', 'procaptcha-wordpress' ),
					'st'    => __( 'Southern Sotho', 'procaptcha-wordpress' ),
					'es'    => __( 'Spanish', 'procaptcha-wordpress' ),
					'su'    => __( 'Sundanese', 'procaptcha-wordpress' ),
					'sw'    => __( 'Swahili', 'procaptcha-wordpress' ),
					'sv'    => __( 'Swedish', 'procaptcha-wordpress' ),
					'tl'    => __( 'Tagalog', 'procaptcha-wordpress' ),
					'tg'    => __( 'Tajik', 'procaptcha-wordpress' ),
					'ta'    => __( 'Tamil', 'procaptcha-wordpress' ),
					'tt'    => __( 'Tatar', 'procaptcha-wordpress' ),
					'te'    => __( 'Telugu', 'procaptcha-wordpress' ),
					'th'    => __( 'Thai', 'procaptcha-wordpress' ),
					'tr'    => __( 'Turkish', 'procaptcha-wordpress' ),
					'tk'    => __( 'Turkmen', 'procaptcha-wordpress' ),
					'ug'    => __( 'Uyghur', 'procaptcha-wordpress' ),
					'uk'    => __( 'Ukrainian', 'procaptcha-wordpress' ),
					'ur'    => __( 'Urdu', 'procaptcha-wordpress' ),
					'uz'    => __( 'Uzbek', 'procaptcha-wordpress' ),
					'vi'    => __( 'Vietnamese', 'procaptcha-wordpress' ),
					'cy'    => __( 'Welsh', 'procaptcha-wordpress' ),
					'xh'    => __( 'Xhosa', 'procaptcha-wordpress' ),
					'yi'    => __( 'Yiddish', 'procaptcha-wordpress' ),
					'yo'    => __( 'Yoruba', 'procaptcha-wordpress' ),
					'zu'    => __( 'Zulu', 'procaptcha-wordpress' ),
				],
				'helper'  => __(
					"By default, hCaptcha will automatically detect the user's locale and localize widgets accordingly.",
					'procaptcha-wordpress'
				),
			],
			'mode'                 => [
				'label'   => __( 'Mode', 'procaptcha-wordpress' ),
				'type'    => 'select',
				'section' => self::SECTION_APPEARANCE,
				// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned, WordPress.Arrays.MultipleStatementAlignment.LongIndexSpaceBeforeDoubleArrow
				'options' => [
					self::MODE_LIVE                          => __( 'Live', 'procaptcha-wordpress' ),
					self::MODE_TEST_PUBLISHER                => __( 'Test: Publisher Account', 'procaptcha-wordpress' ),
					self::MODE_TEST_ENTERPRISE_SAFE_END_USER => __( 'Test: Enterprise Account (Safe End User)', 'procaptcha-wordpress' ),
					self::MODE_TEST_ENTERPRISE_BOT_DETECTED  => __( 'Test: Enterprise Account (Bot Detected)', 'procaptcha-wordpress' ),
				],
				// phpcs:enable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned, WordPress.Arrays.MultipleStatementAlignment.LongIndexSpaceBeforeDoubleArrow
				'default' => self::MODE_LIVE,
				'helper'  => __(
					'Select live or test mode. In test mode, predefined keys are used.',
					'procaptcha-wordpress'
				),
			],
			'force'                => [
				'label'   => __( 'Force hCaptcha', 'procaptcha-wordpress' ),
				'type'    => 'checkbox',
				'section' => self::SECTION_APPEARANCE,
				'options' => [
					'on' => __( 'Force hCaptcha', 'procaptcha-wordpress' ),
				],
				'helper'  => __( 'Force hCaptcha check before submit.', 'procaptcha-wordpress' ),
			],
			'custom_themes'        => [
				'label'   => __( 'Custom Themes', 'procaptcha-wordpress' ),
				'type'    => 'checkbox',
				'section' => self::SECTION_CUSTOM,
				'options' => [
					'on' => __( 'Enable Custom Themes', 'procaptcha-wordpress' ),
				],
				'helper'  => sprintf(
				/* translators: 1: hCaptcha Pro link, 2: hCaptcha Enterprise link. */
					__( 'Note: only works on hCaptcha %1$s and %2$s site keys.', 'procaptcha-wordpress' ),
					sprintf(
						'<a href="https://www.prosopo.io/pro?utm_source=wordpress&utm_medium=wpplugin&utm_campaign=upgrade" target="_blank">%s</a>',
						__( 'Pro', 'procaptcha-wordpress' )
					),
					sprintf(
						'<a href="https://www.prosopo.io/enterprise?utm_source=wordpress&utm_medium=wpplugin&utm_campaign=upgrade" target="_blank">%s</a>',
						__( 'Enterprise', 'procaptcha-wordpress' )
					)
				),
			],
			'custom_prop'          => [
				'label'   => __( 'Property', 'procaptcha-wordpress' ),
				'type'    => 'select',
				'options' => [],
				'section' => self::SECTION_CUSTOM,
				'helper'  => __( 'Select custom theme property.', 'procaptcha-wordpress' ),
			],
			'custom_value'         => [
				'label'   => __( 'Value', 'procaptcha-wordpress' ),
				'type'    => 'text',
				'section' => self::SECTION_CUSTOM,
				'helper'  => __( 'Set property value.', 'procaptcha-wordpress' ),
			],
			'config_params'        => [
				'label'   => __( 'Config Params', 'procaptcha-wordpress' ),
				'type'    => 'textarea',
				'section' => self::SECTION_CUSTOM,
				'helper'  => sprintf(
				/* translators: 1: hCaptcha render params doc link. */
					__( 'hCaptcha render %s (optional). Must be a valid JSON.', 'procaptcha-wordpress' ),
					sprintf(
						'<a href="https://docs.prosopo.io/configuration/#hcaptcharendercontainer-params?utm_source=wordpress&utm_medium=wpplugin&utm_campaign=docs" target="_blank">%s</a>',
						__( 'parameters', 'procaptcha-wordpress' )
					)
				),
			],
			'api_host'             => [
				'label'   => __( 'API Host', 'procaptcha-wordpress' ),
				'type'    => 'text',
				'section' => self::SECTION_ENTERPRISE,
				'default' => Main::API_HOST,
				'helper'  => __( 'See Enterprise docs.' ),
			],
			'asset_host'           => [
				'label'   => __( 'Asset Host', 'procaptcha-wordpress' ),
				'type'    => 'text',
				'section' => self::SECTION_ENTERPRISE,
				'helper'  => __( 'See Enterprise docs.', 'procaptcha-wordpress' ),
			],
			'endpoint'             => [
				'label'   => __( 'Endpoint', 'procaptcha-wordpress' ),
				'type'    => 'text',
				'section' => self::SECTION_ENTERPRISE,
				'helper'  => __( 'See Enterprise docs.', 'procaptcha-wordpress' ),
			],
			'host'                 => [
				'label'   => __( 'Host', 'procaptcha-wordpress' ),
				'type'    => 'text',
				'section' => self::SECTION_ENTERPRISE,
				'helper'  => __( 'See Enterprise docs.', 'procaptcha-wordpress' ),
			],
			'image_host'           => [
				'label'   => __( 'Image Host', 'procaptcha-wordpress' ),
				'type'    => 'text',
				'section' => self::SECTION_ENTERPRISE,
				'helper'  => __( 'See Enterprise docs.', 'procaptcha-wordpress' ),
			],
			'report_api'           => [
				'label'   => __( 'Report API', 'procaptcha-wordpress' ),
				'type'    => 'text',
				'section' => self::SECTION_ENTERPRISE,
				'helper'  => __( 'See Enterprise docs.', 'procaptcha-wordpress' ),
			],
			'sentry'               => [
				'label'   => __( 'Sentry', 'procaptcha-wordpress' ),
				'type'    => 'text',
				'section' => self::SECTION_ENTERPRISE,
				'helper'  => __( 'See Enterprise docs.', 'procaptcha-wordpress' ),
			],
			'backend'              => [
				'label'   => __( 'Backend', 'procaptcha-wordpress' ),
				'type'    => 'text',
				'section' => self::SECTION_ENTERPRISE,
				'default' => Main::VERIFY_HOST,
				'helper'  => __( 'See Enterprise docs.', 'procaptcha-wordpress' ),
			],
			'off_when_logged_in'   => [
				'label'   => __( 'Other Settings', 'procaptcha-wordpress' ),
				'type'    => 'checkbox',
				'section' => self::SECTION_OTHER,
				'options' => [
					'on' => __( 'Turn Off When Logged In', 'procaptcha-wordpress' ),
				],
				'helper'  => __( 'Do not show hCaptcha to logged-in users.', 'procaptcha-wordpress' ),
			],
			'recaptcha_compat_off' => [
				'type'    => 'checkbox',
				'section' => self::SECTION_OTHER,
				'options' => [
					'on' => __( 'Disable reCAPTCHA Compatibility', 'procaptcha-wordpress' ),
				],
				'helper'  => __( 'Use if including both hCaptcha and reCAPTCHA on the same page.', 'procaptcha-wordpress' ),
			],
			self::NETWORK_WIDE     => [
				'type'    => 'checkbox',
				'section' => self::SECTION_OTHER,
				'options' => [
					'on' => __( 'Use network-wide settings', 'procaptcha-wordpress' ),
				],
				'helper'  => __( 'On multisite, use same settings for all sites of the network.', 'procaptcha-wordpress' ),
			],
			'whitelisted_ips'      => [
				'label'   => __( 'Whitelisted IPs', 'procaptcha-wordpress' ),
				'type'    => 'textarea',
				'section' => self::SECTION_OTHER,
				'helper'  => __( 'Do not show hCaptcha for listed IP addresses. Please specify one IP address per line.', 'procaptcha-wordpress' ),
			],
			'login_limit'          => [
				'label'   => __( 'Login attempts before hCaptcha', 'procaptcha-wordpress' ),
				'type'    => 'number',
				'section' => self::SECTION_OTHER,
				'default' => 0,
				'min'     => 0,
				'helper'  => __( 'Maximum number of failed login attempts before showing hCaptcha.', 'procaptcha-wordpress' ),
			],
			'login_interval'       => [
				'label'   => __( 'Failed login attempts interval, min', 'procaptcha-wordpress' ),
				'type'    => 'number',
				'section' => self::SECTION_OTHER,
				'default' => 15,
				'min'     => 1,
				'helper'  => __( 'Time interval in minutes when failed login attempts are counted.', 'procaptcha-wordpress' ),
			],
			'delay'                => [
				'label'   => __( 'Delay showing hCaptcha, ms', 'procaptcha-wordpress' ),
				'type'    => 'number',
				'section' => self::SECTION_OTHER,
				'default' => -100,
				'min'     => -100,
				'step'    => 100,
				'helper'  => __( 'Delay time for loading the hCaptcha API script. Any negative value will prevent the API script from loading until user interaction: mouseenter, click, scroll or touch. This significantly improves Google Pagespeed Insights score.', 'procaptcha-wordpress' ),
			],
			'statistics'           => [
				'label'   => __( 'Statistics', 'procaptcha-wordpress' ),
				'type'    => 'checkbox',
				'section' => self::SECTION_STATISTICS,
				'options' => [
					'on' => __( 'Enable Statistics', 'procaptcha-wordpress' ),
				],
				'helper'  => __( 'By turning the statistics on, you agree to the collection of non-personal data to improve the plugin.', 'procaptcha-wordpress' ),
			],
			'collect_ip'           => [
				'label'   => __( 'Collection', 'procaptcha-wordpress' ),
				'type'    => 'checkbox',
				'section' => self::SECTION_STATISTICS,
				'options' => [
					'on' => __( 'Collect IP', 'procaptcha-wordpress' ),
				],
				'helper'  => __( 'Allow collecting of IP addresses from which forms were sent.', 'procaptcha-wordpress' ),
			],
			'collect_ua'           => [
				'type'    => 'checkbox',
				'section' => self::SECTION_STATISTICS,
				'options' => [
					'on' => __( 'Collect User Agent', 'procaptcha-wordpress' ),
				],
				'helper'  => __( 'Allow collecting of User Agent headers of users sending forms.', 'procaptcha-wordpress' ),
			],
		];

		if ( ! is_multisite() ) {
			unset( $this->form_fields[ self::NETWORK_WIDE ] );
		}
	}

	/**
	 * Setup settings fields.
	 */
	public function setup_fields() {
		if ( ! $this->is_options_screen() ) {
			return;
		}

		$settings = hcaptcha()->settings();

		// In Settings, a filter applied for mode.
		$mode = $settings->get_mode();

		if ( self::MODE_LIVE !== $mode ) {
			$this->form_fields['site_key']['disabled']   = true;
			$this->form_fields['secret_key']['disabled'] = true;
		}

		$custom_theme  = $settings->get( 'config_params' )['theme'] ?? [];
		$default_theme = $settings->get_default_theme();
		$custom_theme  = array_merge_recursive( $default_theme, $custom_theme );
		$custom_theme  = $this->flatten_array( $custom_theme );
		$options       = [];
		$custom_theme  = array_merge(
			[ esc_html__( '- Select Property -', 'procaptcha-wordpress' ) => '' ],
			$custom_theme
		);

		foreach ( $custom_theme as $key => $value ) {
			$key_arr = explode( '--', $key );
			$level   = count( $key_arr ) - 1;
			$prefix  = $level ? str_repeat( '–', $level ) . ' ' : '';
			$option  = $prefix . ucfirst( end( $key_arr ) );

			$options[ $key . '=' . $value ] = $option;
		}

		$this->form_fields['custom_prop']['options'] = $options;

		parent::setup_fields();
	}

	/**
	 * Section callback.
	 *
	 * @param array $arguments Section arguments.
	 */
	public function section_callback( array $arguments ) {
		switch ( $arguments['id'] ) {
			case self::SECTION_KEYS:
				?>
				<h2>
					<?php echo esc_html( $this->page_title() ); ?>
				</h2>
				<div id="hcaptcha-message"></div>
				<?php
				$this->notifications->show();
				$this->print_section_header( $arguments['id'], __( 'Keys', 'procaptcha-wordpress' ) );
				break;
			case self::SECTION_APPEARANCE:
				$this->print_section_header( $arguments['id'], __( 'Appearance', 'procaptcha-wordpress' ) );
				break;
			case self::SECTION_CUSTOM:
				$this->print_section_header( $arguments['id'], __( 'Custom', 'procaptcha-wordpress' ) );
				break;
			case self::SECTION_ENTERPRISE:
				$this->print_section_header( $arguments['id'], __( 'Enterprise', 'procaptcha-wordpress' ) );
				break;
			case self::SECTION_OTHER:
				$this->print_section_header( $arguments['id'], __( 'Other', 'procaptcha-wordpress' ) );
				break;
			case self::SECTION_STATISTICS:
				$this->print_section_header( $arguments['id'], __( 'Statistics', 'procaptcha-wordpress' ) );
				break;
			default:
				break;
		}
	}

	/**
	 * Print section header.
	 *
	 * @param string $id    Section id.
	 * @param string $title Section title.
	 *
	 * @return void
	 */
	private function print_section_header( string $id, string $title ) {
		$user                   = wp_get_current_user();
		$hcaptcha_user_settings = [];

		if ( $user ) {
			$hcaptcha_user_settings = get_user_meta( $user->ID, self::USER_SETTINGS_META, true );
		}

		$open     = $hcaptcha_user_settings['sections'][ $id ] ?? true;
		$disabled = '';
		$class    = '';
		$license  = hcaptcha()->settings()->get_license();

		switch ( $id ) {
			case self::SECTION_CUSTOM:
				if ( 'free' === $license ) {
					$open     = false;
					$disabled = true;

					$title .= ' - ' . __( 'hCaptcha Pro Required', 'procaptcha-wordpress' );
				}
				break;
			case self::SECTION_ENTERPRISE:
				if ( 'free' === $license ) {
					$open     = false;
					$disabled = true;

					$title .= ' - ' . __( 'hCaptcha Enterprise Required', 'procaptcha-wordpress' );
				}
				break;
			default:
				break;
		}

		$class .= $open ? '' : ' closed';
		$class .= $disabled ? ' disabled' : '';

		?>
		<h3 class="hcaptcha-section-<?php echo esc_attr( $id ); ?><?php echo esc_attr( $class ); ?>">
			<span class="hcaptcha-section-header-title">
				<?php echo esc_html( $title ); ?>
			</span>
			<span class="hcaptcha-section-header-toggle">
			</span>
		</h3>
		<?php
	}

	/**
	 * Enqueue class scripts.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script(
			self::DIALOG_HANDLE,
			constant( 'HCAPTCHA_URL' ) . "/assets/js/kagg-dialog$this->min_prefix.js",
			[],
			constant( 'HCAPTCHA_VERSION' ),
			true
		);

		wp_enqueue_style(
			self::DIALOG_HANDLE,
			constant( 'HCAPTCHA_URL' ) . "/assets/css/kagg-dialog$this->min_prefix.css",
			[],
			constant( 'HCAPTCHA_VERSION' )
		);

		wp_enqueue_script(
			self::HANDLE,
			constant( 'HCAPTCHA_URL' ) . "/assets/js/general$this->min_prefix.js",
			[ 'jquery', self::DIALOG_HANDLE ],
			constant( 'HCAPTCHA_VERSION' ),
			true
		);

		$check_config_notice =
			esc_html__( 'Credentials changed.', 'procaptcha-wordpress' ) . "\n" .
			esc_html__( 'Please complete hCaptcha and check the site config.', 'procaptcha-wordpress' );

		wp_localize_script(
			self::HANDLE,
			self::OBJECT,
			[
				'ajaxUrl'                              => admin_url( 'admin-ajax.php' ),
				'checkConfigAction'                    => self::CHECK_CONFIG_ACTION,
				'checkConfigNonce'                     => wp_create_nonce( self::CHECK_CONFIG_ACTION ),
				'toggleSectionAction'                  => self::TOGGLE_SECTION_ACTION,
				'toggleSectionNonce'                   => wp_create_nonce( self::TOGGLE_SECTION_ACTION ),
				'modeLive'                             => self::MODE_LIVE,
				'modeTestPublisher'                    => self::MODE_TEST_PUBLISHER,
				'modeTestEnterpriseSafeEndUser'        => self::MODE_TEST_ENTERPRISE_SAFE_END_USER,
				'modeTestEnterpriseBotDetected'        => self::MODE_TEST_ENTERPRISE_BOT_DETECTED,
				'siteKey'                              => hcaptcha()->settings()->get( 'site_key' ),
				'modeTestPublisherSiteKey'             => self::MODE_TEST_PUBLISHER_SITE_KEY,
				'modeTestEnterpriseSafeEndUserSiteKey' => self::MODE_TEST_ENTERPRISE_SAFE_END_USER_SITE_KEY,
				'modeTestEnterpriseBotDetectedSiteKey' => self::MODE_TEST_ENTERPRISE_BOT_DETECTED_SITE_KEY,
				'checkConfigNotice'                    => $check_config_notice,
				'checkingConfigMsg'                    => __( 'Checking site config...', 'procaptcha-wordpress' ),
				'completeHCaptchaTitle'                => __( 'Please complete the Procaptcha.', 'procaptcha-wordpress' ),
				'completeHCaptchaContent'              => __( 'Before checking the site config, please complete the Active hCaptcha in the current section.', 'procaptcha-wordpress' ),
				'OKBtnText'                            => __( 'OK', 'procaptcha-wordpress' ),
			]
		);

		wp_enqueue_style(
			self::HANDLE,
			constant( 'HCAPTCHA_URL' ) . "/assets/css/general$this->min_prefix.css",
			[ static::PREFIX . '-' . SettingsBase::HANDLE, self::DIALOG_HANDLE ],
			constant( 'HCAPTCHA_VERSION' )
		);

	}

	/**
	 * Add custom hCaptcha field.
	 *
	 * @param array|mixed $fields Fields.
	 *
	 * @return array
	 */
	public function settings_fields( $fields ): array {
		$fields             = (array) $fields;
		$fields['hcaptcha'] = [ $this, 'print_hcaptcha_field' ];

		return $fields;
	}

	/**
	 * Print hCaptcha field.
	 *
	 * @return void
	 */
	public function print_hcaptcha_field() {
		HCaptcha::form_display();

		$display = 'none';

		if ( 'invisible' === hcaptcha()->settings()->get( 'size' ) ) {
			$display = 'block';
		}

		?>
		<div id="hcaptcha-invisible-notice" style="display: <?php echo esc_attr( $display ); ?>">
			<p>
				<?php esc_html_e( 'hCaptcha is in invisible mode.', 'procaptcha-wordpress' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Ajax action to check config.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function check_config() {
		$this->run_checks( self::CHECK_CONFIG_ACTION );

		// Nonce is checked by check_ajax_referer() in run_checks().
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$ajax_mode       = isset( $_POST['mode'] ) ? sanitize_text_field( wp_unslash( $_POST['mode'] ) ) : '';
		$ajax_site_key   = isset( $_POST['siteKey'] ) ? sanitize_text_field( wp_unslash( $_POST['siteKey'] ) ) : '';
		$ajax_secret_key = isset( $_POST['secretKey'] ) ? sanitize_text_field( wp_unslash( $_POST['secretKey'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		add_filter(
			'hcap_mode',
			static function ( $mode ) use ( $ajax_mode ) {
				// @codeCoverageIgnoreStart
				return $ajax_mode;
				// @codeCoverageIgnoreEnd
			}
		);

		if ( self::MODE_LIVE === $ajax_mode ) {
			add_filter(
				'hcap_site_key',
				static function ( $site_key ) use ( $ajax_site_key ) {
					// @codeCoverageIgnoreStart
					return $ajax_site_key;
					// @codeCoverageIgnoreEnd
				}
			);
			add_filter(
				'hcap_secret_key',
				static function ( $secret_key ) use ( $ajax_secret_key ) {
					// @codeCoverageIgnoreStart
					return $ajax_secret_key;
					// @codeCoverageIgnoreEnd
				}
			);
		}

		$result = hcap_check_site_config();

		if ( $result['error'] ?? false ) {
			$this->send_check_config_error( $result['error'] );
		}

		$pro     = $result['features']['custom_theme'] ?? false;
		$license = $pro ? 'pro' : 'free';

		$this->update_option( 'license', $license );

		// Nonce is checked by check_ajax_referer() in run_checks().
		$hcaptcha_response =
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			isset( $_POST['procaptcha-response'] ) ? filter_var( wp_unslash( $_POST['procaptcha-response'] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : '';

		$result = hcaptcha_request_verify( $hcaptcha_response );

		if ( null !== $result ) {
			$this->send_check_config_error( $result, true );
		}

		wp_send_json_success(
			esc_html__( 'Site config is valid.', 'hcaptcha-for-forms-and-more' )
		);
	}

	/**
	 * Ajax action to toggle a section.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function toggle_section() {
		$this->run_checks( self::TOGGLE_SECTION_ACTION );

		// Nonce is checked by check_ajax_referer() in run_checks().
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$section = isset( $_POST['section'] ) ? sanitize_text_field( wp_unslash( $_POST['section'] ) ) : '';
		$status  =
			isset( $_POST['status'] ) ? filter_input( INPUT_POST, 'status', FILTER_VALIDATE_BOOLEAN ) : false;
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$user    = wp_get_current_user();
		$user_id = $user->ID ?? 0;

		if ( ! $user_id ) {
			wp_send_json_error( esc_html__( 'Cannot save section status.', 'procaptcha-wordpress' ) );
		}

		$hcaptcha_user_settings = array_filter(
			(array) get_user_meta( $user_id, self::USER_SETTINGS_META, true )
		);

		$hcaptcha_user_settings['sections'][ $section ] = (bool) $status;

		update_user_meta( $user_id, self::USER_SETTINGS_META, $hcaptcha_user_settings );

		wp_send_json_success();
	}

	/**
	 * Send stats if the key is switched on.
	 *
	 * @param mixed $value     New option value.
	 * @param mixed $old_value Old option value.
	 *
	 * @return mixed
	 */
	public function maybe_send_stats( $value, $old_value ) {
		$stats     = $value['statistics'][0] ?? '';
		$old_stats = $old_value['statistics'][0] ?? '';

		if ( 'on' === $stats && 'on' !== $old_stats ) {
			/**
			 * Statistics switch is turned on, send plugin statistics.
			 */
			do_action( 'hcap_send_plugin_stats' );
		}

		return $value;
	}

	/**
	 * Send check config error.
	 *
	 * @param string $error      Error message.
	 * @param bool   $raw_result Send a raw result.
	 *
	 * @return void
	 */
	private function send_check_config_error( string $error, bool $raw_result = false ) {
		$prefix = '';

		if ( ! $raw_result ) {
			$prefix = esc_html__( 'Site configuration error', 'procaptcha-wordpress' );
			$prefix = $error ? $prefix . ': ' : $prefix . '.';
		}
		wp_send_json_error( $prefix . $error );
	}

	/**
	 * Flatten array.
	 *
	 * @param array $arr Multidimensional array.
	 *
	 * @return array
	 */
	private function flatten_array( array $arr ): array {
		static $level  = [];
		static $result = [];

		foreach ( $arr as $key => $value ) {
			$level[] = $key;

			if ( is_array( $value ) ) {
				$result[] = [ implode( '--', $level ) => '' ];
				$result[] = $this->flatten_array( $value );
				array_pop( $level );
				continue;
			}

			$result[] = [ implode( '--', $level ) => $value ];
			array_pop( $level );
		}

		return array_merge( [], ...$result );
	}
}
