<?php
/**
 * Integrations class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\Settings;

use KAGG\Settings\Abstracts\SettingsBase;
use WP_Theme;

/**
 * Class Integrations
 *
 * Settings page "Integrations".
 */
class Integrations extends PluginSettingsBase {

	/**
	 * Admin script handle.
	 */
	const HANDLE = 'procaptcha-integrations';

	/**
	 * Script localization object.
	 */
	const OBJECT = 'PROCAPTCHAIntegrationsObject';

	/**
	 * Activate plugin ajax action.
	 */
	const ACTIVATE_ACTION = 'procaptcha-integrations-activate';

	/**
	 * Enabled section id.
	 */
	const SECTION_ENABLED = 'enabled';

	/**
	 * Disabled section id.
	 */
	const SECTION_DISABLED = 'disabled';

	/**
	 * Get page title.
	 *
	 * @return string
	 */
	protected function page_title(): string {
		return __( 'Integrations', 'procaptcha-wordpress' );
	}

	/**
	 * Get section title.
	 *
	 * @return string
	 */
	protected function section_title(): string {
		return 'integrations';
	}

	/**
	 * Init class hooks.
	 */
	protected function init_hooks() {
		parent::init_hooks();

		add_action( 'wp_ajax_' . self::ACTIVATE_ACTION, [ $this, 'activate' ] );
	}

	/**
	 * Init form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = [
			'wp_status'                        => [
				'entity'  => 'core',
				'label'   => 'WP Core',
				'type'    => 'checkbox',
				'options' => [
					'comment'            => __( 'Comment Form', 'procaptcha-wordpress' ),
					'login'              => __( 'Login Form', 'procaptcha-wordpress' ),
					'lost_pass'          => __( 'Lost Password Form', 'procaptcha-wordpress' ),
					'password_protected' => __( 'Post/Page Password Form', 'procaptcha-wordpress' ),
					'register'           => __( 'Register Form', 'procaptcha-wordpress' ),
				],
			],
			'acfe_status'                      => [
				'label'   => 'ACF Extended',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'ACF Extended Form', 'procaptcha-wordpress' ),
				],
			],
			'asgaros_status'                   => [
				'label'   => 'Asgaros',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'avada_status'                     => [
				'entity'  => 'theme',
				'label'   => 'Avada',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Avada Form', 'procaptcha-wordpress' ),
				],
			],
			'back_in_stock_notifier_status'    => [
				'label'   => 'Back In Stock Notifier',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Back In Stock Notifier Form', 'procaptcha-wordpress' ),
				],
			],
			'bbp_status'                       => [
				'label'   => 'bbPress',
				'type'    => 'checkbox',
				'options' => [
					'new_topic' => __( 'New Topic Form', 'procaptcha-wordpress' ),
					'reply'     => __( 'Reply Form', 'procaptcha-wordpress' ),
				],
			],
			'beaver_builder_status'            => [
				'label'   => 'Beaver Builder',
				'type'    => 'checkbox',
				'options' => [
					'contact' => __( 'Contact Form', 'procaptcha-wordpress' ),
					'login'   => __( 'Login Form', 'procaptcha-wordpress' ),
				],
			],
			'brizy_status'                     => [
				'label'   => 'Brizy',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'bp_status'                        => [
				'label'   => 'BuddyPress',
				'type'    => 'checkbox',
				'options' => [
					'create_group' => __( 'Create Group Form', 'procaptcha-wordpress' ),
					'registration' => __( 'Register Form', 'procaptcha-wordpress' ),
				],
			],
			'classified_listing_status'        => [
				'label'   => 'Classified Listing',
				'type'    => 'checkbox',
				'options' => [
					'contact'   => __( 'Contact Form', 'procaptcha-wordpress' ),
					'login'     => __( 'Login Form', 'procaptcha-wordpress' ),
					'lost_pass' => __( 'Lost Password Form', 'procaptcha-wordpress' ),
					'register'  => __( 'Register Form', 'procaptcha-wordpress' ),
				],
			],
			'colorlib_customizer_status'       => [
				'label'   => 'Colorlib Login Customizer',
				'type'    => 'checkbox',
				'options' => [
					'login'     => __( 'Login Form', 'procaptcha-wordpress' ),
					'lost_pass' => __( 'Lost Password Form', 'procaptcha-wordpress' ),
					'register'  => __( 'Register Form', 'procaptcha-wordpress' ),
				],
			],
			'cf7_status'                       => [
				'label'   => 'Contact Form 7',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'divi_status'                      => [
				'entity'  => 'theme',
				'label'   => 'Divi',
				'type'    => 'checkbox',
				'options' => [
					'comment'     => __( 'Divi Comment Form', 'procaptcha-wordpress' ),
					'contact'     => __( 'Divi Contact Form', 'procaptcha-wordpress' ),
					'email_optin' => __( 'Divi Email Optin Form', 'procaptcha-wordpress' ),
					'login'       => __( 'Divi Login Form', 'procaptcha-wordpress' ),
				],
			],
			'download_manager_status'          => [
				'label'   => 'Download Manager',
				'type'    => 'checkbox',
				'options' => [
					'button' => __( 'Button', 'procaptcha-wordpress' ),
				],
			],
			'easy_digital_downloads_status'    => [
				'label'   => 'Easy Digital Downloads',
				'type'    => 'checkbox',
				'options' => [
					'checkout'  => __( 'Checkout Form', 'procaptcha-wordpress' ),
					'login'     => __( 'Login Form', 'procaptcha-wordpress' ),
					'lost_pass' => __( 'Lost Password Form', 'procaptcha-wordpress' ),
					'register'  => __( 'Register Form', 'procaptcha-wordpress' ),
				],
			],
			'elementor_pro_status'             => [
				'label'   => 'Elementor Pro',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'fluent_status'                    => [
				'label'   => 'Fluent Forms',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'formidable_forms_status'          => [
				'label'   => 'Formidable Forms',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'forminator_status'                => [
				'label'   => 'Forminator',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'give_wp_status'                   => [
				'label'   => 'GiveWP',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'gravity_status'                   => [
				'label'   => 'Gravity Forms',
				'type'    => 'checkbox',
				'options' => [
					'form'  => __( 'Form Auto-Add', 'procaptcha-wordpress' ),
					'embed' => __( 'Form Embed', 'procaptcha-wordpress' ),
				],
			],
			'html_forms_status'                => [
				'label'   => 'HTML Forms',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'jetpack_status'                   => [
				'label'   => 'Jetpack',
				'type'    => 'checkbox',
				'options' => [
					'contact' => __( 'Contact Form', 'procaptcha-wordpress' ),
				],
			],
			'kadence_status'                   => [
				'label'   => 'Kadence',
				'type'    => 'checkbox',
				'options' => [
					'form'          => __( 'Kadence Form', 'procaptcha-wordpress' ),
					'advanced_form' => __( 'Kadence Advanced Form', 'procaptcha-wordpress' ),
				],
			],
			'learn_dash_status'                => [
				'label'   => 'LearnDash LMS',
				'type'    => 'checkbox',
				'options' => [
					'login'     => __( 'Login Form', 'procaptcha-wordpress' ),
					'lost_pass' => __( 'Lost Password Form', 'procaptcha-wordpress' ),
					'register'  => __( 'Register Form', 'procaptcha-wordpress' ),
				],
			],
			'mailchimp_status'                 => [
				'label'   => 'Mailchimp for WP',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'mailpoet_status'                  => [
				'label'   => 'MailPoet',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'memberpress_status'               => [
				'label'   => 'MemberPress',
				'type'    => 'checkbox',
				'options' => [
					'login'    => __( 'Login Form', 'procaptcha-wordpress' ),
					'register' => __( 'Register Form', 'procaptcha-wordpress' ),
				],
			],
			'ninja_status'                     => [
				'label'   => 'Ninja Forms',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'otter_status'                     => [
				'label'   => 'Otter Blocks',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'paid_memberships_pro_status'      => [
				'label'   => 'Paid Memberships Pro',
				'type'    => 'checkbox',
				'options' => [
					'checkout' => __( 'Checkout Form', 'procaptcha-wordpress' ),
					'login'    => __( 'Login Form', 'procaptcha-wordpress' ),
				],
			],
			'passster_status'                  => [
				'label'   => 'Passster',
				'type'    => 'checkbox',
				'options' => [
					'protect' => __( 'Protection Form', 'procaptcha-wordpress' ),
				],
			],
			'profile_builder_status'           => [
				'label'   => 'Profile Builder',
				'type'    => 'checkbox',
				'options' => [
					'login'     => __( 'Login Form', 'procaptcha-wordpress' ),
					'lost_pass' => __( 'Recover Password Form', 'procaptcha-wordpress' ),
					'register'  => __( 'Register Form', 'procaptcha-wordpress' ),
				],
			],
			'quform_status'                    => [
				'label'   => 'Quform',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'sendinblue_status'                => [
				'label'   => 'Sendinblue',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'simple_basic_contact_form_status' => [
				'label'   => 'Simple Basic Contact Form',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'simple_download_monitor_status'   => [
				'label'   => 'Simple Download Monitor',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'subscriber_status'                => [
				'label'   => 'Subscriber',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'supportcandy_status'              => [
				'label'   => 'Support Candy',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'theme_my_login_status'            => [
				'label'   => 'Theme My Login',
				'type'    => 'checkbox',
				'options' => [
					'login'     => __( 'Login Form', 'procaptcha-wordpress' ),
					'lost_pass' => __( 'Lost Password Form', 'procaptcha-wordpress' ),
					'register'  => __( 'Register Form', 'procaptcha-wordpress' ),
				],
			],
			'ultimate_member_status'           => [
				'label'   => 'Ultimate Member',
				'type'    => 'checkbox',
				'options' => [
					'login'     => __( 'Login Form', 'procaptcha-wordpress' ),
					'lost_pass' => __( 'Lost Password Form', 'procaptcha-wordpress' ),
					'register'  => __( 'Register Form', 'procaptcha-wordpress' ),
				],
			],
			'users_wp_status'                  => [
				'label'   => 'Users WP',
				'type'    => 'checkbox',
				'options' => [
					'forgot'   => __( 'Forgot Password Form', 'procaptcha-wordpress' ),
					'login'    => __( 'Login Form', 'procaptcha-wordpress' ),
					'register' => __( 'Register Form', 'procaptcha-wordpress' ),
				],
			],
			'woocommerce_status'               => [
				'label'   => 'WooCommerce',
				'type'    => 'checkbox',
				'options' => [
					'checkout'       => __( 'Checkout Form', 'procaptcha-wordpress' ),
					'login'          => __( 'Login Form', 'procaptcha-wordpress' ),
					'lost_pass'      => __( 'Lost Password Form', 'procaptcha-wordpress' ),
					'order_tracking' => __( 'Order Tracking Form', 'procaptcha-wordpress' ),
					'register'       => __( 'Register Form', 'procaptcha-wordpress' ),
				],
			],
			'woocommerce_wishlists_status'     => [
				'label'   => 'WooCommerce Wishlists',
				'type'    => 'checkbox',
				'options' => [
					'create_list' => __( 'Create List Form', 'procaptcha-wordpress' ),
				],
			],
			'wordfence_status'                 => [
				'label'   => 'Wordfence',
				'type'    => 'checkbox',
				'options' => [
					'login' => __( 'Login Form', 'procaptcha-wordpress' ),
				],
			],
			'wpforms_status'                   => [
				'label'   => 'WPForms',
				'type'    => 'checkbox',
				'options' => [
					'lite' => __( 'Lite', 'procaptcha-wordpress' ),
					'pro'  => __( 'Pro', 'procaptcha-wordpress' ),
				],
			],
			'wpdiscuz_status'                  => [
				'label'   => 'WPDiscuz',
				'type'    => 'checkbox',
				'options' => [
					'comment_form'   => __( 'Comment Form', 'procaptcha-wordpress' ),
					'subscribe_form' => __( 'Subscribe Form', 'procaptcha-wordpress' ),
				],
			],
			'wpforo_status'                    => [
				'label'   => 'WPForo',
				'type'    => 'checkbox',
				'options' => [
					'new_topic' => __( 'New Topic Form', 'procaptcha-wordpress' ),
					'reply'     => __( 'Reply Form', 'procaptcha-wordpress' ),
				],
			],
			'wp_job_openings_status'           => [
				'label'   => 'WP Job Openings',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
		];
	}

	/**
	 * Get logo image.
	 *
	 * @param array $form_field Label.
	 *
	 * @return string
	 * @noinspection HtmlUnknownTarget
	 */
	private function logo( array $form_field ): string {
		$label     = $form_field['label'];
		$logo_file = sanitize_file_name( strtolower( $label ) . '-logo.png' );
		$entity    = $form_field['entity'] ?? 'plugin';

		return sprintf(
			'<div class="procaptcha-integrations-logo"><img src="%1$s" alt="%2$s Logo" data-entity="%3$s"></div>',
			esc_url( constant( 'PROCAPTCHA_URL' ) . "/assets/images/$logo_file" ),
			$label,
			$entity
		);
	}

	/**
	 * Setup settings fields.
	 */
	public function setup_fields() {
		if ( ! $this->is_options_screen() ) {
			return;
		}

		$this->form_fields = $this->sort_fields( $this->form_fields );

		foreach ( $this->form_fields as &$form_field ) {
			if ( isset( $form_field['label'] ) ) {
				$form_field['label'] = $this->logo( $form_field );
			}

			if ( $form_field['disabled'] ) {
				$form_field['section'] = self::SECTION_DISABLED;
			} else {
				$form_field['section'] = self::SECTION_ENABLED;
			}
		}

		unset( $form_field );

		parent::setup_fields();
	}

	/**
	 * Sort fields. First, by enabled status, then by label.
	 *
	 * @param array $fields Fields.
	 *
	 * @return array
	 */
	public function sort_fields( array $fields ): array {
		uasort(
			$fields,
			static function ( $a, $b ) {
				$a_disabled = $a['disabled'] ?? false;
				$b_disabled = $b['disabled'] ?? false;

				$a_label = strtolower( $a['label'] ?? '' );
				$b_label = strtolower( $b['label'] ?? '' );

				if ( $a_disabled === $b_disabled ) {
					return $a_label <=> $b_label;
				}

				if ( ! $a_disabled && $b_disabled ) {
					return -1;
				}

				return 1;
			}
		);

		return $fields;
	}

	/**
	 * Section callback.
	 *
	 * @param array $arguments Section arguments.
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	public function section_callback( array $arguments ) {
		if ( self::SECTION_DISABLED === $arguments['id'] ) {
			?>
			<hr class="procaptcha-disabled-section">
			<h3><?php esc_html_e( 'Inactive plugins and themes', 'procaptcha-wordpress' ); ?></h3>
			<?php

			return;
		}

		?>
		<h2>
			<?php echo esc_html( $this->page_title() ); ?>
		</h2>
		<div id="procaptcha-message"></div>
		<p>
			<?php esc_html_e( 'Manage integrations with popular plugins such as Contact Form 7, WPForms, Gravity Forms, and more.', 'procaptcha-wordpress' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'You can activate and deactivate a plugin by clicking on its logo.', 'procaptcha-wordpress' ); ?>
		</p>
		<p>
			<?php
			$shortcode_url   = 'https://wordpress.org/plugins/procaptcha/#does%20the%20%5Bprocaptcha%5D%20shortcode%20have%20arguments%3F';
			$integration_url = 'https://github.com/prosopo/procaptcha-wordpress//issues';

			echo wp_kses_post(
				sprintf(
				/* translators: 1: procaptcha shortcode doc link, 2: integration doc link. */
					__( 'Don\'t see your plugin here? Use the `[procaptcha]` %1$s or %2$s.', 'procaptcha-wordpress' ),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						$shortcode_url,
						__( 'shortcode', 'procaptcha-wordpress' )
					),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						$integration_url,
						__( 'request an integration', 'procaptcha-wordpress' )
					)
				)
			);
			?>
		</p>
		<h3><?php esc_html_e( 'Active plugins and themes', 'procaptcha-wordpress' ); ?></h3>
		<?php
	}

	/**
	 * Enqueue class scripts.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script(
			self::HANDLE,
			constant( 'PROCAPTCHA_URL' ) . "/assets/js/integrations$this->min_prefix.js",
			[ 'jquery' ],
			constant( 'PROCAPTCHA_VERSION' ),
			true
		);

		wp_localize_script(
			self::HANDLE,
			self::OBJECT,
			[
				'ajaxUrl'            => admin_url( 'admin-ajax.php' ),
				'action'             => self::ACTIVATE_ACTION,
				'nonce'              => wp_create_nonce( self::ACTIVATE_ACTION ),
				/* translators: 1: Plugin name. */
				'activateMsg'        => __( 'Activate %s plugin?', 'procaptcha-wordpress' ),
				/* translators: 1: Plugin name. */
				'deactivateMsg'      => __( 'Deactivate %s plugin?', 'procaptcha-wordpress' ),
				/* translators: 1: Theme name. */
				'activateThemeMsg'   => __( 'Activate %s theme?', 'procaptcha-wordpress' ),
				/* translators: 1: Theme name. */
				'deactivateThemeMsg' => __( 'Deactivate %s theme?', 'procaptcha-wordpress' ),
			]
		);

		wp_enqueue_style(
			self::HANDLE,
			constant( 'PROCAPTCHA_URL' ) . "/assets/css/integrations$this->min_prefix.css",
			[ static::PREFIX . '-' . SettingsBase::HANDLE ],
			constant( 'PROCAPTCHA_VERSION' )
		);
	}

	/**
	 * Ajax action to activate/deactivate plugin.
	 *
	 * @return void
	 */
	public function activate() {
		// Run a security check.
		if ( ! check_ajax_referer( self::ACTIVATE_ACTION, 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Your session has expired. Please reload the page.', 'procaptcha-wordpress' ) );
		}

		// Check for permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'procaptcha-wordpress' ) );
		}

		$activate    = filter_input( INPUT_POST, 'activate', FILTER_VALIDATE_BOOLEAN );
		$entity      = filter_input( INPUT_POST, 'entity', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$status      = filter_input( INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$status      = str_replace( '-', '_', $status );
		$entity_name = $this->form_fields[ $status ]['label'];
		$entities    = [];

		foreach ( procaptcha()->modules as $module ) {
			if ( $module[0][0] === $status ) {
				$entities[] = (array) $module[1];
			}
		}

		$entities = array_merge( [], ...$entities );

		header_remove( 'Location' );
		http_response_code( 200 );

		if ( 'plugin' === $entity ) {
			$this->process_plugins( $activate, $entities, $entity_name );
		} else {
			$this->process_themes( $activate, $entities, $entity_name );
		}
	}

	/**
	 * Activate/deactivate plugins.
	 *
	 * @param bool   $activate    Activate or deactivate.
	 * @param array  $plugins     Plugins to process.
	 * @param string $plugin_name Main plugin name to process.
	 *
	 * @return void
	 */
	private function process_plugins( bool $activate, array $plugins, string $plugin_name ) {
		if ( $activate ) {
			if ( ! $this->activate_plugins( $plugins ) ) {
				$message = sprintf(
				/* translators: 1: Plugin name. */
					__( 'Error activating %s plugin.', 'procaptcha-wordpress' ),
					$plugin_name
				);

				wp_send_json_error( esc_html( $message ) );
			}

			$message = sprintf(
			/* translators: 1: Plugin name. */
				__( '%s plugin is activated.', 'procaptcha-wordpress' ),
				$plugin_name
			);

			wp_send_json_success( esc_html( $message ) );
		}

		deactivate_plugins( $plugins );

		$message = sprintf(
		/* translators: 1: Plugin name. */
			__( '%s plugin is deactivated.', 'procaptcha-wordpress' ),
			$plugin_name
		);

		wp_send_json_success( esc_html( $message ) );
	}

	/**
	 * Activate/deactivate themes.
	 *
	 * @param bool   $activate   Activate or deactivate.
	 * @param array  $themes     Themes to process.
	 * @param string $theme_name Main theme name to process.
	 *
	 * @return void
	 */
	private function process_themes( bool $activate, array $themes, string $theme_name ) {
		if ( $activate ) {
			if ( ! $this->activate_themes( $themes ) ) {
				$message = sprintf(
				/* translators: 1: Theme name. */
					__( 'Error activating %s theme.', 'procaptcha-wordpress' ),
					$theme_name
				);

				wp_send_json_error( esc_html( $message ) );
			}

			$message = sprintf(
			/* translators: 1: Theme name. */
				__( '%s theme is activated.', 'procaptcha-wordpress' ),
				$theme_name
			);

			wp_send_json_success( esc_html( $message ) );
		}

		$new_theme = WP_Theme::get_core_default_theme();

		if ( ! $new_theme ) {
			wp_send_json_error( esc_html__( 'No available theme to activate found.', 'procaptcha-wordpress' ) );
		}

		ob_start();

		switch_theme( $new_theme->get_stylesheet() );

		ob_end_clean();

		$message = sprintf(
		/* translators: 1: Deactivated theme name. 2: Activated theme name. */
			__( '%1$s theme is deactivated. %2$s theme is activated.', 'procaptcha-wordpress' ),
			$theme_name,
			$new_theme->get( 'Name' )
		);

		wp_send_json_success( esc_html( $message ) );
	}

	/**
	 * Activate plugins.
	 *
	 * We activate the first available plugin in the list only,
	 * assuming that Pro plugins are placed earlier in the list.
	 *
	 * @param array $plugins Plugins to activate.
	 *
	 * @return bool
	 */
	private function activate_plugins( array $plugins ): bool {
		foreach ( $plugins as $plugin ) {
			ob_start();

			$result = activate_plugin( $plugin );

			ob_end_clean();

			if ( null === $result ) {
				// Activate the first available plugin only.
				return true;
			}
		}

		return false;
	}

	/**
	 * Activate themes.
	 *
	 * We activate the first available theme in the list only,
	 * assuming that parent theme is placed earlier in the list.
	 *
	 * @param array $themes Themes to activate.
	 *
	 * @return bool
	 */
	private function activate_themes( array $themes ): bool {
		ob_start();

		switch_theme( $themes[0] );

		ob_end_clean();

		// Activate the first available theme only.
		return true;
	}
}
