<?php
/**
 * ProcaptchaWPTestCase class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\Tests\Integration;

use Codeception\TestCase\WPTestCase;
use Procaptcha\Helpers\Procaptcha;
use Mockery;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use tad\FunctionMocker\FunctionMocker;

/**
 * Class ProcaptchaWPTestCase
 */
class ProcaptchaWPTestCase extends WPTestCase {

	/**
	 * Setup test
	 */
	public function setUp(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		FunctionMocker::setUp();
		parent::setUp();

		procaptcha()->has_result = false;
		procaptcha()->form_shown = false;

		$_SERVER['REQUEST_URI'] = 'http://test.test/';
	}

	/**
	 * End test
	 */
	public function tearDown(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		unset( $_POST, $_SERVER['REQUEST_URI'], $_SERVER['HTTP_CLIENT_IP'] );

		delete_option( 'procaptcha_settings' );
		procaptcha()->init_hooks();

		Mockery::close();
		parent::tearDown();
		FunctionMocker::tearDown();
	}

	/**
	 * Get an object protected property.
	 *
	 * @param object $subject       Object.
	 * @param string $property_name Property name.
	 *
	 * @return mixed
	 *
	 * @throws ReflectionException Reflection exception.
	 */
	protected function get_protected_property( $subject, string $property_name ) {
		$property = ( new ReflectionClass( $subject ) )->getProperty( $property_name );

		$property->setAccessible( true );

		$value = $property->getValue( $subject );

		$property->setAccessible( false );

		return $value;
	}

	/**
	 * Set an object protected property.
	 *
	 * @param object $subject       Object.
	 * @param string $property_name Property name.
	 * @param mixed  $value         Property vale.
	 *
	 * @throws ReflectionException Reflection exception.
	 */
	protected function set_protected_property( $subject, string $property_name, $value ) {
		$property = ( new ReflectionClass( $subject ) )->getProperty( $property_name );

		$property->setAccessible( true );
		$property->setValue( $subject, $value );
		$property->setAccessible( false );
	}

	/**
	 * Set an object protected method accessibility.
	 *
	 * @param object $subject     Object.
	 * @param string $method_name Property name.
	 * @param bool   $accessible  Property vale.
	 *
	 * @return ReflectionMethod
	 *
	 * @throws ReflectionException Reflection exception.
	 */
	protected function set_method_accessibility( $subject, string $method_name, bool $accessible = true ): ReflectionMethod {
		$method = ( new ReflectionClass( $subject ) )->getMethod( $method_name );

		$method->setAccessible( $accessible );

		return $method;
	}

	/**
	 * Return Procaptcha::get_widget() content.
	 *
	 * @param array $id The procap_ widget id.
	 *
	 * @return string
	 */
	protected function get_procap_widget( array $id ): string {
		$id['source']  = (array) ( $id['source'] ?? [] );
		$id['form_id'] = $id['form_id'] ?? 0;

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$encoded_id = base64_encode( wp_json_encode( $id ) );
		$widget_id  = $encoded_id . '-' . wp_hash( $encoded_id );

		return '		<input
				type="hidden"
				class="procaptcha-widget-id"
				name="procaptcha-widget-id"
				value="' . $widget_id . '">';
	}

	/**
	 * Return Procaptcha::form_display() content.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string
	 */
	protected function get_procap_form( array $args = [] ): string {
		$nonce_field = '';

		if ( ! empty( $args['action'] ) && ! empty( $args['name'] ) ) {
			$nonce_field = wp_nonce_field( $args['action'], $args['name'], true, false );
		}

		$data_sitekey = $args['data-sitekey'] ?? '';
		$data_theme   = $args['data-theme'] ?? '';
		$data_auto    = $args['auto'] ?? false;
		$data_auto    = $data_auto ? 'true' : 'false';
		$data_force   = $args['force'] ?? false;
		$data_force   = $data_force ? 'true' : 'false';
		$data_size    = $args['size'] ?? '';
		$id           = $args['id'] ?? [];

		$default_id = [
			'source'  => [],
			'form_id' => 0,
		];

		$id = wp_parse_args(
			$id,
			$default_id
		);

		return $this->get_procap_widget( $id ) . '
				<div
			class="procaptcha"
			data-sitekey="' . $data_sitekey . '"
			data-theme="' . $data_theme . '"
			data-size="' . $data_size . '"
			data-auto="' . $data_auto . '"
			data-force="' . $data_force . '">
		</div>
		' . $nonce_field;
	}

	/**
	 * Prepare response from procaptcha_request_verify().
	 *
	 * @param string    $procaptcha_response procap_ response.
	 * @param bool|null $result            Desired result.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	protected function prepare_procaptcha_request_verify( string $procaptcha_response, $result = true ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['procaptcha-response'] ) ) {
			$_POST[ PROCAPTCHA_NONCE ]     = wp_create_nonce( PROCAPTCHA_ACTION );
			$_POST['procaptcha-response'] = $procaptcha_response;
		}

		$raw_response = wp_json_encode( [ 'success' => $result ] );

		if ( null === $result ) {
			$raw_response = '';
		}

		$procaptcha_secret_key = 'some secret key';

		update_option( 'procaptcha_settings', [ 'secret_key' => $procaptcha_secret_key ] );
		procaptcha()->init_hooks();

		$ip                        = '7.7.7.7';
		$_SERVER['HTTP_CLIENT_IP'] = $ip;

		add_filter(
			'pre_http_request',
			static function ( $preempt, $parsed_args, $url ) use ( $procaptcha_secret_key, $procaptcha_response, $raw_response, $ip ) {
				$expected_url  =
					'https://api.procaptcha.io/siteverify';
				$expected_body = [
					'secret'   => $procaptcha_secret_key,
					'response' => $procaptcha_response,
					'remoteip' => $ip,
				];

				if ( $expected_url === $url && $expected_body === $parsed_args['body'] ) {
					return [
						'body' => $raw_response,
					];
				}

				return null;
			},
			10,
			3
		);
	}

	/**
	 * Prepare response for procaptcha_verify_POST().
	 *
	 * @param string    $nonce_field_name  Nonce field name.
	 * @param string    $nonce_action_name Nonce action name.
	 * @param bool|null $result            Desired result.
	 *
	 * @noinspection PhpMissingParamTypeInspection*/
	protected function prepare_procaptcha_verify_post( string $nonce_field_name, string $nonce_action_name, $result = true ) {
		if ( null === $result ) {
			return;
		}

		$procaptcha_response = 'some response';

		$_POST[ $nonce_field_name ]  = wp_create_nonce( $nonce_action_name );
		$_POST['procaptcha-response'] = $procaptcha_response;

		$this->prepare_procaptcha_request_verify( $procaptcha_response, $result );
	}

	/**
	 * Prepare response from procaptcha_get_verify_message().
	 *
	 * @param string    $nonce_field_name  Nonce field name.
	 * @param string    $nonce_action_name Nonce action name.
	 * @param bool|null $result            Desired result.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	protected function prepare_procaptcha_get_verify_message( string $nonce_field_name, string $nonce_action_name, $result = true ) {
		$this->prepare_procaptcha_verify_post( $nonce_field_name, $nonce_action_name, $result );
	}

	/**
	 * Prepare response from procaptcha_get_verify_message_html().
	 *
	 * @param string    $nonce_field_name  Nonce field name.
	 * @param string    $nonce_action_name Nonce action name.
	 * @param bool|null $result            Desired result.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	protected function prepare_procaptcha_get_verify_message_html( string $nonce_field_name, string $nonce_action_name, $result = true ) {
		$this->prepare_procaptcha_get_verify_message( $nonce_field_name, $nonce_action_name, $result );
	}

	/**
	 * Get encoded signature.
	 *
	 * @param string[]   $source         Signature source.
	 * @param int|string $form_id        Form id.
	 * @param bool       $procaptcha_shown The procap_ was shown.
	 *
	 * @return string
	 */
	protected function get_encoded_signature( array $source, $form_id, bool $procaptcha_shown ): string {
		$id = [
			'source'         => $source,
			'form_id'        => $form_id,
			'procaptcha_shown' => $procaptcha_shown,
		];

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$encoded_id = base64_encode( wp_json_encode( $id ) );

		return $encoded_id . '-' . wp_hash( $encoded_id );
	}
}
