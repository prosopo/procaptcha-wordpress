<?php
/**
 * GeneralTest class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\Tests\Integration\Wordfence;

use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;
use Procaptcha\Wordfence\General;
use Procaptcha\WP\Login;
use ReflectionException;

/**
 * Test General class.
 *
 * @group wordfence
 */
class GeneralTest extends ProcaptchaWPTestCase {

	/**
	 * Test init_hooks().
	 *
	 * @param string $wordfence_status Wordfence status.
	 * @dataProvider dp_test_init_hooks
	 */
	public function test_init_hooks( string $wordfence_status ) {
		if ( 'login' === $wordfence_status ) {
			update_option(
				'procaptcha_settings',
				[
					'wordfence_status' => [ 'login' ],
				]
			);
		}

		procaptcha()->init_hooks();

		$subject = new General();

		if ( 'login' === $wordfence_status ) {
			self::assertSame( [ 'on' ], procaptcha()->settings()->get( 'recaptcha_compat_off' ) );
			self::assertSame( 20, has_action( 'login_enqueue_scripts', [ $subject, 'remove_wordfence_recaptcha_script' ] ) );
			self::assertSame( 10, has_filter( 'wordfence_ls_require_captcha', [ $subject, 'block_wordfence_recaptcha' ] ) );
		} else {
			self::assertSame( 10, has_action( 'plugins_loaded', [ $subject, 'remove_wp_login_procaptcha_hooks' ] ) );
		}
	}

	/**
	 * Data provider for test_init_hooks().
	 *
	 * @return array
	 */
	public function dp_test_init_hooks(): array {
		return [
			'not active' => [ '' ],
			'active'     => [ 'login' ],
		];
	}

	/**
	 * Test remove_wordfence_recaptcha_script().
	 *
	 * @return void
	 */
	public function test_remove_wordfence_recaptcha_script() {
		$handle = 'wordfence-ls-recaptcha';

		wp_enqueue_script(
			$handle,
			'http://test.test/some.js',
			[],
			'1.0.0',
			true
		);
		self::assertTrue( wp_script_is( $handle ) );

		$subject = new General();

		$subject->remove_wordfence_recaptcha_script();

		self::assertFalse( wp_script_is( $handle ) );
		self::assertFalse( wp_script_is( $handle, 'registered' ) );
	}

	/**
	 * Test block_wordfence_recaptcha().
	 *
	 * @return void
	 */
	public function test_block_wordfence_recaptcha() {
		$subject = new General();

		self::assertFalse( $subject->block_wordfence_recaptcha() );
	}

	/**
	 * Test remove_wp_login_procaptcha_hooks().
	 *
	 * @return void
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_remove_wp_login_procaptcha_hooks() {
		$subject = new General();

		$subject->remove_wp_login_procaptcha_hooks();

		$main     = procaptcha();
		$wp_login = new Login();

		$loaded_classes                 = $this->get_protected_property( $main, 'loaded_classes' );
		$loaded_classes[ Login::class ] = $wp_login;

		$this->set_protected_property( $main, 'loaded_classes', $loaded_classes );

		$wp_login = procaptcha()->get( Login::class );

		self::assertSame( 10, has_action( 'login_form', [ $wp_login, 'add_captcha' ] ) );
		self::assertSame( PHP_INT_MAX, has_filter( 'wp_authenticate_user', [ $wp_login, 'check_signature' ] ) );

		$subject->remove_wp_login_procaptcha_hooks();

		self::assertFalse( has_action( 'login_form', [ $wp_login, 'add_captcha' ] ) );
		self::assertFalse( has_filter( 'wp_authenticate_user', [ $wp_login, 'check_signature' ] ) );
	}
}
