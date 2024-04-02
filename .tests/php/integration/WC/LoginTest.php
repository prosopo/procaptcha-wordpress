<?php
/**
 * LoginTest class file.
 *
 * @package Procaptcha\Tests
 */

namespace Procaptcha\Tests\Integration\WC;

use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;
use Procaptcha\WC\Login;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use WP_Error;

/**
 * Test Login class.
 *
 * @group wc-login
 * @group wc
 */
class LoginTest extends ProcaptchaWPTestCase {

	/**
	 * Tear down the test.
	 *
	 * @noinspection PhpLanguageLevelInspection
	 * @noinspection PhpUndefinedClassInspection
	 * @throws ReflectionException ReflectionException.
	 */
	public function tearDown(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		$this->set_protected_property( procaptcha(), 'loaded_classes', [] );

		parent::tearDown();
	}

	/**
	 * Test constructor and init_hooks().
	 */
	public function test_constructor_and_init_hooks() {
		$subject = new Login();

		self::assertSame(
			10,
			has_action( 'woocommerce_login_form', [ $subject, 'add_captcha' ] )
		);
		self::assertSame(
			10,
			has_action( 'woocommerce_process_login_errors', [ $subject, 'verify' ] )
		);
	}

	/**
	 * Test add_captcha().
	 */
	public function test_add_captcha() {
		$args     = [
			'action' => 'procaptcha_login',
			'name'   => 'procaptcha_login_nonce',
			'id'     => [
				'source'  => [ 'woocommerce/woocommerce.php' ],
				'form_id' => 'login',
			],
		];
		$expected = $this->get_procap_form( $args );

		$subject = new Login();

		ob_start();

		$subject->add_captcha();

		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Test verify().
	 */
	public function test_verify() {
		$validation_error = new WP_Error();

		$this->prepare_procaptcha_get_verify_message( 'procaptcha_login_nonce', 'procaptcha_login' );

		$subject = new Login();

		add_filter( 'woocommerce_process_login_errors', [ $subject, 'verify' ] );

		self::assertEquals(
			$validation_error,
			apply_filters( 'woocommerce_process_login_errors', $validation_error )
		);
	}

	/**
	 * Test verify() when not in the WC filter.
	 */
	public function test_verify_NOT_wc_filter() {
		$validation_error = new WP_Error();

		$subject = new Login();

		self::assertEquals( $validation_error, $subject->verify( $validation_error ) );
	}

	/**
	 * Test verify() when not login limit exceeded.
	 */
	public function test_verify_NOT_login_limit_exceeded() {
		$validation_error = new WP_Error();

		$subject = new Login();

		add_filter(
			'procap_login_limit_exceeded',
			static function () {
				return false;
			}
		);

		add_filter( 'woocommerce_process_login_errors', [ $subject, 'verify' ] );

		self::assertEquals(
			$validation_error,
			apply_filters( 'woocommerce_process_login_errors', $validation_error )
		);
	}

	/**
	 * Test verify() not verified.
	 */
	public function test_verify_not_verified() {
		$validation_error = 'some wrong error, to be replaced by WP_Error';
		$expected         = new WP_Error();
		$expected->add( 'procaptcha_error', 'The procap_ is invalid.' );

		$this->prepare_procaptcha_get_verify_message( 'procaptcha_login_nonce', 'procaptcha_login', false );

		$subject = new Login();

		add_filter( 'woocommerce_process_login_errors', [ $subject, 'verify' ] );

		self::assertEquals(
			$expected,
			apply_filters( 'woocommerce_process_login_errors', $validation_error )
		);
	}

	/**
	 * Test print_inline_styles().
	 *
	 * @return void
	 */
	public function test_print_inline_styles() {
		FunctionMocker::replace(
			'defined',
			static function ( $constant_name ) {
				return 'SCRIPT_DEBUG' === $constant_name;
			}
		);

		FunctionMocker::replace(
			'constant',
			static function ( $name ) {
				return 'SCRIPT_DEBUG' === $name;
			}
		);

		$expected = <<<CSS
	.woocommerce-form-login .procaptcha {
		margin-top: 2rem;
	}
CSS;
		$expected = "<style>\n$expected\n</style>\n";

		$subject = new Login();

		ob_start();

		$subject->print_inline_styles();

		self::assertSame( $expected, ob_get_clean() );
	}
}
