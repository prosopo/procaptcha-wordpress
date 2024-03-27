<?php
/**
 * Procaptcha class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\Helpers;

use WP_Error;

/**
 * Class Procaptcha.
 */
class Procaptcha {
	const PROCAPTCHA_WIDGET_ID = 'procaptcha-widget-id';

	/**
	 * Default widget id.
	 *
	 * @var array
	 */
	private static $default_id = [
		'source'  => [],
		'form_id' => 0,
	];

	/**
	 * Get Procaptcha form.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string
	 */
	public static function form( array $args = [] ): string {
		ob_start();
		self::form_display( $args );

		return (string) ob_get_clean();
	}

	/**
	 * Display Procaptcha form.
	 *
	 * @param array $args Arguments.
	 */
	public static function form_display( array $args = [] ) {
		$settings          = procaptcha()->settings();
		$procaptcha_site_key = $settings->get_site_key();
		$procaptcha_theme    = $settings->get( 'theme' );
		$procaptcha_size     = $settings->get( 'size' );
		$allowed_sizes     = [ 'normal', 'compact', 'invisible' ];

		$args = wp_parse_args(
			$args,
			[
				'action'  => '', // Action name for wp_nonce_field.
				'name'    => '', // Nonce name for wp_nonce_field.
				'auto'    => false, // Whether a form has to be auto-verified.
				'size'    => $procaptcha_size, // The Procaptcha widget size.
				'id'      => [], // Procaptcha widget id.
				/**
				 * Example of id:
				 * [
				 *   'source'  => ['gravityforms/gravityforms.php'],
				 *   'form_id' => 23
				 * ]
				 */
				'protect' => true, // Protection status. When true, Procaptcha should be added. When false, hidden widget to be added.
			]
		);

		if ( $args['id'] ) {
			$id            = (array) $args['id'];
			$id['source']  = (array) ( $id['source'] ?? [] );
			$id['form_id'] = $id['form_id'] ?? 0;

			/**
			 * Filters the protection status of a form.
			 *
			 * @param bool       $value   The protection status of a form.
			 * @param string[]   $source  The source of the form (plugin, theme, WordPress Core).
			 * @param int|string $form_id Form id.
			 */
			if (
				! $args['protect'] ||
				! apply_filters( 'hcap_protect_form', true, $id['source'], $id['form_id'] )
			) {
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				$encoded_id = base64_encode( wp_json_encode( $id ) );
				$widget_id  = $encoded_id . '-' . wp_hash( $encoded_id );
				?>
				<input
					type="hidden"
					class="<?php echo esc_attr( self::PROCAPTCHA_WIDGET_ID ); ?>"
					name="<?php echo esc_attr( self::PROCAPTCHA_WIDGET_ID ); ?>"
					value="<?php echo esc_attr( $widget_id ); ?>">
				<?php

				procaptcha()->form_shown = true;

				return;
			}
		}

		$args['auto'] = filter_var( $args['auto'], FILTER_VALIDATE_BOOLEAN );
		$args['size'] = in_array( $args['size'], $allowed_sizes, true ) ? $args['size'] : $procaptcha_size;

		?>
		<div
			class="h-captcha"
			data-sitekey="<?php echo esc_attr( $procaptcha_site_key ); ?>"
			data-theme="<?php echo esc_attr( $procaptcha_theme ); ?>"
			data-size="<?php echo esc_attr( $args['size'] ); ?>"
			data-auto="<?php echo $args['auto'] ? 'true' : 'false'; ?>">
		</div>
		<?php

		if ( ! empty( $args['action'] ) && ! empty( $args['name'] ) ) {
			wp_nonce_field( $args['action'], $args['name'] );
		}

		procaptcha()->form_shown = true;
	}

	/**
	 * Whether form protection is enabled/disabled via Procaptcha widget id.
	 *
	 * Return false(protection disabled) in only one case:
	 * when $_POST['procaptcha-widget-id'] contains encoded id with proper hash
	 * and hcap_protect_form filter confirms that form referenced in widget id is not protected.
	 *
	 * @return bool
	 */
	public static function is_protection_enabled(): bool {
		// Nonce is checked in procaptcha_verify_post().
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$widget_id = isset( $_POST[ self::PROCAPTCHA_WIDGET_ID ] ) ?
			filter_var( wp_unslash( $_POST[ self::PROCAPTCHA_WIDGET_ID ] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ) :
			'';
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( ! $widget_id ) {
			return true;
		}

		list( $encoded_id, $hash ) = explode( '-', $widget_id );

		$id = wp_parse_args(
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			(array) json_decode( base64_decode( $encoded_id ), true ),
			self::$default_id
		);

		return ! (
			wp_hash( $encoded_id ) === $hash &&
			! apply_filters( 'hcap_protect_form', true, $id['source'], $id['form_id'] )
		);
	}

	/**
	 * Get procaptcha widget id from $_POST.
	 *
	 * @return array
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	public static function get_widget_id(): array {
		// Nonce is checked in procaptcha_verify_post().
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$widget_id = isset( $_POST[ self::PROCAPTCHA_WIDGET_ID ] ) ?
			filter_var( wp_unslash( $_POST[ self::PROCAPTCHA_WIDGET_ID ] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ) :
			'';
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( ! $widget_id ) {
			return self::$default_id;
		}

		list( $encoded_id, $hash ) = explode( '-', $widget_id );

		return wp_parse_args(
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			(array) json_decode( base64_decode( $encoded_id ), true ),
			self::$default_id
		);
	}

	/**
	 * Get source which class serves.
	 *
	 * @param string $class_name Class name.
	 *
	 * @return array
	 */
	public static function get_class_source( string $class_name ): array {
		foreach ( procaptcha()->modules as $module ) {
			if ( in_array( $class_name, (array) $module[2], true ) ) {
				$source = $module[1];

				// For WP Core (empty $source string), return option value.
				return '' === $source ? [ 'WordPress' ] : (array) $source;
			}
		}

		return [];
	}

	/**
	 * Get Procaptcha plugin notice.
	 *
	 * @return string[]
	 * @noinspection HtmlUnknownTarget
	 */
	public static function get_procaptcha_plugin_notice(): array {
		$url                   = admin_url( 'options-general.php?page=procaptcha&tab=general' );
		$notice['label']       = esc_html__( 'Procaptcha plugin is active', 'procaptcha-wordpress' );
		$notice['description'] = wp_kses_post(
			sprintf(
			/* translators: 1: link to the General setting page */
				__( 'When Procaptcha plugin is active and integration is on, Procaptcha settings must be modified on the %1$s.', 'procaptcha-wordpress' ),
				sprintf(
					'<a href="%s" target="_blank">General settings page</a>',
					esc_url( $url )
				)
			)
		);

		return $notice;
	}

	/**
	 * Retrieves the number of times a filter has been applied during the current request.
	 *
	 * Introduced in WP 6.1.0.
	 *
	 * @global int[] $wp_filters Stores the number of times each filter was triggered.
	 *
	 * @param string $hook_name The name of the filter hook.
	 * @return int The number of times the filter hook has been applied.
	 */
	public static function did_filter( string $hook_name ): int {
		global $wp_filters;

		return $wp_filters[ $hook_name ] ?? 0;
	}

	/**
	 * Add Procaptcha error message to WP_Error object.
	 *
	 * @param WP_Error|mixed $errors        A WP_Error object containing any errors.
	 * @param string|null    $error_message Error message.
	 *
	 * @return WP_Error
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public static function add_error_message( $errors, $error_message ): WP_Error {
		if ( null === $error_message ) {
			return $errors;
		}

		$code = array_search( $error_message, hcap_get_error_messages(), true ) ?: 'fail';

		$errors = is_wp_error( $errors ) ? $errors : new WP_Error();

		if ( ! isset( $errors->errors[ $code ] ) || ! in_array( $error_message, $errors->errors[ $code ], true ) ) {
			$errors->add( $code, $error_message );
		}

		return $errors;
	}
}
