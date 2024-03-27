<?php
/**
 * General class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\Settings;

use PROCAPTCHA\Helpers\PROCAPTCHA;
use KAGG\Settings\Abstracts\SettingsBase;

/**
 * Class General
 *
 * Settings page "General".
 */
class General extends PluginSettingsBase {

	/**
	 * Admin script handle.
	 */
	const HANDLE = 'procaptcha-general';

	/**
	 * Script localization object.
	 */
	const OBJECT = 'PROCAPTCHAGeneralObject';

	/**
	 * Check config ajax action.
	 */
	const CHECK_CONFIG_ACTION = 'procaptcha-general-check-config';

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
	 * Other section id.
	 */
	const SECTION_OTHER = 'other';

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

		$procaptcha = procaptcha();
		add_action( 'admin_head', [ $procaptcha, 'print_inline_styles' ] );
		add_action( 'admin_print_footer_scripts', [ $procaptcha, 'print_footer_scripts' ], 0 );

		add_filter( 'kagg_settings_fields', [ $this, 'settings_fields' ] );
		add_action( 'wp_ajax_' . self::CHECK_CONFIG_ACTION, [ $this, 'check_config' ] );
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
			'sample_procaptcha'      => [
				'label'   => __( 'Active proCAPTCHA to Check Site Config', 'procaptcha-wordpress' ),
				'type'    => 'procaptcha',
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
					// 'auto'  => __( 'Auto', 'procaptcha-wordpress' ),
				],
				'helper'  => __( 'Select proCAPTCHA theme.', 'procaptcha-wordpress' ),
			],
			
			'custom_themes'        => [
				'label'   => __( 'Custom Themes', 'procaptcha-wordpress' ),
				'type'    => 'checkbox',
				'section' => self::SECTION_CUSTOM,
				'options' => [
					'on' => __( 'Enable Custom Themes', 'procaptcha-wordpress' ),
				],
				'helper'  => sprintf(
				/* translators: 1: Procaptcha Premium link, 2: proCAPTCHA Enterprise link. */
					__( 'Note: only works on proCAPTCHA %1$s and %2$s site keys.', 'procaptcha-wordpress' ),
					sprintf(
						'<a href="https://www.procaptcha.com/pro?utm_source=wordpress&utm_medium=wpplugin&utm_campaign=upgrade" target="_blank">%s</a>',
						__( 'Pro', 'procaptcha-wordpress' )
					),
					sprintf(
						'<a href="https://www.procaptcha.com/enterprise?utm_source=wordpress&utm_medium=wpplugin&utm_campaign=upgrade" target="_blank">%s</a>',
						__( 'Enterprise', 'procaptcha-wordpress' )
					)
				),
			],
			'config_params'        => [
				'label'   => __( 'Config Params', 'procaptcha-wordpress' ),
				'type'    => 'textarea',
				'section' => self::SECTION_CUSTOM,
				'helper'  => sprintf(
				/* translators: 1: proCAPTCHA render params doc link. */
					__( 'proCAPTCHA render %s (optional). Must be a valid JSON.', 'procaptcha-wordpress' ),
					sprintf(
						'<a href="https://docs.procaptcha.com/configuration/#procaptcharendercontainer-params?utm_source=wordpress&utm_medium=wpplugin&utm_campaign=docs" target="_blank">%s</a>',
						__( 'parameters', 'procaptcha-wordpress' )
					)
				),
			],
			'off_when_logged_in'   => [
				'label'   => __( 'Other Settings', 'procaptcha-wordpress' ),
				'type'    => 'checkbox',
				'section' => self::SECTION_OTHER,
				'options' => [
					'on' => __( 'Turn Off When Logged In', 'procaptcha-wordpress' ),
				],
				'helper'  => __( 'Do not show proCAPTCHA to logged-in users.', 'procaptcha-wordpress' ),
			],
			'recaptcha_compat_off' => [
				'type'    => 'checkbox',
				'section' => self::SECTION_OTHER,
				'options' => [
					'on' => __( 'Disable reCAPTCHA Compatibility', 'procaptcha-wordpress' ),
				],
				'helper'  => __( 'Use if including both proCAPTCHA and reCAPTCHA on the same page.', 'procaptcha-wordpress' ),
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
				'helper'  => __( 'Do not show proCAPTCHA for listed IP addresses. Please specify one IP address per line.', 'procaptcha-wordpress' ),
			],
			'login_limit'          => [
				'label'   => __( 'Login attempts before proCAPTCHA', 'procaptcha-wordpress' ),
				'type'    => 'number',
				'section' => self::SECTION_OTHER,
				'default' => 0,
				'min'     => 0,
				'helper'  => __( 'Maximum number of failed login attempts before showing proCAPTCHA.', 'procaptcha-wordpress' ),
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
				'label'   => __( 'Delay showing proCAPTCHA, ms', 'procaptcha-wordpress' ),
				'type'    => 'number',
				'section' => self::SECTION_OTHER,
				'default' => -100,
				'min'     => -100,
				'step'    => 100,
				'helper'  => __( 'Delay time for loading the proCAPTCHA API script. Any negative value will prevent the API script from loading until user interaction: mouseenter, click, scroll or touch. This significantly improves Google Pagespeed Insights score.', 'procaptcha-wordpress' ),
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

		// In Settings, a filter applied for mode.
		$mode = procaptcha()->settings()->get_mode();

		if ( self::MODE_LIVE !== $mode ) {
			$this->form_fields['site_key']['disabled']   = true;
			$this->form_fields['secret_key']['disabled'] = true;
		}

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
				<div id="procaptcha-message"></div>
				<?php
				procaptcha()->notifications()->show();
				$this->print_section_header( $arguments['id'], __( 'Keys', 'procaptcha-wordpress' ) );
				break;
			case self::SECTION_APPEARANCE:
				$this->print_section_header( $arguments['id'], __( 'Appearance', 'procaptcha-wordpress' ) );
				break;
			case self::SECTION_CUSTOM:
				$this->print_section_header( $arguments['id'], __( 'Custom', 'procaptcha-wordpress' ) );
				break;
			case self::SECTION_OTHER:
				$this->print_section_header( $arguments['id'], __( 'Other', 'procaptcha-wordpress' ) );
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
		?>
		<h3 class="procaptcha-section-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $title ); ?></h3>
		<?php
	}

	/**
	 * Enqueue class scripts.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script(
			self::HANDLE,
			constant( 'PROCAPTCHA_URL' ) . "/assets/js/general$this->min_prefix.js",
			[ 'jquery' ],
			constant( 'PROCAPTCHA_VERSION' ),
			true
		);

		$check_config_notice =
			esc_html__( 'Credentials changed.', 'procaptcha-wordpress' ) . "\n" .
			esc_html__( 'Please complete proCAPTCHA and check the site config.', 'procaptcha-wordpress' );

		wp_localize_script(
			self::HANDLE,
			self::OBJECT,
			[
				'ajaxUrl'                              => admin_url( 'admin-ajax.php' ),
				'checkConfigAction'                    => self::CHECK_CONFIG_ACTION,
				'nonce'                                => wp_create_nonce( self::CHECK_CONFIG_ACTION ),
				'modeLive'                             => self::MODE_LIVE,
				'modeTestPublisher'                    => self::MODE_TEST_PUBLISHER,
				'modeTestEnterpriseSafeEndUser'        => self::MODE_TEST_ENTERPRISE_SAFE_END_USER,
				'modeTestEnterpriseBotDetected'        => self::MODE_TEST_ENTERPRISE_BOT_DETECTED,
				'siteKey'                              => procaptcha()->settings()->get( 'site_key' ),
				'modeTestPublisherSiteKey'             => self::MODE_TEST_PUBLISHER_SITE_KEY,
				'modeTestEnterpriseSafeEndUserSiteKey' => self::MODE_TEST_ENTERPRISE_SAFE_END_USER_SITE_KEY,
				'modeTestEnterpriseBotDetectedSiteKey' => self::MODE_TEST_ENTERPRISE_BOT_DETECTED_SITE_KEY,
				'checkConfigNotice'                    => $check_config_notice,
			]
		);

		wp_enqueue_style(
			self::HANDLE,
			constant( 'PROCAPTCHA_URL' ) . "/assets/css/general$this->min_prefix.css",
			[ static::PREFIX . '-' . SettingsBase::HANDLE ],
			constant( 'PROCAPTCHA_VERSION' )
		);
	}

	/**
	 * Add custom proCAPTCHA field.
	 *
	 * @param array|mixed $fields Fields.
	 *
	 * @return array
	 */
	public function settings_fields( $fields ): array {
		$fields             = (array) $fields;
		$fields['procaptcha'] = [ $this, 'print_procaptcha_field' ];

		return $fields;
	}

	/**
	 * Print proCAPTCHA field.
	 *
	 * @return void
	 */
	public function print_procaptcha_field() {
		PROCAPTCHA::form_display();

		$display = 'none';

		if ( 'invisible' === procaptcha()->settings()->get( 'size' ) ) {
			$display = 'block';
		}

		?>
		<div id="procaptcha-invisible-notice" style="display: <?php echo esc_attr( $display ); ?>">
			<p>
				<?php esc_html_e( 'proCAPTCHA is in invisible mode.', 'procaptcha-wordpress' ); ?>
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
		
		// Run a security check.
		if ( ! check_ajax_referer( self::CHECK_CONFIG_ACTION, 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Your session has expired. Please reload the page.', 'procaptcha-wordpress' ) );
		}

		// Check for permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'procaptcha-wordpress' ) );
		}

		$ajax_mode       = isset( $_POST['mode'] ) ? sanitize_text_field( wp_unslash( $_POST['mode'] ) ) : '';
		$ajax_site_key   = isset( $_POST['siteKey'] ) ? sanitize_text_field( wp_unslash( $_POST['siteKey'] ) ) : '';

		add_filter(
			'procap_mode',
			static function ( $mode ) use ( $ajax_mode ) {
				return $ajax_mode;
			}
		);
		add_filter(
			'procap_site_key',
			static function ( $site_key ) use ( $ajax_site_key ) {
				return $ajax_site_key;
			}
		);
		

		$settings = procaptcha()->settings();
		if(empty($settings->get_site_key())){
			$this->send_check_config_error( __( 'Site key is Empty.', 'procaptcha-wordpress' ) );
		}
		$params   = [
			'address' => $settings->get_site_key(),
		];
		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://api.prosopo.io/siteverify',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS =>json_encode($params),
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json'
		),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$response= json_decode($response,true);
		
	
		if(isset($response['error'])){
			$this->send_check_config_error( __( 'Site key Invalid.', 'procaptcha-wordpress' ) );	
		}
		if(isset($response['dapp']['status']) && $response['dapp']['status']=='Active'){
			wp_send_json_success(
				esc_html__( 'Site config is valid.', 'procaptcha-wordpress' )
			);	
		}
		$this->send_check_config_error( __( 'Site key Invalid.', 'procaptcha-wordpress' ) );

		
	}

	/**
	 * Send check config error.
	 *
	 * @param string $error Error message.
	 *
	 * @return void
	 */
	private function send_check_config_error( $error ) {
		wp_send_json_error(
			esc_html__( 'Site configuration error: ', 'procaptcha-wordpress' ) . $error
		);
	}
}
