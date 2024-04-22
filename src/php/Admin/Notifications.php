<?php
/**
 * Notifications class file.
 *
 * @package hcaptcha-wp
 */

namespace HCaptcha\Admin;

/**
 * Class Notifications.
 *
 * Show notifications in the admin.
 */
class Notifications {

	/**
	 * Admin script handle.
	 */
	const HANDLE = 'hcaptcha-notifications';

	/**
	 * Script localization object.
	 */
	const OBJECT = 'HCaptchaNotificationsObject';

	/**
	 * Dismiss notification ajax action.
	 */
	const DISMISS_NOTIFICATION_ACTION = 'hcaptcha-dismiss-notification';

	/**
	 * Reset notifications ajax action.
	 */
	const RESET_NOTIFICATIONS_ACTION = 'hcaptcha-reset-notifications';

	/**
	 * Dismissed user meta.
	 */
	const HCAPTCHA_DISMISSED_META_KEY = 'hcaptcha_dismissed';

	/**
	 * Notifications.
	 *
	 * @var array
	 */
	protected $notifications = [];

	/**
	 * Shuffle notifications.
	 *
	 * @var bool
	 */
	protected $shuffle = true;

	/**
	 * Init class.
	 */
	public function init() {
		$this->init_notifications();
		$this->init_hooks();
	}

	/**
	 * Init class hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'wp_ajax_' . self::DISMISS_NOTIFICATION_ACTION, [ $this, 'dismiss_notification' ] );
		add_action( 'wp_ajax_' . self::RESET_NOTIFICATIONS_ACTION, [ $this, 'reset_notifications' ] );
	}

	/**
	 * Init notifications.
	 *
	 * @return void
	 * @noinspection HtmlUnknownTarget
	 */
	private function init_notifications() {
		$hcaptcha_url            = 'https://www.prosopo.io/?r=wp&utm_source=wordpress&utm_medium=wpplugin&utm_campaign=sk';
		$register_url            = 'https://www.prosopo.io/signup-interstitial/?r=wp&utm_source=wordpress&utm_medium=wpplugin&utm_campaign=sk';
		$pro_url                 = 'https://www.prosopo.io/pro?r=wp&utm_source=wordpress&utm_medium=wpplugin&utm_campaign=not';
		$dashboard_url           = 'https://dashboard.prosopo.io/?r=wp&utm_source=wordpress&utm_medium=wpplugin&utm_campaign=not';
		$post_leadership_url     = 'https://www.prosopo.io/post/hcaptcha-named-a-technology-leader-in-bot-management/?r=wp&utm_source=wordpress&utm_medium=wpplugin&utm_campaign=not';
		$rate_url                = 'https://wordpress.org/support/plugin/procaptcha-wordpress/reviews/?filter=5#new-post';
		$search_integrations_url = admin_url( 'options-general.php?page=hcaptcha&tab=integrations#hcaptcha-integrations-search' );
		$enterprise_features_url = 'https://www.prosopo.io/#enterprise-features?r=wp&utm_source=wordpress&utm_medium=wpplugin&utm_campaign=not';
		$statistics_url          = admin_url( 'options-general.php?page=hcaptcha&tab=general#statistics_1' );
		$forms_url               = admin_url( 'options-general.php?page=hcaptcha&tab=forms' );
		$events_url              = admin_url( 'options-general.php?page=hcaptcha&tab=events' );
		$force_url               = admin_url( 'options-general.php?page=hcaptcha&tab=general#force_1' );

		$this->notifications = [
			'register'            => [
				'title'   => __( 'Get your hCaptcha site keys', 'procaptcha-wordpress' ),
				'message' => sprintf(
				/* translators: 1: hCaptcha link, 2: register link. */
					__( 'To use %1$s, please register %2$s to get your site and secret keys.', 'procaptcha-wordpress' ),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						$hcaptcha_url,
						__( 'hCaptcha', 'procaptcha-wordpress' )
					),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						$register_url,
						__( 'here', 'procaptcha-wordpress' )
					)
				),
				'button'  => [
					'url'  => $register_url,
					'text' => __( 'Get site keys', 'procaptcha-wordpress' ),
				],
			],
			'pro-free-trial'      => [
				'title'   => __( 'Try Pro for free', 'procaptcha-wordpress' ),
				'message' => sprintf(
				/* translators: 1: hCaptcha Pro link, 2: dashboard link. */
					__( 'Want low friction and custom themes? %1$s is for you. %2$s, no credit card required.', 'procaptcha-wordpress' ),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						$pro_url,
						__( 'hCaptcha Pro', 'procaptcha-wordpress' )
					),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						$dashboard_url,
						__( 'Start a free trial in your dashboard', 'procaptcha-wordpress' )
					)
				),
				'button'  => [
					'url'  => $pro_url,
					'text' => __( 'Try Pro', 'procaptcha-wordpress' ),
				],
			],
			'post-leadership'     => [
				'title'   => __( 'hCaptcha\'s Leadership', 'procaptcha-wordpress' ),
				'message' => __( 'hCaptcha Named a Technology Leader in Bot Management: 2023 SPARK Matrix™', 'procaptcha-wordpress' ),
				'button'  => [
					'url'  => $post_leadership_url,
					'text' => __( 'Read post', 'procaptcha-wordpress' ),
				],
			],
			'please-rate'         => [
				'title'   => __( 'Rate hCaptcha plugin', 'procaptcha-wordpress' ),
				'message' => sprintf(
				/* translators: 1: plugin name, 2: wp.org review link with stars, 3: wp.org review link with text. */
					__( 'Please rate %1$s %2$s on %3$s. Thank you!', 'procaptcha-wordpress' ),
					'<strong>Procaptcha for WordPress</strong>',
					sprintf(
						'<a href="%1$s" target="_blank" rel="noopener noreferrer">★★★★★</a>',
						$rate_url
					),
					sprintf(
						'<a href="%1$s" target="_blank" rel="noopener noreferrer">WordPress.org</a>',
						$rate_url
					)
				),
				'button'  => [
					'url'  => $rate_url,
					'text' => __( 'Rate', 'procaptcha-wordpress' ),
				],
			],
			// Added in 3.8.0.
			'search-integrations' => [
				'title'   => __( 'Search on Integrations page', 'procaptcha-wordpress' ),
				'message' => __( 'Now you can search for plugin an themes on the Integrations page.', 'procaptcha-wordpress' ),
				'button'  => [
					'url'  => $search_integrations_url,
					'text' => __( 'Start search', 'procaptcha-wordpress' ),
				],
			],
			// Added in 3.9.0.
			'enterprise-support'  => [
				'title'   => __( 'Support for Enterprise features', 'procaptcha-wordpress' ),
				'message' => __( 'The hCaptcha plugin commenced support for Enterprise features. Solve your fraud and abuse problem today.', 'procaptcha-wordpress' ),
				'button'  => [
					'url'  => $enterprise_features_url,
					'text' => __( 'Get started', 'procaptcha-wordpress' ),
				],
			],
			// Added in 4.0.0.
			'statistics'          => [
				'title'   => __( 'Events statistics and Forms admin page', 'procaptcha-wordpress' ),
				'message' => sprintf(
				/* translators: 1: statistics switch link, 2: the 'forms' page link. */
					__( '%1$s events statistics and %2$s how your forms are used.', 'procaptcha-wordpress' ),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						$statistics_url,
						__( 'Turn on', 'procaptcha-wordpress' )
					),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						$forms_url,
						__( 'see', 'procaptcha-wordpress' )
					)
				),
				'button'  => [
					'url'  => $statistics_url,
					'text' => __( 'Turn on stats', 'procaptcha-wordpress' ),
				],
			],
			// Added in 4.0.0.
			'events_page'         => [
				'title'   => __( 'Events admin page', 'procaptcha-wordpress' ),
				'message' => sprintf(
				/* translators: 1: statistics switch link, 2: the 'forms' page link. */
					__( '%1$s events statistics and %2$s to %3$s complete statistics on form events.', 'procaptcha-wordpress' ),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						$statistics_url,
						__( 'Turn on', 'procaptcha-wordpress' )
					),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						$dashboard_url,
						__( 'upgrade to Pro', 'procaptcha-wordpress' )
					),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						$events_url,
						__( 'see', 'procaptcha-wordpress' )
					)
				),
				'button'  => [
					'url'  => $statistics_url,
					'text' => __( 'Turn on stats', 'procaptcha-wordpress' ),
				],
			],
			// Added in 4.0.0.
			'force'               => [
				'title'   => __( 'Force hCaptcha', 'procaptcha-wordpress' ),
				'message' => __( 'Force hCaptcha check before submitting the form and simplify the user experience.', 'procaptcha-wordpress' ),
				'button'  => [
					'url'  => $force_url,
					'text' => __( 'Turn on force', 'procaptcha-wordpress' ),
				],
			],
		];

		$settings = hcaptcha()->settings();

		if ( ! empty( $settings->get_site_key() ) && ! empty( $settings->get_secret_key() ) ) {
			unset( $this->notifications['register'] );
		}

		if ( hcaptcha()->is_pro() ) {
			unset( $this->notifications['pro-free-trial'] );
		}

		if ( $settings->is_on( 'force' ) ) {
			unset( $this->notifications['force'] );
		}
	}

	/**
	 * Show notifications.
	 *
	 * @return void
	 */
	public function show() {
		$user = wp_get_current_user();

		// phpcs:ignore Generic.Commenting.DocComment.MissingShort
		/** @noinspection NullPointerExceptionInspection */
		$dismissed     = get_user_meta( $user->ID, self::HCAPTCHA_DISMISSED_META_KEY, true ) ?: [];
		$notifications = array_diff_key( $this->notifications, array_flip( $dismissed ) );

		if ( ! $notifications ) {
			return;
		}

		?>
		<div id="hcaptcha-notifications">
			<div id="hcaptcha-notifications-header">
				<?php esc_html_e( 'Notifications', 'procaptcha-wordpress' ); ?>
			</div>
			<?php

			if ( $this->shuffle ) {
				$notifications = $this->shuffle_assoc( $notifications );
			}

			foreach ( $notifications as $id => $notification ) {
				$title       = $notification['title'] ?: '';
				$message     = $notification['message'] ?? '';
				$button_url  = $notification['button']['url'] ?? '';
				$button_text = $notification['button']['text'] ?? '';
				$button      = '';

				if ( $button_url && $button_text ) {
					ob_start();
					?>
					<div class="hcaptcha-notification-buttons hidden">
						<a href="<?php echo esc_url( $button_url ); ?>" class="button button-primary" target="_blank">
							<?php echo esc_html( $button_text ); ?>
						</a>
					</div>
					<?php
					$button = ob_get_clean();
				}

				// We need 'inline' class below to prevent moving the 'notice' div after h2 by common.js script in WP Core.
				?>
				<div
						class="hcaptcha-notification notice notice-info is-dismissible inline"
						data-id="<?php echo esc_attr( $id ); ?>">
					<div class="hcaptcha-notification-title">
						<?php echo esc_html( $title ); ?>
					</div>
					<p><?php echo wp_kses_post( $message ); ?></p>
					<?php echo wp_kses_post( $button ); ?>
				</div>
				<?php
			}

			$next_disabled = count( $notifications ) === 1 ? 'disabled' : '';

			?>
			<div id="hcaptcha-notifications-footer">
				<div id="hcaptcha-navigation">
					<a class="prev disabled"></a>
					<a class="next <?php echo esc_attr( $next_disabled ); ?>"></a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue class scripts.
	 */
	public function admin_enqueue_scripts() {
		$min = hcap_min_suffix();

		wp_enqueue_script(
			self::HANDLE,
			constant( 'HCAPTCHA_URL' ) . "/assets/js/notifications$min.js",
			[ 'jquery' ],
			constant( 'HCAPTCHA_VERSION' ),
			true
		);

		wp_localize_script(
			self::HANDLE,
			self::OBJECT,
			[
				'ajaxUrl'                   => admin_url( 'admin-ajax.php' ),
				'dismissNotificationAction' => self::DISMISS_NOTIFICATION_ACTION,
				'dismissNotificationNonce'  => wp_create_nonce( self::DISMISS_NOTIFICATION_ACTION ),
				'resetNotificationAction'   => self::RESET_NOTIFICATIONS_ACTION,
				'resetNotificationNonce'    => wp_create_nonce( self::RESET_NOTIFICATIONS_ACTION ),
			]
		);

		wp_enqueue_style(
			self::HANDLE,
			constant( 'HCAPTCHA_URL' ) . "/assets/css/notifications$min.css",
			[],
			constant( 'HCAPTCHA_VERSION' )
		);
	}

	/**
	 * Ajax action to dismiss notification.
	 *
	 * @return void
	 */
	public function dismiss_notification() {
		// Run a security check.
		if ( ! check_ajax_referer( self::DISMISS_NOTIFICATION_ACTION, 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Your session has expired. Please reload the page.', 'procaptcha-wordpress' ) );
		}

		// Check for permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'procaptcha-wordpress' ) );
		}

		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';

		if ( ! $this->update_dismissed( $id ) ) {
			wp_send_json_error( esc_html__( 'Error dismissing notification.', 'procaptcha-wordpress' ) );
		}

		wp_send_json_success();
	}

	/**
	 * Update dismissed notifications.
	 *
	 * @param string $id Notification id.
	 *
	 * @return bool
	 */
	private function update_dismissed( string $id ): bool {
		if ( ! $id ) {
			return false;
		}

		$user    = wp_get_current_user();
		$user_id = $user->ID ?? 0;

		$dismissed = get_user_meta( $user_id, self::HCAPTCHA_DISMISSED_META_KEY, true ) ?: [];

		if ( in_array( $id, $dismissed, true ) ) {
			return false;
		}

		$dismissed[] = $id;

		return (bool) update_user_meta( $user_id, self::HCAPTCHA_DISMISSED_META_KEY, $dismissed );
	}

	/**
	 * Ajax action to reset notifications.
	 *
	 * @return void
	 */
	public function reset_notifications() {
		// Run a security check.
		if ( ! check_ajax_referer( self::RESET_NOTIFICATIONS_ACTION, 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Your session has expired. Please reload the page.', 'procaptcha-wordpress' ) );
		}

		// Check for permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'procaptcha-wordpress' ) );
		}

		if ( ! $this->remove_dismissed() ) {
			wp_send_json_error( esc_html__( 'Error removing dismissed notifications.', 'procaptcha-wordpress' ) );
		}

		ob_start();
		$this->show();

		wp_send_json_success( wp_kses_post( ob_get_clean() ) );
	}

	/**
	 * Remove dismissed status for all notifications.
	 *
	 * @return bool
	 */
	private function remove_dismissed(): bool {
		$user    = wp_get_current_user();
		$user_id = $user->ID ?? 0;

		return delete_user_meta( $user_id, self::HCAPTCHA_DISMISSED_META_KEY );
	}

	/**
	 * Shuffle array retaining its keys.
	 *
	 * @param array $arr Array.
	 *
	 * @return array
	 * @noinspection NonSecureShuffleUsageInspection
	 */
	private function shuffle_assoc( array $arr ): array {
		$new_arr = [];
		$keys    = array_keys( $arr );

		shuffle( $keys );

		foreach ( $keys as $key ) {
			$new_arr[ $key ] = $arr[ $key ];
		}

		return $new_arr;
	}
}
