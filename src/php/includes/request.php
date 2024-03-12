<?php
/**
 * Request file.
 *
 * @package procaptcha-wp
 */

use PROCAPTCHA\Helpers\PROCAPTCHA;

/**
 * Determines the user's actual IP address and attempts to partially
 * anonymize an IP address by converting it to a network ID.
 *
 * Based on the code of the \WP_Community_Events::get_unsafe_client_ip.
 * Returns a string with the IP address or false for local IPs.
 *
 * @return false|string
 */
function procap_get_user_ip() {
	$client_ip = false;

	// In order of preference, with the best ones for this purpose first.
	$address_headers = [
		'HTTP_CF_CONNECTING_IP',
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_X_CLUSTER_CLIENT_IP',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'REMOTE_ADDR',
	];

	foreach ( $address_headers as $header ) {
		if ( ! array_key_exists( $header, $_SERVER ) ) {
			continue;
		}

		$address_chain = explode(
			',',
			filter_var( wp_unslash( $_SERVER[ $header ] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
			2
		);
		$client_ip     = trim( $address_chain[0] );

		break;
	}

	// Filter out local addresses.
	return filter_var(
		$client_ip,
		FILTER_VALIDATE_IP,
		FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
	);
}

/**
 * Get error messages provided by API and the plugin.
 *
 * @return array
 */
function procap_get_error_messages(): array {
	/**
	 * Filters procaptcha error messages.
	 *
	 * @param array $error_messages Error messages.
	 */
	return apply_filters(
		'procap_error_messages',
		[
			// Plugin messages.
			'empty'                            => __( 'Please complete the procaptcha.', 'procaptcha-for-forms-and-more' ),
		]
	);
}

/**
 * Get procaptcha error message.
 *
 * @param string|string[] $error_codes Error codes.
 *
 * @return string
 */
function procap_get_error_message( $error_codes ): string {
	$error_codes = (array) $error_codes;
	$errors      = procap_get_error_messages();
	$message_arr = [];

	foreach ( $error_codes as $error_code ) {
		if ( array_key_exists( $error_code, $errors ) ) {
			$message_arr[] = $errors[ $error_code ];
		}
	}

	if ( ! $message_arr ) {
		return '';
	}

	$header = _n( 'procaptcha error:', 'procaptcha errors:', count( $message_arr ), 'procaptcha-for-forms-and-more' );

	return $header . ' ' . implode( '; ', $message_arr );
}

if ( ! function_exists( 'procaptcha_request_verify' ) ) {
	/**
	 * Verify procaptcha response.
	 *
	 * @param string|null $procaptcha_response procaptcha response.
	 *
	 * @return null|string Null on success, error message on failure.
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection UnnecessaryBooleanExpressionInspection
	 */
	function procaptcha_request_verify( $procaptcha_response ) {
		
		static $result;
		$result      = null;
		//return apply_filters( 'procap_verify_request', $result, $error_codes );
		// Do not make remote request more than once.
		if ( procaptcha()->has_result ) {
			/**
			 * Filters the result of request verification.
			 *
			 * @param string|null $result      The result of verification. The null means success.
			 * @param string[]    $error_codes Error code(s). Empty array on success.
			 */
			return apply_filters( 'procap_verify_request', $result, [] );
		}

		procaptcha()->has_result = true;

		if ( ! PROCAPTCHA::is_protection_enabled() ) {
			$result = null;

			/** This filter is documented above. */
			return apply_filters( 'procap_verify_request', $result, [] );
		}

		$procaptcha_response_sanitized = htmlspecialchars(
			filter_var( $procaptcha_response, FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
			ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401
		);

		$errors = procap_get_error_messages();

		$empty_message = $errors['empty'];
		$fail_message  = $errors['fail'];

		if ( '' === $procaptcha_response_sanitized ) {
			$result = $empty_message;

			/** This filter is documented above. */
			return apply_filters( 'procap_verify_request', $result, [ 'empty' ] );
		}

	

	

	

		// Success.
		$result      = null;
		$error_codes = [];

	

		/** This filter is documented above. */
		return apply_filters( 'procap_verify_request', $result, $error_codes );
	}
}

if ( ! function_exists( 'procaptcha_verify_post' ) ) {
	/**
	 * Verify POST.
	 *
	 * @param string $nonce_field_name  Nonce field name.
	 * @param string $nonce_action_name Nonce action name.
	 *
	 * @return null|string Null on success, error message on failure.
	 */
	function procaptcha_verify_post( string $nonce_field_name = PROCAPTCHA_NONCE, string $nonce_action_name = PROCAPTCHA_ACTION ) {

		$procaptcha_response = isset( $_POST['procaptcha-response'] ) ?
			filter_var( wp_unslash( $_POST['procaptcha-response'] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ) :
			'';

		$procaptcha_nonce = isset( $_POST[ $nonce_field_name ] ) ?
			filter_var( wp_unslash( $_POST[ $nonce_field_name ] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ) :
			'';

		// Verify nonce for logged-in users only.
		if (
			is_user_logged_in() &&
			! wp_verify_nonce( $procaptcha_nonce, $nonce_action_name ) &&
			PROCAPTCHA::is_protection_enabled()
		) {
			$errors = procap_get_error_messages();

			/** This filter is documented above. */
			return apply_filters( 'procap_verify_request', $errors['bad-nonce'], [ 'bad-nonce' ] );
		}

		return procaptcha_request_verify( $procaptcha_response );
	}
}

if ( ! function_exists( 'procaptcha_get_verify_output' ) ) {
	/**
	 * Get verify output.
	 *
	 * @param string $empty_message     Empty message.
	 * @param string $fail_message      Fail message.
	 * @param string $nonce_field_name  Nonce field name.
	 * @param string $nonce_action_name Nonce action name.
	 *
	 * @return null|string Null on success, error message on failure.
	 */
	function procaptcha_get_verify_output( string $empty_message, string $fail_message, string $nonce_field_name, string $nonce_action_name ) {
		if ( ! empty( $empty_message ) || ! empty( $fail_message ) ) {
			_deprecated_argument( __FUNCTION__, '2.1.0' );
		}

		return procaptcha_verify_post( $nonce_field_name, $nonce_action_name );
	}
}

if ( ! function_exists( 'procaptcha_get_verify_message' ) ) {
	/**
	 * Get a verify message.
	 *
	 * @param string $nonce_field_name  Nonce field name.
	 * @param string $nonce_action_name Nonce action name.
	 *
	 * @return null|string Null on success, error message on failure.
	 */
	function procaptcha_get_verify_message( string $nonce_field_name, string $nonce_action_name ) {
		return procaptcha_get_verify_output( '', '', $nonce_field_name, $nonce_action_name );
	}
}

if ( ! function_exists( 'procaptcha_get_verify_message_html' ) ) {
	/**
	 * Get verify message html.
	 *
	 * @param string $nonce_field_name  Nonce field name.
	 * @param string $nonce_action_name Nonce action name.
	 *
	 * @return null|string Null on success, error message on failure.
	 */
	function procaptcha_get_verify_message_html( string $nonce_field_name, string $nonce_action_name ) {
		$message = procaptcha_get_verify_output( '', '', $nonce_field_name, $nonce_action_name );

		if ( null === $message ) {
			return null;
		}

		$header = _n( 'procaptcha error:', 'procaptcha errors:', substr_count( $message, ';' ) + 1, 'procaptcha-for-forms-and-more' );

		if ( false === strpos( $message, $header ) ) {
			$message = $header . ' ' . $message;
		}

		return str_replace( $header, '<strong>' . $header . '</strong>', $message );
	}
}

if ( ! function_exists( 'procap_procaptcha_error_message' ) ) {
	/**
	 * Print error message.
	 *
	 * @param string $procaptcha_content Content of procaptcha.
	 *
	 * @return string
	 */
	function procap_procaptcha_error_message( string $procaptcha_content = '' ): string {
		_deprecated_function( __FUNCTION__, '2.1.0' );

		$message = sprintf(
			'<p id="procap_error" class="error procap_error">%s</p>',
			__( 'The procaptcha is invalid.', 'procaptcha-for-forms-and-more' )
		);

		return $message . $procaptcha_content;
	}
}
