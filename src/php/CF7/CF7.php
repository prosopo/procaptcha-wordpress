<?php
/**
 * CF7 form class file.
 *
 * @package procaptcha-wp
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */

namespace PROCAPTCHA\CF7;

use PROCAPTCHA\Helpers\PROCAPTCHA;
use WPCF7_FormTag;
use WPCF7_Submission;
use WPCF7_Validation;

/**
 * Class CF7.
 */
class CF7 {
	const HANDLE    = 'procaptcha-cf7';
	const SHORTCODE = 'cf7-procaptcha';
	const DATA_NAME = 'procap-cf7';

	/**
	 * CF7 constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 */
	public function init_hooks() {
		add_filter( 'do_shortcode_tag', [ $this, 'wpcf7_shortcode' ], 20, 4 );
		add_shortcode( self::SHORTCODE, [ $this, 'cf7_procaptcha_shortcode' ] );
		add_filter( 'wpcf7_validate', [ $this, 'verify_procaptcha' ], 20, 2 );
		add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_scripts' ], 9 );
	}

	/**
	 * Add Procaptcha to CF7 form.
	 *
	 * @param string|mixed $output Shortcode output.
	 * @param string       $tag    Shortcode name.
	 * @param array|string $attr   Shortcode attributes array or empty string.
	 * @param array        $m      Regular expression match array.
	 *
	 * @return string|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function wpcf7_shortcode( $output, string $tag, $attr, array $m ) {
		
		
		if ( 'contact-form-7' !== $tag ) {
			return $output;
		}

		remove_filter( 'do_shortcode_tag', [ $this, 'wpcf7_shortcode' ], 20 );

		$output  = (string) $output;
		$form_id = isset( $attr['id'] ) ? (int) $attr['id'] : 0;

		if ( has_shortcode( $output, self::SHORTCODE ) ) {
			$output = do_shortcode( $this->add_form_id_to_cf7_procap_shortcode( $output, $form_id ) );

			add_filter( 'do_shortcode_tag', [ $this, 'wpcf7_shortcode' ], 20, 4 );

			return $output;
		}

		$cf7_procap_form = do_shortcode( '[' . self::SHORTCODE . " form_id=\"$form_id\"]" );
		$submit_button = '/(<(input|button) .*?type="submit")/';

		add_filter( 'do_shortcode_tag', [ $this, 'wpcf7_shortcode' ], 20, 4 );

		return preg_replace(
			$submit_button,
			$cf7_procap_form . '$1',
			$output
		);
	}

	/**
	 * CF7 Procaptcha shortcode.
	 *
	 * @param array|string $attr Shortcode attributes.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function cf7_procaptcha_shortcode( $attr = [] ): string {
		$settings          = procaptcha()->settings();
		$procaptcha_site_key = $settings->get_site_key();
		$procaptcha_theme    = $settings->get( 'theme' );
		$procaptcha_size     = $settings->get( 'size' );
		$allowed_sizes     = [ 'normal', 'compact', 'invisible' ];

		$args = wp_parse_args(
			(array) $attr,
			/**
			 * CF7 works via REST API, where the current user is set to 0 (not logged in) if nonce is not present.
			 * However, we can add standard nonce for the action 'wp_rest' and rest_cookie_check_errors() provides the check.
			 */
			[
				'action'  => 'wp_rest', // Action name for wp_nonce_field.
				'name'    => '_wpnonce', // Nonce name for wp_nonce_field.
				'auto'    => false, // Whether a form has to be auto-verified.
				'size'    => $procaptcha_size, // The Procaptcha widget size.
				'id'      => [
					'source'  => PROCAPTCHA::get_class_source( __CLASS__ ),
					'form_id' => $attr['form_id'] ?? 0,
				], // Procaptcha widget id.
				/**
				 * Example of id:
				 * [
				 *   'source' => ['gravityforms/gravityforms.php'],
				 *   $form_id => 23
				 * ]
				 */
				'protect' => true,
			]
		);

		if ( $args['id'] ) {
			
			$id            = (array) $args['id'];
			$id['source']  = isset( $id['source'] ) ? (array) $id['source'] : [];
			$id['form_id'] = $id['form_id'] ?? 0;

			if (
				! $args['protect'] ||
				! apply_filters( 'procap_protect_form', true, $id['source'], $id['form_id'] )
			) {
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				$encoded_id = base64_encode( wp_json_encode( $id ) );
				$widget_id  = $encoded_id . '-' . wp_hash( $encoded_id );

				ob_start();
				?>
				<input
					type="hidden"
					class="<?php echo esc_attr( PROCAPTCHA::PROCAPTCHA_WIDGET_ID ); ?>"
					name="<?php echo esc_attr( PROCAPTCHA::PROCAPTCHA_WIDGET_ID ); ?>"
					value="<?php echo esc_attr( $widget_id ); ?>">
				<?php

				return ob_get_clean();
			}
		}

		$args['size'] = in_array( $args['size'], $allowed_sizes, true ) ? $args['size'] : $procaptcha_size;
		$callback     = 'invisible' === $args['size'] ? 'data-callback="ProcaptchaSubmit"' : '';

		procaptcha()->form_shown = true;

		return (
			'<div class="procaptcha" data-sitekey="' . esc_attr( $procaptcha_site_key ) . '"></div>' .
			wp_nonce_field( $args['action'], $args['name'], true, false )
		);
	}

	/**
	 * Verify CF7 recaptcha.
	 *
	 * @param WPCF7_Validation|mixed $result Result.
	 * @param WPCF7_FormTag[]|mixed  $tag    Tag.
	 *
	 * @return WPCF7_Validation|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function verify_procaptcha( $result, $tag ) {
		$submission = WPCF7_Submission::get_instance();
		

		if ( null === $submission ) {
			return $this->get_invalidated_result( $result );
		}

		$data           = $submission->get_posted_data();
		
		$response       = $data['procaptcha-response'] ?? '';
		
		
		$captcha_result = procaptcha_request_verify( $response );

		if ( null !== $captcha_result ) {
			return $this->get_invalidated_result( $result, $captcha_result );
		}

		return $result;
	}

	/**
	 * Get invalidated result.
	 *
	 * @param WPCF7_Validation|mixed $result         Result.
	 * @param string|null            $captcha_result Procaptcha result.
	 *
	 * @return WPCF7_Validation|mixed
	 * @noinspection PhpMissingParamTypeInspection
	 */
	private function get_invalidated_result( $result, $captcha_result = '' ) {
		if ( '' === $captcha_result ) {
			$captcha_result = procap_get_error_messages()['empty'];
		}

		$result->invalidate(
			[
				'type' => 'procaptcha',
				'name' => self::DATA_NAME,
			],
			$captcha_result
		);

		return $result;
	}

	/**
	 * Enqueue CF7 scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! procaptcha()->form_shown ) {
			return;
		}

		$min = procap_min_suffix();

		wp_enqueue_script(
			self::HANDLE,
			PROCAPTCHA_URL . "/assets/js/procaptcha-cf7$min.js",
			[],
			PROCAPTCHA_VERSION,
			true
		);
	}

	/**
	 * Add form_id to cf7_procaptcha shortcode if it does not exist.
	 * Replace to proper form_id if needed.
	 *
	 * @param string $output  CF7 form output.
	 * @param int    $form_id CF7 form id.
	 *
	 * @return string
	 */
	private function add_form_id_to_cf7_procap_shortcode( string $output, int $form_id ): string {
		$cf7_procap_sc_regex = get_shortcode_regex( [ self::SHORTCODE ] );

		// The preg_match should always be true, because $output has shortcode.
		if ( ! preg_match( "/$cf7_procap_sc_regex/", $output, $matches ) ) {
			// @codeCoverageIgnoreStart
			return $output;
			// @codeCoverageIgnoreEnd
		}

		$cf7_procap_sc = $matches[0];
		$atts        = shortcode_parse_atts( $cf7_procap_sc );

		unset( $atts[0] );

		if ( isset( $atts['form_id'] ) && (int) $atts['form_id'] === $form_id ) {
			return $output;
		}

		$atts['form_id'] = $form_id;

		array_walk(
			$atts,
			static function ( &$value, $key ) {
				$value = "$key=\"$value\"";
			}
		);

		$updated_cf_procap_sc = '[' . self::SHORTCODE . ' ' . implode( ' ', $atts ) . ']';

		return str_replace( $cf7_procap_sc, $updated_cf_procap_sc, $output );
	}
}
