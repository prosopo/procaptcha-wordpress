<?php
/**
 * Integrations class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\Settings;

use Procaptcha\Admin\Dialog;
use KAGG\Settings\Abstracts\SettingsBase;
use WP_Theme;

/**
 * Class Integrations
 *
 * Settings page "Integrations".
 */
class Integrations extends PluginSettingsBase {

	/**
	 * Dialog scripts and style handle.
	 */
	const DIALOG_HANDLE = 'kagg-dialog';

	/**
	 * Admin script and style handle.
	 */
	const HANDLE = 'procaptcha-integrations';

	/**
	 * Script localization object.
	 */
	const OBJECT = 'ProcaptchaIntegrationsObject';

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
	 * Dialog class instance.
	 *
	 * @var Dialog
	 */
	protected $dialog;

	/**
	 * Entity name to activate/deactivate. Can be 'plugin' or 'theme'.
	 *
	 * @var string
	 */
	private $entity = '';

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

		add_action( 'kagg_settings_tab', [ $this, 'search_box' ] );
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
			'affiliates_status'                => [
				'label'   => 'Affiliates',
				'type'    => 'checkbox',
				'options' => [
					'login'    => __( 'Affiliates Login Form', 'procaptcha-wordpress' ),
					'register' => __( 'Affiliates Register Form', 'procaptcha-wordpress' ),
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
				'logo'    => 'svg',
				'type'    => 'checkbox',
				'options' => [
					'contact' => __( 'Contact Form', 'procaptcha-wordpress' ),
					'login'   => __( 'Login Form', 'procaptcha-wordpress' ),
				],
			],
			'brizy_status'                     => [
				'label'   => 'Brizy',
				'logo'    => 'svg',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'bp_status'                        => [
				'label'   => 'BuddyPress',
				'logo'    => 'svg',
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
			'coblocks_status'                  => [
				'label'   => 'CoBlocks',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
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
				'logo'    => 'svg',
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
				'logo'    => 'svg',
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
				'logo'    => 'svg',
				'type'    => 'checkbox',
				'options' => [
					'form'  => __( 'Form', 'procaptcha-wordpress' ),
					'login' => __( 'Login', 'procaptcha-wordpress' ),
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
				'logo'    => 'svg',
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
				'logo'    => 'svg',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'gravity_status'                   => [
				'label'   => 'Gravity Forms',
				'logo'    => 'svg',
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
				'logo'    => 'svg',
				'type'    => 'checkbox',
				'options' => [
					'contact' => __( 'Contact Form', 'procaptcha-wordpress' ),
				],
			],
			'kadence_status'                   => [
				'label'   => 'Kadence',
				'logo'    => 'svg',
				'type'    => 'checkbox',
				'options' => [
					'form'          => __( 'Kadence Form', 'procaptcha-wordpress' ),
					'advanced_form' => __( 'Kadence Advanced Form', 'procaptcha-wordpress' ),
				],
			],
			'learn_dash_status'                => [
				'label'   => 'LearnDash LMS',
				'logo'    => 'svg',
				'type'    => 'checkbox',
				'options' => [
					'login'     => __( 'Login Form', 'procaptcha-wordpress' ),
					'lost_pass' => __( 'Lost Password Form', 'procaptcha-wordpress' ),
					'register'  => __( 'Register Form', 'procaptcha-wordpress' ),
				],
			],
			'login_signup_popup_status'        => [
				'label'   => 'Login Signup Popup',
				'type'    => 'checkbox',
				'options' => [
					'login'    => __( 'Login Form', 'procaptcha-wordpress' ),
					'register' => __( 'Register Form', 'procaptcha-wordpress' ),
				],
			],
			'mailchimp_status'                 => [
				'label'   => 'Mailchimp for WP',
				'logo'    => 'svg',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'mailpoet_status'                  => [
				'label'   => 'MailPoet',
				'logo'    => 'svg',
				'type'    => 'checkbox',
				'options' => [
					'form' => __( 'Form', 'procaptcha-wordpress' ),
				],
			],
			'memberpress_status'               => [
				'label'   => 'MemberPress',
				'logo'    => 'svg',
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
				'logo'    => 'svg',
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
				'label'   => 'Brevo',
				'logo'    => 'svg',
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
			'spectra_status'                   => [
				'label'   => 'Spectra',
				'logo'    => 'svg',
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
				'logo'    => 'svg',
				'type'    => 'checkbox',
				'options' => [
					'login' => __( 'Login Form', 'procaptcha-wordpress' ),
				],
			],
			'wpforms_status'                   => [
				'label'   => 'WPForms',
				'type'    => 'checkbox',
				'options' => [
					'form'  => __( 'Form Auto-Add', 'procaptcha-wordpress' ),
					'embed' => __( 'Form Embed', 'procaptcha-wordpress' ),
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
		$logo_type = $form_field['logo'] ?? 'png';
		$logo_file = sanitize_file_name( strtolower( $label ) . '.' . $logo_type );
		$entity    = $form_field['entity'] ?? 'plugin';

		return sprintf(
			'<div class="procaptcha-integrations-logo">' .
			'<img src="%1$s" alt="%2$s Logo" data-label="%2$s" data-entity="%3$s">' .
			'</div>',
			esc_url( constant( 'HCAPTCHA_URL' ) . "/assets/images/logo/$logo_file" ),
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
	 * Show search box.
	 */
	public function search_box() {
		?>
		<span id="procaptcha-integrations-search-wrap">
			<label for="procaptcha-integrations-search"></label>
			<input
					type="search" id="procaptcha-integrations-search"
					placeholder="<?php esc_html_e( 'Search plugins and themes...', 'procaptcha-wordpress' ); ?>">
		</span>
		<?php
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
			$this->submit_button();

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
			$shortcode_url   = 'https://wordpress.org/plugins/procaptcha-wordpress/#does%20the%20%5Bprocaptcha%5D%20shortcode%20have%20arguments%3F';
			$integration_url = 'https://github.com/procap_/procaptcha-wordpress-plugin/issues';

			echo wp_kses_post(
				sprintf(
				/* translators: 1: procap_ shortcode doc link, 2: integration doc link. */
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
			constant( 'HCAPTCHA_URL' ) . "/assets/js/integrations$this->min_prefix.js",
			[ 'jquery', self::DIALOG_HANDLE ],
			constant( 'HCAPTCHA_VERSION' ),
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
				'selectThemeMsg'     => __( 'Select theme to activate:', 'procaptcha-wordpress' ),
				'onlyOneThemeMsg'    => __( 'Cannot deactivate the only theme on the site.', 'procaptcha-wordpress' ),
				'unexpectedErrorMsg' => __( 'Unexpected error.', 'procaptcha-wordpress' ),
				'OKBtnText'          => __( 'OK', 'procaptcha-wordpress' ),
				'CancelBtnText'      => __( 'Cancel', 'procaptcha-wordpress' ),
				'themes'             => $this->get_themes(),
				'defaultTheme'       => $this->get_default_theme(),
			]
		);

		wp_enqueue_style(
			self::HANDLE,
			constant( 'HCAPTCHA_URL' ) . "/assets/css/integrations$this->min_prefix.css",
			[ static::PREFIX . '-' . SettingsBase::HANDLE, self::DIALOG_HANDLE ],
			constant( 'HCAPTCHA_VERSION' )
		);
	}

	/**
	 * Ajax action to activate/deactivate plugin/theme.
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

		$activate     = filter_input( INPUT_POST, 'activate', FILTER_VALIDATE_BOOLEAN );
		$this->entity = filter_input( INPUT_POST, 'entity', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$new_theme    = filter_input( INPUT_POST, 'newTheme', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$status       = filter_input( INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$status       = str_replace( '-', '_', $status );
		$entity_name  = $this->form_fields[ $status ]['label'];

		header_remove( 'Location' );
		http_response_code( 200 );

		if ( 'plugin' === $this->entity ) {
			$entities = [];

			foreach ( procaptcha()->modules as $module ) {
				if ( $module[0][0] === $status ) {
					$entities[] = (array) $module[1];
				}
			}

			$entities = array_unique( array_merge( [], ...$entities ) );

			$this->process_plugins( $activate, $entities, $entity_name );
		} else {
			$theme = $activate ? $entity_name : $new_theme;

			$this->process_theme( $theme );
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

				$this->send_json_error( esc_html( $message ) );
			}

			$message = sprintf(
			/* translators: 1: Plugin name. */
				__( '%s plugin is activated.', 'procaptcha-wordpress' ),
				$plugin_name
			);

			$this->send_json_success( esc_html( $message ) );
		}

		deactivate_plugins( $plugins );

		$message = sprintf(
		/* translators: 1: Plugin name. */
			__( '%s plugin is deactivated.', 'procaptcha-wordpress' ),
			$plugin_name
		);

		$this->send_json_success( esc_html( $message ) );
	}

	/**
	 * Activate a theme.
	 *
	 * @param string $theme Theme name to process.
	 *
	 * @return void
	 */
	private function process_theme( string $theme ) {
		if ( ! $this->activate_theme( $theme ) ) {
			$message = sprintf(
			/* translators: 1: Theme name. */
				__( 'Error activating %s theme.', 'procaptcha-wordpress' ),
				$theme
			);

			$this->send_json_error( esc_html( $message ) );
		}

		$message = sprintf(
		/* translators: 1: Theme name. */
			__( '%s theme is activated.', 'procaptcha-wordpress' ),
			$theme
		);

		$this->send_json_success( esc_html( $message ) );
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
	 * Activate theme.
	 *
	 * @param string $theme Theme to activate.
	 *
	 * @return bool
	 */
	private function activate_theme( string $theme ): bool {
		if ( ! wp_get_theme( $theme )->exists() ) {
			return false;
		}

		ob_start();

		switch_theme( $theme );

		ob_end_clean();

		return true;
	}

	/**
	 * Send json success.
	 *
	 * @param string $message Message.
	 *
	 * @return void
	 */
	private function send_json_success( string $message ) {
		wp_send_json_success( $this->json_data( $message ) );
	}

	/**
	 * Send json error.
	 *
	 * @param string $message Message.
	 *
	 * @return void
	 */
	private function send_json_error( string $message ) {
		wp_send_json_error( $this->json_data( $message ) );
	}

	/**
	 * Prepare json data.
	 *
	 * @param string $message Message.
	 *
	 * @return array
	 */
	private function json_data( string $message ): array {
		$data = [ 'message' => esc_html( $message ) ];

		if ( 'theme' === $this->entity ) {
			$data['themes']       = $this->get_themes();
			$data['defaultTheme'] = $this->get_default_theme();
		}

		return $data;
	}

	/**
	 * Get themes to switch (all themes, excluding the active one).
	 *
	 * @return array
	 */
	public function get_themes(): array {
		$themes = array_map(
			static function ( $theme ) {
				return $theme->get( 'Name' );
			},
			wp_get_themes()
		);

		unset( $themes[ wp_get_theme()->get_stylesheet() ] );

		asort( $themes );

		return $themes;
	}

	/**
	 * Get default theme.
	 *
	 * @return string
	 */
	public function get_default_theme(): string {
		$core_default_theme_obj = WP_Theme::get_core_default_theme();

		return $core_default_theme_obj ? $core_default_theme_obj->get_stylesheet() : '';
	}
}
