<?php
/**
 * Main class file.
 *
 * @package procaptcha-wp
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use Procaptcha\AutoVerify\AutoVerify;
use Procaptcha\CF7\CF7;
use Procaptcha\DelayedScript\DelayedScript;
use Procaptcha\Divi\Fix;
use Procaptcha\DownloadManager\DownloadManager;
use Procaptcha\ElementorPro\ProcaptchaHandler;
use Procaptcha\Helpers\Procaptcha;
use Procaptcha\Jetpack\JetpackForm;
use Procaptcha\Migrations\Migrations;
use Procaptcha\NF\NF;
use Procaptcha\Quform\Quform;
use Procaptcha\Sendinblue\Sendinblue;
use Procaptcha\Settings\General;
use Procaptcha\Settings\Integrations;
use Procaptcha\Settings\Settings;
use Procaptcha\Settings\SystemInfo;
use Procaptcha\WCWishlists\CreateList;
use Procaptcha\WP\PasswordProtected;

/**
 * Class Main.
 */
class Main {
	/**
	 * Main script handle.
	 */
	const HANDLE = 'procaptcha';

	/**
	 * Main script localization object.
	 */
	const OBJECT = 'ProcaptchaMainObject';

	/**
	 * Default API host.
	 */
	const API_HOST = 'js.procaptcha.io';

	/**
	 * Default verify host.
	 */
	const VERIFY_HOST = 'api.procaptcha.io';

	/**
	 * Form shown somewhere, use this flag to run the script.
	 *
	 * @var boolean
	 */
	public $form_shown = false;

	/**
	 * We have the verification result of the procap_ widget.
	 * Use this flag to send remote request only once.
	 *
	 * @var boolean
	 */
	public $has_result = false;

	/**
	 * Plugin modules.
	 *
	 * @var array
	 */
	public $modules = [];

	/**
	 * Loaded integration-related classes.
	 *
	 * @var array
	 */
	protected $loaded_classes = [];

	/**
	 * Settings class instance.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Instance of AutoVerify.
	 *
	 * @var AutoVerify
	 */
	protected $auto_verify;

	/**
	 * Whether procap_ is active.
	 *
	 * @var bool
	 */
	private $active;

	/**
	 * Input class.
	 */
	public function init() {
		if ( $this->is_xml_rpc() ) {
			return;
		}

		( new Fix() )->init();

		new Migrations();

		add_action( 'plugins_loaded', [ $this, 'init_hooks' ], -PHP_INT_MAX );
	}

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		$this->load_textdomain();

		$this->settings = new Settings(
			[
				'procap_' => [
					General::class,
					Integrations::class,
					SystemInfo::class,
				],
			]
		);

		add_action( 'plugins_loaded', [ $this, 'load_modules' ], -PHP_INT_MAX + 1 );
		add_filter( 'procap_whitelist_ip', [ $this, 'whitelist_ip' ], -PHP_INT_MAX, 2 );
		add_action( 'before_woocommerce_init', [ $this, 'declare_wc_compatibility' ] );

		$this->active = $this->activate_procaptcha();

		if ( ! $this->active ) {
			return;
		}

		add_filter( 'wp_resource_hints', [ $this, 'prefetch_procaptcha_dns' ], 10, 2 );
		add_filter( 'wp_headers', [ $this, 'csp_headers' ] );
		add_action( 'wp_head', [ $this, 'print_inline_styles' ] );
		add_action( 'login_head', [ $this, 'print_inline_styles' ] );
		add_action( 'login_head', [ $this, 'login_head' ] );
		add_action( 'wp_print_footer_scripts', [ $this, 'print_footer_scripts' ], 0 );

		$this->auto_verify = new AutoVerify();
		$this->auto_verify->init();
	}

	/**
	 * Get plugin class instance.
	 *
	 * @param string $class_name Class name.
	 *
	 * @return object|null
	 */
	public function get( string $class_name ) {
		return $this->loaded_classes[ $class_name ] ?? null;
	}

	/**
	 * Load a service class.
	 *
	 * @param string $class_name Class name.
	 *
	 * @return void
	 */
	private function load( string $class_name ) {
		$this->loaded_classes[ $class_name ] = new $class_name();
	}

	/**
	 * Get Settings instance.
	 *
	 * @return Settings
	 */
	public function settings(): Settings {
		return $this->settings;
	}

	/**
	 * Check if we have to activate the plugin.
	 *
	 * @return bool
	 */
	private function activate_procaptcha(): bool {
		$settings = $this->settings();

		/**
		 * Do not load procap_ functionality:
		 * - if a user is logged in and the option 'off_when_logged_in' is set;
		 * - for whitelisted IPs;
		 * - when the site key or the secret key is empty (after first plugin activation).
		 */
		$deactivate = (
			( is_user_logged_in() && $settings->is_on( 'off_when_logged_in' ) ) ||
			/**
			 * Filters the user IP to check whether it is whitelisted.
			 *
			 * @param bool         $whitelisted IP is whitelisted.
			 * @param string|false $ip          IP string or false for local addresses.
			 */
			apply_filters( 'procap_whitelist_ip', false, procap_get_user_ip() ) ||
			( '' === $settings->get_site_key() || '' === $settings->get_secret_key() )
		);

		$activate = ( ! $deactivate ) || $this->is_elementor_pro_edit_page();

		/**
		 * Filters the procap_ activation flag.
		 *
		 * @param bool $activate Activate the procaptcha functionality.
		 */
		return (bool) apply_filters( 'procap_activate', $activate );
	}

	/**
	 * Check if it is a Pro account.
	 *
	 * @return false
	 */
	public function is_pro(): bool {
		return false;
	}

	/**
	 * Whether we are on the Elementor Pro edit post/page and procap_ for Elementor Pro is active.
	 *
	 * @return bool
	 */
	private function is_elementor_pro_edit_page(): bool {
		if ( ! $this->settings()->is_on( 'elementor_pro_status' ) ) {
			return false;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
		$request1 = (
			isset( $_SERVER['REQUEST_URI'], $_GET['post'], $_GET['action'] ) &&
			0 === strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/wp-admin/post.php' ) &&
			'elementor' === $_GET['action']
		);
		$request2 = (
			isset( $_SERVER['REQUEST_URI'], $_GET['elementor-preview'] ) &&
			0 === strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/elementor' )
		);
		$request3 = (
			isset( $_POST['action'] ) && 'elementor_ajax' === $_POST['action']
		);

		// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing

		return $request1 || $request2 || $request3;
	}

	/**
	 * Prefetch procap_ dns.
	 * We cannot control if procap_ form is shown here, as this is hooked on wp_head.
	 * So, we always prefetch procap_ dns if procap_ is active, but it is a small overhead.
	 *
	 * @param array|mixed $urls          URLs to print for resource hints.
	 * @param string      $relation_type The relation type the URLs are printed for.
	 *
	 * @return array
	 */
	public function prefetch_procaptcha_dns( $urls, string $relation_type ): array {
		$urls = (array) $urls;

		if ( 'dns-prefetch' === $relation_type ) {
			$urls[] = 'https://procaptcha.io';
		}

		return $urls;
	}

	/**
	 * Add Content Security Policy (CSP) headers.
	 *
	 * @param array|mixed $headers Headers.
	 *
	 * @return array
	 */
	public function csp_headers( $headers ): array {
		if ( ! apply_filters( 'procap_add_csp_headers', false, $headers ) ) {
			return $headers;
		}

		$headers       = (array) $headers;
		$keys_lower    = array_map( 'strtolower', array_keys( $headers ) );
		$csp_key       = 'Content-Security-Policy';
		$csp_key_lower = strtolower( $csp_key );

		if ( ! in_array( $csp_key_lower, $keys_lower, true ) ) {
			return $headers;
		}

		$procap_src     = "'self' 'unsafe-inline' 'unsafe-eval' https://procaptcha.io https://*.procaptcha.io";
		$procap_csp     = "script-src $procap_src; frame-src $procap_src; style-src $procap_src; connect-src $procap_src";
		$procap_csp_arr = $this->parse_csp( $procap_csp );

		foreach ( $headers as $key => $header ) {
			if ( strtolower( $key ) === $csp_key_lower ) {
				$procap_csp_arr = $this->merge_csp( $procap_csp_arr, $this->parse_csp( $header ) );
			}
		}

		$procap_csp_subheaders = [];

		foreach ( $procap_csp_arr as $key => $value ) {
			$procap_csp_subheaders[] = $key . ' ' . implode( ' ', $value );
		}

		$headers[ $csp_key ] = implode( '; ', $procap_csp_subheaders );

		return $headers;
	}

	/**
	 * Parse csp header.
	 *
	 * @param string $csp CSP header.
	 *
	 * @return array
	 */
	private function parse_csp( string $csp ): array {
		$csp_subheaders = explode( ';', $csp );
		$csp_arr        = [];

		foreach ( $csp_subheaders as $csp_subheader ) {
			$csp_subheader_arr = explode( ' ', trim( $csp_subheader ) );
			$key               = (string) array_shift( $csp_subheader_arr );
			$csp_arr[ $key ]   = $csp_subheader_arr;
		}

		unset( $csp_arr[''] );

		return array_filter( $csp_arr );
	}

	/**
	 * Merge csp headers.
	 *
	 * @param array $csp_arr1 CSP headers array 1.
	 * @param array $csp_arr2 CSP headers array 2.
	 *
	 * @return array
	 */
	private function merge_csp( array $csp_arr1, array $csp_arr2 ): array {
		$csp  = [];
		$keys = array_unique( array_merge( array_keys( $csp_arr1 ), array_keys( $csp_arr2 ) ) );

		foreach ( $keys as $key ) {
			$csp1        = $csp_arr1[ $key ] ?? [];
			$csp2        = $csp_arr2[ $key ] ?? [];
			$csp[ $key ] = array_unique( array_merge( $csp1, $csp2 ) );
		}

		return $csp;
	}

	/**
	 * Print inline styles.
	 *
	 * @return void
	 * @noinspection CssUnusedSymbol
	 */
	public function print_inline_styles() {
		$div_logo_url       = HCAPTCHA_URL . '/assets/images/procaptcha-div-logo.svg';
		$div_logo_white_url = HCAPTCHA_URL . '/assets/images/procaptcha-div-logo-white.svg';

		$css = <<<CSS
	.procaptcha {
		position: relative;
		display: block;
		margin-bottom: 2rem;
		padding: 0;
		clear: both;
	}

	.procaptcha[data-size="normal"] {
		width: 303px;
		height: 78px;
	}

	.procaptcha[data-size="compact"] {
		width: 164px;
		height: 144px;
	}

	.procaptcha[data-size="invisible"] {
		display: none;
	}

	.procaptcha::before {
		content: '';
		display: block;
		position: absolute;
		top: 0;
		left: 0;
		background: url( $div_logo_url ) no-repeat;
		border: 1px solid transparent;
		border-radius: 4px;
	}

	.procaptcha[data-size="normal"]::before {
		width: 300px;
		height: 74px;
		background-position: 94% 28%;
	}

	.procaptcha[data-size="compact"]::before {
		width: 156px;
		height: 136px;
		background-position: 50% 79%;
	}

	.procaptcha[data-theme="light"]::before,
	body.is-light-theme .procaptcha[data-theme="auto"]::before,
	.procaptcha[data-theme="auto"]::before {
		background-color: #fafafa;
		border: 1px solid #e0e0e0;
	}

	.procaptcha[data-theme="dark"]::before,
	body.is-dark-theme .procaptcha[data-theme="auto"]::before,
	html.wp-dark-mode-active .procaptcha[data-theme="auto"]::before,
	html.drdt-dark-mode .procaptcha[data-theme="auto"]::before {
		background-image: url( $div_logo_white_url );
		background-repeat: no-repeat;
		background-color: #333;
		border: 1px solid #f5f5f5;
	}

	.procaptcha[data-size="invisible"]::before {
		display: none;
	}

	.procaptcha iframe {
		position: relative;
	}

	div[style*="z-index: 2147483647"] div[style*="border-width: 11px"][style*="position: absolute"][style*="pointer-events: none"] {
		border-style: none;
	}
CSS;

		Procaptcha::css_display( $css );
	}

	/**
	 * Print styles to fit procaptcha widget to the login form.
	 *
	 * @return void
	 * @noinspection CssUnusedSymbol
	 */
	public function login_head() {
		$css = <<<'CSS'
	@media (max-width: 349px) {
		.procaptcha {
			display: flex;
			justify-content: center;
		}
	}

	@media (min-width: 350px) {
		#login {
			width: 350px;
		}
	}
CSS;

		Procaptcha::css_display( $css );
	}

	/**
	 * Get API url.
	 *
	 * @return string
	 */
	public function get_api_url(): string {
		$api_host = trim( $this->settings()->get( 'api_host' ) ) ?: self::API_HOST;

		/**
		 * Filters the API host.
		 *
		 * @param string $api_host API host.
		 */
		$api_host = (string) apply_filters( 'procap_api_host', $api_host );

		$api_host = $this->force_https( $api_host );

		return "$api_host/1/api.js";
	}

	/**
	 * Force https in the hostname.
	 *
	 * @param string $host Hostname. Could be with http|https scheme, or without it.
	 *
	 * @return string
	 */
	private function force_https( string $host ): string {
		$host = preg_replace( '#(http|https)://#', '', $host );

		// We need to add scheme here, otherwise wp_parse_url returns null.
		$host = (string) wp_parse_url( 'https://' . $host, PHP_URL_HOST );

		return 'https://' . $host;
	}

	/**
	 * Get API source url with params.
	 *
	 * @return string
	 */
	public function get_api_src(): string {
		$params = [
			'onload' => 'procap_OnLoad',
			'render' => 'explicit',
		];

		$settings = $this->settings();

		if ( $settings->is_on( 'recaptcha_compat_off' ) ) {
			$params['recaptchacompat'] = 'off';
		}

		if ( $settings->is_on( 'custom_themes' ) ) {
			$params['custom'] = 'true';
		}

		$enterprise_params = [
			'asset_host' => 'assethost',
			'endpoint'   => 'endpoint',
			'host'       => 'host',
			'image_host' => 'imghost',
			'report_api' => 'reportapi',
			'sentry'     => 'sentry',
		];

		foreach ( $enterprise_params as $enterprise_param => $enterprise_arg ) {
			$value = trim( $settings->get( $enterprise_param ) );

			if ( $value ) {
				$params[ $enterprise_arg ] = rawurlencode( $this->force_https( $value ) );
			}
		}

		/**
		 * Filters the API source url with params.
		 *
		 * @param string $api_src API source url with params.
		 */
		return (string) apply_filters( 'procap_api_src', add_query_arg( $params, $this->get_api_url() ) );
	}

	/**
	 * Get verify url.
	 *
	 * @return string
	 */
	public function get_verify_url(): string {
		$verify_host = trim( $this->settings()->get( 'backend' ) ) ?: self::VERIFY_HOST;

		/**
		 * Filters the verification host.
		 *
		 * @param string $verify_host Verification host.
		 */
		$verify_host = (string) apply_filters( 'procap_verify_host', $verify_host );

		$verify_host = $this->force_https( $verify_host );

		return "$verify_host/siteverify";
	}

	/**
	 * Get check site config url.
	 *
	 * @return string
	 */
	public function get_check_site_config_url(): string {
		$verify_host = trim( $this->settings()->get( 'backend' ) ) ?: self::VERIFY_HOST;

		/** This filter is documented above. */
		$verify_host = (string) apply_filters( 'procap_verify_host', $verify_host );

		$verify_host = $this->force_https( $verify_host );

		return "$verify_host/checksiteconfig";
	}

	/**
	 * Add the procap_ script to footer.
	 *
	 * @return void
	 */
	public function print_footer_scripts() {
		$status = $this->form_shown;

		/**
		 * Filters whether to print procap_ scripts.
		 *
		 * @param bool $status Current print status.
		 */
		if ( ! apply_filters( 'procap_print_procaptcha_scripts', $status ) ) {
			return;
		}

		$settings = $this->settings();

		/**
		 * Filters delay time for the procap_ API script.
		 *
		 * Any negative value will prevent the API script from loading
		 * until user interaction: mouseenter, click, scroll or touch.
		 * This significantly improves Google Pagespeed Insights score.
		 *
		 * @param int $delay Number of milliseconds to delay procap_ API script.
		 *                   Any negative value means delay until user interaction.
		 */
		$delay = (int) apply_filters( 'procap_delay_api', (int) $settings->get( 'delay' ) );

		DelayedScript::launch( [ 'src' => $this->get_api_src() ], $delay );

		wp_enqueue_script(
			self::HANDLE,
			HCAPTCHA_URL . '/assets/js/apps/procaptcha.js',
			[],
			HCAPTCHA_VERSION,
			true
		);

		$params   = [
			'sitekey' => $settings->get_site_key(),
			'theme'   => $settings->get_theme(),
			'size'    => $settings->get( 'size' ),
		];
		$language = $settings->get_language();

		// Fix auto-detection of procap_ language.
		$language = $language ?: Procaptcha::get_procap_locale();

		if ( $language ) {
			$params['hl'] = $language;
		}

		$config_params = [];

		if ( $settings->is_on( 'custom_themes' ) ) {
			$config_params = json_decode( $settings->get( 'config_params' ), true ) ?: [];
		}

		$params = array_merge( $params, $config_params );

		wp_localize_script(
			self::HANDLE,
			self::OBJECT,
			[ 'params' => wp_json_encode( $params ) ]
		);
	}

	/**
	 * Declare compatibility with WC features.
	 *
	 * @return void
	 */
	public function declare_wc_compatibility() {
		// @codeCoverageIgnoreStart
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', constant( 'HCAPTCHA_FILE' ), true );
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Filter user IP to check if it is whitelisted.
	 * For whitelisted IPs, procap_ will not be shown.
	 *
	 * @param bool|mixed   $whitelisted Whether IP is whitelisted.
	 * @param string|false $client_ip   Client IP.
	 *
	 * @return bool|mixed
	 */
	public function whitelist_ip( $whitelisted, $client_ip ) {
		$ips = explode(
			"\n",
			$this->settings()->get( 'whitelisted_ips' )
		);

		// Remove invalid IPs.
		$ips = array_filter(
			array_map(
				static function ( $ip ) {
					return filter_var(
						trim( $ip ),
						FILTER_VALIDATE_IP
					);
				},
				$ips
			)
		);

		// Convert local IPs to false.
		$ips = array_map(
			static function ( $ip ) {
				return filter_var(
					trim( $ip ),
					FILTER_VALIDATE_IP,
					FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
				);
			},
			$ips
		);

		if ( in_array( $client_ip, $ips, true ) ) {
			return true;
		}

		return $whitelisted;
	}

	/**
	 * Load plugin modules.
	 *
	 * @return void
	 */
	public function load_modules() {
		/**
		 * Plugins modules.
		 *
		 * @var                  $modules      {
		 *
		 * @type string[]        $module0      {
		 * @type string          $option_name  Option name.
		 * @type string          $option_value Option value.
		 *                                     }
		 * @type string|string[] $module1      Plugins to be active. For WP core features, an empty string.
		 * @type string|string[] $module2      Required procap_ plugin classes.
		 *                                     }
		 */
		$this->modules = [
			'Comment Form'                         => [
				[ 'wp_status', 'comment' ],
				'',
				WP\Comment::class,
			],
			'Login Form'                           => [
				[ 'wp_status', 'login' ],
				'',
				WP\Login::class,
			],
			'Lost Password Form'                   => [
				[ 'wp_status', 'lost_pass' ],
				'',
				WP\LostPassword::class,
			],
			'Post/Page Password Form'              => [
				[ 'wp_status', 'password_protected' ],
				'',
				PasswordProtected::class,
			],
			'Register Form'                        => [
				[ 'wp_status', 'register' ],
				'',
				WP\Register::class,
			],
			'ACF Extended Form'                    => [
				[ 'acfe_status', 'form' ],
				[ 'acf-extended-pro/acf-extended.php', 'acf-extended/acf-extended.php' ],
				ACFE\Form::class,
			],
			'Affiliates Login'                     => [
				[ 'affiliates_status', 'login' ],
				[ 'affiliates/affiliates.php' ],
				Affiliates\Login::class,
			],
			'Affiliates Register'                  => [
				[ 'affiliates_status', 'register' ],
				[ 'affiliates/affiliates.php' ],
				Affiliates\Register::class,
			],
			'Asgaros Form'                         => [
				[ 'asgaros_status', 'form' ],
				'asgaros-forum/asgaros-forum.php',
				Asgaros\Form::class,
			],
			'Avada Form'                           => [
				[ 'avada_status', 'form' ],
				'Avada',
				Avada\Form::class,
			],
			'Back In Stock Notifier Form'          => [
				[ 'back_in_stock_notifier_status', 'form' ],
				'back-in-stock-notifier-for-woocommerce/cwginstocknotifier.php',
				BackInStockNotifier\Form::class,
			],
			'bbPress New Topic'                    => [
				[ 'bbp_status', 'new_topic' ],
				'bbpress/bbpress.php',
				BBPress\NewTopic::class,
			],
			'bbPress Reply'                        => [
				[ 'bbp_status', 'reply' ],
				'bbpress/bbpress.php',
				BBPress\Reply::class,
			],
			'Beaver Builder Contact Form'          => [
				[ 'beaver_builder_status', 'contact' ],
				'bb-plugin/fl-builder.php',
				BeaverBuilder\Contact::class,
			],
			'Beaver Builder Login Form'            => [
				[ 'beaver_builder_status', 'login' ],
				'bb-plugin/fl-builder.php',
				BeaverBuilder\Login::class,
			],
			'Brizy Form'                           => [
				[ 'brizy_status', 'form' ],
				'brizy/brizy.php',
				Brizy\Form::class,
			],
			'BuddyPress Create Group'              => [
				[ 'bp_status', 'create_group' ],
				'buddypress/bp-loader.php',
				BuddyPress\CreateGroup::class,
			],
			'BuddyPress Register'                  => [
				[ 'bp_status', 'registration' ],
				'buddypress/bp-loader.php',
				BuddyPress\Register::class,
			],
			'Classified Listing Contact'           => [
				[ 'classified_listing_status', 'contact' ],
				'classified-listing/classified-listing.php',
				ClassifiedListing\Contact::class,
			],
			'Classified Listing Login'             => [
				[ 'classified_listing_status', 'login' ],
				'classified-listing/classified-listing.php',
				ClassifiedListing\Login::class,
			],
			'Classified Listing Lost Password'     => [
				[ 'classified_listing_status', 'lost_pass' ],
				'classified-listing/classified-listing.php',
				ClassifiedListing\LostPassword::class,
			],
			'Classified Listing Register'          => [
				[ 'classified_listing_status', 'register' ],
				'classified-listing/classified-listing.php',
				ClassifiedListing\Register::class,
			],
			'CoBlocks Form'                        => [
				[ 'coblocks_status', 'form' ],
				'coblocks/class-coblocks.php',
				CoBlocks\Form::class,
			],
			'Colorlib Customizer Login'            => [
				[ 'colorlib_customizer_status', 'login' ],
				'colorlib-login-customizer/colorlib-login-customizer.php',
				ColorlibCustomizer\Login::class,
			],
			'Colorlib Customizer Lost Password'    => [
				[ 'colorlib_customizer_status', 'lost_pass' ],
				'colorlib-login-customizer/colorlib-login-customizer.php',
				ColorlibCustomizer\LostPassword::class,
			],
			'Colorlib Customizer Register'         => [
				[ 'colorlib_customizer_status', 'register' ],
				'colorlib-login-customizer/colorlib-login-customizer.php',
				ColorlibCustomizer\Register::class,
			],
			'Contact Form 7'                       => [
				[ 'cf7_status', 'form' ],
				'contact-form-7/wp-contact-form-7.php',
				CF7::class,
			],
			'Divi Comment Form'                    => [
				[ 'divi_status', 'comment' ],
				'Divi',
				[ Divi\Comment::class, WP\Comment::class ],
			],
			'Divi Contact Form'                    => [
				[ 'divi_status', 'contact' ],
				'Divi',
				Divi\Contact::class,
			],
			'Divi Email Optin Form'                => [
				[ 'divi_status', 'email_optin' ],
				'Divi',
				Divi\EmailOptin::class,
			],
			'Divi Login Form'                      => [
				[ 'divi_status', null ],
				'Divi',
				[ Divi\Login::class ],
			],
			'Download Manager'                     => [
				[ 'download_manager_status', 'button' ],
				'download-manager/download-manager.php',
				DownloadManager::class,
			],
			'Easy Digital Downloads Checkout'      => [
				[ 'easy_digital_downloads_status', 'checkout' ],
				'easy-digital-downloads/easy-digital-downloads.php',
				EasyDigitalDownloads\Checkout::class,
			],
			'Easy Digital Downloads Login'         => [
				[ 'easy_digital_downloads_status', 'login' ],
				'easy-digital-downloads/easy-digital-downloads.php',
				EasyDigitalDownloads\Login::class,
			],
			'Easy Digital Downloads Lost Password' => [
				[ 'easy_digital_downloads_status', 'lost_pass' ],
				'easy-digital-downloads/easy-digital-downloads.php',
				EasyDigitalDownloads\LostPassword::class,
			],
			'Easy Digital Downloads Register'      => [
				[ 'easy_digital_downloads_status', 'register' ],
				'easy-digital-downloads/easy-digital-downloads.php',
				EasyDigitalDownloads\Register::class,
			],
			'Elementor Pro Form'                   => [
				[ 'elementor_pro_status', 'form' ],
				'elementor-pro/elementor-pro.php',
				ProcaptchaHandler::class,
			],
			'Elementor Pro Login'                  => [
				[ 'elementor_pro_status', null ],
				'elementor-pro/elementor-pro.php',
				ElementorPro\Login::class,
			],
			'Fluent Forms'                         => [
				[ 'fluent_status', 'form' ],
				'fluentform/fluentform.php',
				FluentForm\Form::class,
			],
			'Formidable Forms'                     => [
				[ 'formidable_forms_status', 'form' ],
				'formidable/formidable.php',
				FormidableForms\Form::class,
			],
			'Forminator'                           => [
				[ 'forminator_status', 'form' ],
				'forminator/forminator.php',
				Forminator\Form::class,
			],
			'GiveWP'                               => [
				[ 'give_wp_status', 'form' ],
				'give/give.php',
				GiveWP\Form::class,
			],
			'Gravity Forms'                        => [
				[ 'gravity_status', null ],
				'gravityforms/gravityforms.php',
				[ GravityForms\Form::class, GravityForms\Field::class ],
			],
			'HTML Forms'                           => [
				[ 'html_forms_status', 'form' ],
				'html-forms/html-forms.php',
				HTMLForms\Form::class,
			],
			'Jetpack'                              => [
				[ 'jetpack_status', 'contact' ],
				'jetpack/jetpack.php',
				JetpackForm::class,
			],
			'Kadence Form'                         => [
				[ 'kadence_status', 'form' ],
				'kadence-blocks/kadence-blocks.php',
				Kadence\Form::class,
			],
			'Kadence Advanced Form'                => [
				[ 'kadence_status', 'advanced_form' ],
				'kadence-blocks/kadence-blocks.php',
				Kadence\AdvancedForm::class,
			],
			'LearnDash Login Form'                 => [
				[ 'learn_dash_status', 'login' ],
				'sfwd-lms/sfwd_lms.php',
				LearnDash\Login::class,
			],
			'LearnDash Lost Password Form'         => [
				[ 'learn_dash_status', 'lost_pass' ],
				'sfwd-lms/sfwd_lms.php',
				LearnDash\LostPassword::class,
			],
			'LearnDash Register Form'              => [
				[ 'learn_dash_status', 'register' ],
				'sfwd-lms/sfwd_lms.php',
				LearnDash\Register::class,
			],
			'Login/Signup Popup Login Form'        => [
				[ 'login_signup_popup_status', 'login' ],
				'easy-login-woocommerce/xoo-el-main.php',
				LoginSignupPopup\Login::class,
			],
			'Login/Signup Popup Register Form'     => [
				[ 'login_signup_popup_status', 'register' ],
				'easy-login-woocommerce/xoo-el-main.php',
				LoginSignupPopup\Register::class,
			],
			'MailChimp'                            => [
				[ 'mailchimp_status', 'form' ],
				'mailchimp-for-wp/mailchimp-for-wp.php',
				Mailchimp\Form::class,
			],
			'MailPoet'                             => [
				[ 'mailpoet_status', 'form' ],
				'mailpoet/mailpoet.php',
				MailPoet\Form::class,
			],
			'MemberPress Login'                    => [
				[ 'memberpress_status', 'login' ],
				'memberpress/memberpress.php',
				MemberPress\Login::class,
			],
			'MemberPress Register'                 => [
				[ 'memberpress_status', 'register' ],
				'memberpress/memberpress.php',
				MemberPress\Register::class,
			],
			'Ninja Forms'                          => [
				[ 'ninja_status', 'form' ],
				'ninja-forms/ninja-forms.php',
				NF::class,
			],
			'Otter Blocks'                         => [
				[ 'otter_status', 'form' ],
				'otter-blocks/otter-blocks.php',
				Otter\Form::class,
			],
			'Paid Memberships Pro Checkout'        => [
				[ 'paid_memberships_pro_status', 'checkout' ],
				'paid-memberships-pro/paid-memberships-pro.php',
				PaidMembershipsPro\Checkout::class,
			],
			'Paid Memberships Pro Login'           => [
				[ 'paid_memberships_pro_status', null ],
				'paid-memberships-pro/paid-memberships-pro.php',
				PaidMembershipsPro\Login::class,
			],
			'Passster Protect'                     => [
				[ 'passster_status', 'protect' ],
				'content-protector/content-protector.php',
				Passster\Protect::class,
			],
			'Profile Builder Login'                => [
				[ 'profile_builder_status', 'login' ],
				'profile-builder/index.php',
				ProfileBuilder\Login::class,
			],
			'Profile Builder Register'             => [
				[ 'profile_builder_status', 'register' ],
				'profile-builder/index.php',
				ProfileBuilder\Register::class,
			],
			'Profile Builder Recover Password'     => [
				[ 'profile_builder_status', 'lost_pass' ],
				'profile-builder/index.php',
				ProfileBuilder\LostPassword::class,
			],
			'Quform'                               => [
				[ 'quform_status', 'form' ],
				'quform/quform.php',
				Quform::class,
			],
			'Sendinblue'                           => [
				[ 'sendinblue_status', 'form' ],
				'mailin/sendinblue.php',
				Sendinblue::class,
			],
			'Simple Basic Contact Form'            => [
				[ 'simple_basic_contact_form_status', 'form' ],
				'simple-basic-contact-form/simple-basic-contact-form.php',
				SimpleBasicContactForm\Form::class,
			],
			'Simple Download Monitor'              => [
				[ 'simple_download_monitor_status', 'form' ],
				'simple-download-monitor/main.php',
				SimpleDownloadMonitor\Form::class,
			],
			'Spectra'                              => [
				[ 'spectra_status', 'form' ],
				'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php',
				Spectra\Form::class,
			],
			'Subscriber'                           => [
				[ 'subscriber_status', 'form' ],
				'subscriber/subscriber.php',
				Subscriber\Form::class,
			],
			'Support Candy Form'                   => [
				[ 'supportcandy_status', 'form' ],
				'supportcandy/supportcandy.php',
				SupportCandy\Form::class,
			],
			'Theme My Login Login'                 => [
				[ 'theme_my_login_status', 'login' ],
				'theme-my-login/theme-my-login.php',
				ThemeMyLogin\Login::class,
			],
			'Theme My Login LostPassword'          => [
				[ 'theme_my_login_status', 'lost_pass' ],
				'theme-my-login/theme-my-login.php',
				ThemeMyLogin\LostPassword::class,
			],
			'Theme My Login Register'              => [
				[ 'theme_my_login_status', 'register' ],
				'theme-my-login/theme-my-login.php',
				ThemeMyLogin\Register::class,
			],
			'Ultimate Member Login'                => [
				[ 'ultimate_member_status', 'login' ],
				'ultimate-member/ultimate-member.php',
				UM\Login::class,
			],
			'Ultimate Member LostPassword'         => [
				[ 'ultimate_member_status', 'lost_pass' ],
				'ultimate-member/ultimate-member.php',
				UM\LostPassword::class,
			],
			'Ultimate Member Register'             => [
				[ 'ultimate_member_status', 'register' ],
				'ultimate-member/ultimate-member.php',
				UM\Register::class,
			],
			'UsersWP Forgot Password'              => [
				[ 'users_wp_status', 'forgot' ],
				'userswp/userswp.php',
				UsersWP\ForgotPassword::class,
			],
			'UsersWP Login'                        => [
				[ 'users_wp_status', 'login' ],
				'userswp/userswp.php',
				UsersWP\Login::class,
			],
			'UsersWP Register'                     => [
				[ 'users_wp_status', 'register' ],
				'userswp/userswp.php',
				UsersWP\Register::class,
			],
			'WooCommerce Checkout'                 => [
				[ 'woocommerce_status', 'checkout' ],
				'woocommerce/woocommerce.php',
				WC\Checkout::class,
			],
			'WooCommerce Login'                    => [
				[ 'woocommerce_status', 'login' ],
				'woocommerce/woocommerce.php',
				WC\Login::class,
			],
			'WooCommerce Lost Password'            => [
				[ 'woocommerce_status', 'lost_pass' ],
				'woocommerce/woocommerce.php',
				[ WP\LostPassword::class, WC\LostPassword::class ],
			],
			'WooCommerce Order Tracking'           => [
				[ 'woocommerce_status', 'order_tracking' ],
				'woocommerce/woocommerce.php',
				WC\OrderTracking::class,
			],
			'WooCommerce Register'                 => [
				[ 'woocommerce_status', 'register' ],
				'woocommerce/woocommerce.php',
				WC\Register::class,
			],
			'WooCommerce Wishlists'                => [
				[ 'woocommerce_wishlists_status', 'create_list' ],
				'woocommerce-wishlists/woocommerce-wishlists.php',
				CreateList::class,
			],
			'Wordfence Login'                      => [
				[ 'wordfence_status', null ],
				[ 'wordfence/wordfence.php', 'wordfence-login-security/wordfence-login-security.php' ],
				Wordfence\General::class,
			],
			'WP Job Openings'                      => [
				[ 'wp_job_openings_status', 'form' ],
				'wp-job-openings/wp-job-openings.php',
				WPJobOpenings\Form::class,
			],
			'WPForms'                              => [
				[ 'wpforms_status', null ],
				[ 'wpforms/wpforms.php', 'wpforms-lite/wpforms.php' ],
				WPForms\Form::class,
			],
			'wpDiscuz Comment'                     => [
				[ 'wpdiscuz_status', 'comment_form' ],
				'wpdiscuz/class.WpdiscuzCore.php',
				WPDiscuz\Comment::class,
			],
			'wpDiscuz Subscribe'                   => [
				[ 'wpdiscuz_status', 'subscribe_form' ],
				'wpdiscuz/class.WpdiscuzCore.php',
				WPDiscuz\Subscribe::class,
			],
			'wpForo New Topic'                     => [
				[ 'wpforo_status', 'new_topic' ],
				'wpforo/wpforo.php',
				WPForo\NewTopic::class,
			],
			'wpForo Reply'                         => [
				[ 'wpforo_status', 'reply' ],
				'wpforo/wpforo.php',
				WPForo\Reply::class,
			],
		];

		if ( ! function_exists( 'is_plugin_active' ) ) {
			// @codeCoverageIgnoreStart
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			// @codeCoverageIgnoreEnd
		}

		foreach ( $this->modules as $module ) {
			list( $option_name, $option_value ) = $module[0];

			$option = (array) $this->settings()->get( $option_name );

			if ( ! $this->plugin_or_theme_active( $module[1] ) ) {
				$this->settings()->set_field( $option_name, 'disabled', true );
				continue;
			}

			// If plugin/theme is active, load a class having the option_value specified or null.
			if ( $option_value && ! in_array( $option_value, $option, true ) ) {
				continue;
			}

			if ( ! $this->active ) {
				continue;
			}

			foreach ( (array) $module[2] as $component ) {
				if ( ! class_exists( $component, false ) ) {
					$this->loaded_classes[ $component ] = new $component();
				}
			}
		}
	}

	/**
	 * Check whether one of the plugins or themes is active.
	 *
	 * @param string|array $plugin_or_theme_names Plugin or theme names.
	 *
	 * @return bool
	 */
	private function plugin_or_theme_active( $plugin_or_theme_names ): bool {
		foreach ( (array) $plugin_or_theme_names as $plugin_or_theme_name ) {
			if ( '' === $plugin_or_theme_name ) {
				// WP Core is always active.
				return true;
			}

			if (
				false !== strpos( $plugin_or_theme_name, '.php' ) &&
				is_plugin_active( $plugin_or_theme_name )
			) {
				// The plugin is active.
				return true;
			}

			if (
				false === strpos( $plugin_or_theme_name, '.php' ) &&
				get_template() === $plugin_or_theme_name
			) {
				// The theme is active.
				return true;
			}
		}

		return false;
	}

	/**
	 * Load plugin text domain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_default_textdomain();
		load_plugin_textdomain(
			'procaptcha-wordpress',
			false,
			dirname( plugin_basename( HCAPTCHA_FILE ) ) . '/languages/'
		);
	}

	/**
	 * Check if it is the xml-rpc request.
	 *
	 * @return bool
	 */
	protected function is_xml_rpc(): bool {
		return defined( 'XMLRPC_REQUEST' ) && constant( 'XMLRPC_REQUEST' );
	}
}
