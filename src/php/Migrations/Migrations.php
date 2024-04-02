<?php
/**
 * Migrations class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\Migrations;

use Procaptcha\Admin\Events\Events;

/**
 * Migrations class.
 */
class Migrations {

	/**
	 * Migrated versions options name.
	 */
	const MIGRATED_VERSIONS_OPTION_NAME = 'procaptcha_versions';

	/**
	 * Plugin version.
	 */
	const PLUGIN_VERSION = HCAPTCHA_VERSION;

	/**
	 * Migration started status.
	 */
	const STARTED = - 1;

	/**
	 * Migration failed status.
	 */
	const FAILED = - 2;

	/**
	 * Plugin name.
	 */
	const PLUGIN_NAME = 'procap_ Plugin';

	/**
	 * Migration constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Init class.
	 *
	 * @return void
	 */
	public function init() {
		if ( ! $this->is_allowed() ) {
			return;
		}

		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', [ $this, 'migrate' ], - PHP_INT_MAX );
	}

	/**
	 * Migrate.
	 *
	 * @return void
	 */
	public function migrate() {
		$migrated = get_option( self::MIGRATED_VERSIONS_OPTION_NAME, [] );

		$migrations       = array_filter(
			get_class_methods( $this ),
			static function ( $migration ) {
				return false !== strpos( $migration, 'migrate_' );
			}
		);
		$upgrade_versions = [];

		foreach ( $migrations as $migration ) {
			$upgrade_version    = $this->get_upgrade_version( $migration );
			$upgrade_versions[] = $upgrade_version;

			if (
				( isset( $migrated[ $upgrade_version ] ) && $migrated[ $upgrade_version ] >= 0 ) ||
				version_compare( $upgrade_version, self::PLUGIN_VERSION, '>' )
			) {
				continue;
			}

			if ( ! isset( $migrated[ $upgrade_version ] ) ) {
				$migrated[ $upgrade_version ] = static::STARTED;

				$this->log( sprintf( 'Migration of %1$s to %2$s started.', self::PLUGIN_NAME, $upgrade_version ) );
			}

			// Run migration.
			$result = $this->{$migration}();

			// Some migration methods can be called several times to support AS action,
			// so do not log their completion here.
			if ( null === $result ) {
				// @codeCoverageIgnoreStart
				continue;
				// @codeCoverageIgnoreEnd
			}

			$migrated[ $upgrade_version ] = $result ? time() : static::FAILED;

			$this->log_migration_message( $result, $upgrade_version );
		}

		// Remove any keys that are not in the migrations list.
		$migrated = array_intersect_key( $migrated, array_flip( $upgrade_versions ) );

		// Store the current version.
		$migrated[ self::PLUGIN_VERSION ] = $migrated[ self::PLUGIN_VERSION ] ?? time();

		// Sort the array by version.
		uksort( $migrated, 'version_compare' );

		update_option( self::MIGRATED_VERSIONS_OPTION_NAME, $migrated );
	}

	/**
	 * Determine if migration is allowed.
	 */
	public function is_allowed(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['service-worker'] ) ) {
			return false;
		}

		return wp_doing_cron() || is_admin() || ( defined( 'WP_CLI' ) && constant( 'WP_CLI' ) );
	}

	/**
	 * Get an upgrade version from the method name.
	 *
	 * @param string $method Method name.
	 *
	 * @return string
	 */
	private function get_upgrade_version( string $method ): string {
		// Find only the digits and underscores to get version number.
		if ( ! preg_match( '/(\d_?)+/', $method, $matches ) ) {
			// @codeCoverageIgnoreStart
			return '';
			// @codeCoverageIgnoreEnd
		}

		$raw_version = $matches[0];

		if ( strpos( $raw_version, '_' ) ) {
			// Modern notation: 3_10_0 means 3.10.0 version.

			// @codeCoverageIgnoreStart
			return str_replace( '_', '.', $raw_version );
			// @codeCoverageIgnoreEnd
		}

		// Legacy notation, with 1-digit subversion numbers: 360 means 3.6.0 version.
		return implode( '.', str_split( $raw_version ) );
	}

	/**
	 * Output message into log file.
	 *
	 * @param string $message Message to log.
	 *
	 * @return void
	 * @noinspection ForgottenDebugOutputInspection
	 */
	private function log( string $message ) {
		if ( ! ( defined( 'WP_DEBUG' ) && constant( 'WP_DEBUG' ) ) ) {
			// @codeCoverageIgnoreStart
			return;
			// @codeCoverageIgnoreEnd
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( self::PLUGIN_NAME . ':  ' . $message );
	}

	/**
	 * Log migration message.
	 *
	 * @param bool   $migrated        Migration status.
	 * @param string $upgrade_version Upgrade version.
	 *
	 * @return void
	 */
	private function log_migration_message( bool $migrated, string $upgrade_version ) {

		$message = $migrated ?
			sprintf( 'Migration of %1$s to %2$s completed.', self::PLUGIN_NAME, $upgrade_version ) :
			// @codeCoverageIgnoreStart
			sprintf( 'Migration of %1$s to %2$s failed.', self::PLUGIN_NAME, $upgrade_version );
		// @codeCoverageIgnoreEnd

		$this->log( $message );
	}

	/**
	 * Migrate to 2.0.0
	 *
	 * @return bool|null
	 * @noinspection MultiAssignmentUsageInspection
	 * @noinspection PhpUnused
	 */
	protected function migrate_200() {
		$options_map = [
			'procaptcha_api_key'                     => 'site_key',
			'procaptcha_secret_key'                  => 'secret_key',
			'procaptcha_theme'                       => 'theme',
			'procaptcha_size'                        => 'size',
			'procaptcha_language'                    => 'language',
			'procaptcha_off_when_logged_in'          => [ 'off_when_logged_in', 'on' ],
			'procaptcha_recaptchacompat'             => [ 'recaptcha_compat_off', 'on' ],
			'procaptcha_cmf_status'                  => [ 'wp_status', 'comment' ],
			'procaptcha_lf_status'                   => [ 'wp_status', 'login' ],
			'procaptcha_lpf_status'                  => [ 'wp_status', 'lost_pass' ],
			'procaptcha_rf_status'                   => [ 'wp_status', 'register' ],
			'procaptcha_bbp_new_topic_status'        => [ 'bbp_status', 'new_topic' ],
			'procaptcha_bbp_reply_status'            => [ 'bbp_status', 'reply' ],
			'procaptcha_bp_create_group_status'      => [ 'bp_status', 'create_group' ],
			'procaptcha_bp_reg_status'               => [ 'bp_status', 'registration' ],
			'procaptcha_cf7_status'                  => [ 'cf7_status', 'form' ],
			'procaptcha_divi_cmf_status'             => [ 'divi_status', 'comment' ],
			'procaptcha_divi_cf_status'              => [ 'divi_status', 'contact' ],
			'procaptcha_divi_lf_status'              => [ 'divi_status', 'login' ],
			'procaptcha_elementor__pro_form_status'  => [ 'elementor_pro_status', 'form' ],
			'procaptcha_fluentform_status'           => [ 'fluent_status', 'form' ],
			'procaptcha_gravityform_status'          => [ 'gravity_status', 'form' ],
			'procaptcha_jetpack_cf_status'           => [ 'jetpack_status', 'contact' ],
			'procaptcha_mc4wp_status'                => [ 'mailchimp_status', 'form' ],
			'procaptcha_memberpress_register_status' => [ 'memberpress_status', 'register' ],
			'procaptcha_nf_status'                   => [ 'ninja_status', 'form' ],
			'procaptcha_subscribers_status'          => [ 'subscriber_status', 'form' ],
			'procaptcha_um_login_status'             => [ 'ultimate_member_status', 'login' ],
			'procaptcha_um_lost_pass_status'         => [ 'ultimate_member_status', 'lost_pass' ],
			'procaptcha_um_register_status'          => [ 'ultimate_member_status', 'register' ],
			'procaptcha_wc_checkout_status'          => [ 'woocommerce_status', 'checkout' ],
			'procaptcha_wc_login_status'             => [ 'woocommerce_status', 'login' ],
			'procaptcha_wc_lost_pass_status'         => [ 'woocommerce_status', 'lost_pass' ],
			'procaptcha_wc_order_tracking_status'    => [ 'woocommerce_status', 'order_tracking' ],
			'procaptcha_wc_reg_status'               => [ 'woocommerce_status', 'register' ],
			'procaptcha_wc_wl_create_list_status'    => [ 'woocommerce_wishlists_status', 'create_list' ],
			'procaptcha_wpforms_status'              => [ 'wpforms_status', 'lite' ],
			'procaptcha_wpforms_pro_status'          => [ 'wpforms_status', 'pro' ],
			'procaptcha_wpforo_new_topic_status'     => [ 'wpforo_status', 'new_topic' ],
			'procaptcha_wpforo_reply_status'         => [ 'wpforo_status', 'reply' ],
		];

		$new_options = [];

		foreach ( $options_map as $old_option_name => $new_option_name ) {
			$old_option = get_option( $old_option_name, '' );

			if ( ! is_array( $new_option_name ) ) {
				$new_options[ $new_option_name ] = $old_option;
				continue;
			}

			list( $new_option_key, $new_option_value ) = $new_option_name;

			$new_options[ $new_option_key ] = $new_options[ $new_option_key ] ?? [];

			if ( 'on' === $old_option ) {
				$new_options[ $new_option_key ][] = $new_option_value;
			}
		}

		update_option( 'procaptcha_settings', $new_options );

		foreach ( array_keys( $options_map ) as $old_option_name ) {
			delete_option( $old_option_name );
		}

		return true;
	}

	/**
	 * Migrate to 3.6.0
	 *
	 * @return bool|null
	 * @noinspection PhpUnused
	 */
	protected function migrate_360() {
		$option         = get_option( 'procaptcha_settings', [] );
		$wpforms_status = $option['wpforms_status'] ?? [];

		if ( empty( $wpforms_status ) ) {
			return true;
		}

		// Convert any WPForms status ('lite' or 'pro') to the status 'form'.
		$option['wpforms_status'] = [ 'form' ];

		update_option( 'procaptcha_settings', $option );

		return true;
	}
}
