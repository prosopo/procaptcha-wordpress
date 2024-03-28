<?php
/**
 * Notifications class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\Admin;

/**
 * Class Notifications.
 *
 * Show notifications in the admin.
 */
class Notifications {

	/**
	 * Admin script handle.
	 */
	const HANDLE = 'procaptcha-notifications';

	/**
	 * Script localization object.
	 */
	const OBJECT = 'PROCAPTCHANotificationsObject';

	/**
	 * Dismiss notification ajax action.
	 */
	const DISMISS_NOTIFICATION_ACTION = 'procaptcha-dismiss-notification';

	/**
	 * Reset notifications ajax action.
	 */
	const RESET_NOTIFICATIONS_ACTION = 'procaptcha-reset-notifications';

	/**
	 * Dismissed user meta.
	 */
	const PROCAPTCHA_DISMISSED_META_KEY = 'procaptcha_dismissed';

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
		$procaptcha_url      = 'https://www.prosopo.io/';
		$register_url        = 'https://www.prosopo.io/register';
		$premium_url         = 'https://prosopo.io/#pricing-one';
		$dashboard_url       = 'https://www.prosopo.io/';

		$this->notifications = [
			'register'        => [
				'title'   => __( 'Get your Prosopo site keys', 'prosopoProcaptcha' ),
				'message' => sprintf(
				/* translators: 1: procaptcha link, 2: register link. */
					__( 'To use %1$s, please register %2$s to get your site keys.', 'prosopoProcaptcha' ),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						$procaptcha_url,
						__( 'procaptcha', 'prosopoProcaptcha' )
					),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						$register_url,
						__( 'here', 'prosopoProcaptcha' )
					)
				),
				'button'  => [
					'url'  => $register_url,
					'text' => __( 'Get site keys', 'prosopoProcaptcha' ),
				],
			],
			'pro-free-trial'  => [
				'title'   => __( 'Try Pro for free', 'prosopoProcaptcha' ),
				'message' => sprintf(
				/* translators: 1: Procaptcha Premium link, 2: dashboard link. */
					__( 'Want low friction and custom themes? %1$s is for you. %2$s, no credit card required.', 'prosopoProcaptcha' ),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						$premium_url,
						__( 'Procaptcha Premium', 'prosopoProcaptcha' )
					),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						$dashboard_url,
						__( 'Start a free trial in your dashboard', 'prosopoProcaptcha' )
					)
				),
				'button'  => [
					'url'  => $premium_url,
					'text' => __( 'Try Pro', 'prosopoProcaptcha' ),
				],
			]
		];

		$settings = procaptcha()->settings();

		if ( ! empty( $settings->get_site_key()  ) ) {
			unset( $this->notifications['register'] );
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
		$dismissed     = get_user_meta( $user->ID, self::PROCAPTCHA_DISMISSED_META_KEY, true ) ?: [];
		$notifications = array_diff_key( $this->notifications, array_flip( $dismissed ) );

		if ( ! $notifications ) {
			return;
		}

		?>
		<div id="procaptcha-notifications">
			<div id="procaptcha-notifications-header">
				<?php esc_html_e( 'Notifications', 'prosopoProcaptcha' ); ?>
			</div>
			<?php

			if ( $this->shuffle ) {
				$notifications = $this->shuffle_assoc( $notifications );
			}

			foreach ( $notifications as $id => $notification ) {
				if ( array_key_exists( $id, $dismissed ) ) {
					continue;
				}

				$title       = $notification['title'] ?: '';
				$message     = $notification['message'] ?? '';
				$button_url  = $notification['button']['url'] ?? '';
				$button_text = $notification['button']['text'] ?? '';
				$button      = '';

				if ( $button_url && $button_text ) {
					ob_start();
					?>
					<div class="procaptcha-notification-buttons hidden">
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
						class="procaptcha-notification notice notice-info is-dismissible inline"
						data-id="<?php echo esc_attr( $id ); ?>">
					<div class="procaptcha-notification-title">
						<?php echo esc_html( $title ); ?>
					</div>
					<p><?php echo wp_kses_post( $message ); ?></p>
					<?php echo wp_kses_post( $button ); ?>
				</div>
				<?php
			}

			$next_disabled = count( $notifications ) === 1 ? 'disabled' : '';

			?>
			<div id="procaptcha-notifications-footer">
				<div id="procaptcha-navigation">
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
		$min = procap_min_suffix();

		wp_enqueue_script(
			self::HANDLE,
			constant( 'PROCAPTCHA_URL' ) . "/assets/js/notifications$min.js",
			[ 'jquery' ],
			constant( 'PROCAPTCHA_VERSION' ),
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
			constant( 'PROCAPTCHA_URL' ) . "/assets/css/notifications$min.css",
			[],
			constant( 'PROCAPTCHA_VERSION' )
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
			wp_send_json_error( esc_html__( 'Your session has expired. Please reload the page.', 'prosopoProcaptcha' ) );
		}

		// Check for permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'prosopoProcaptcha' ) );
		}

		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';

		if ( ! $this->update_dismissed( $id ) ) {
			wp_send_json_error( esc_html__( 'Error dismissing notification.', 'prosopoProcaptcha' ) );
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

		$user = wp_get_current_user();

		if ( ! $user ) {
			return false;
		}

		$dismissed = get_user_meta( $user->ID, self::PROCAPTCHA_DISMISSED_META_KEY, true ) ?: [];

		if ( in_array( $id, $dismissed, true ) ) {
			return false;
		}

		$dismissed[] = $id;

		$result = update_user_meta( $user->ID, self::PROCAPTCHA_DISMISSED_META_KEY, $dismissed );

		if ( ! $result ) {
			return false;
		}

		return true;
	}

	/**
	 * Ajax action to reset notifications.
	 *
	 * @return void
	 */
	public function reset_notifications() {
		// Run a security check.
		if ( ! check_ajax_referer( self::RESET_NOTIFICATIONS_ACTION, 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Your session has expired. Please reload the page.', 'prosopoProcaptcha' ) );
		}

		// Check for permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'prosopoProcaptcha' ) );
		}

		if ( ! $this->remove_dismissed() ) {
			wp_send_json_error( esc_html__( 'Error removing dismissed notifications.', 'prosopoProcaptcha' ) );
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
		$user = wp_get_current_user();

		if ( ! $user ) {
			return false;
		}

		return delete_user_meta( $user->ID, self::PROCAPTCHA_DISMISSED_META_KEY );
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
