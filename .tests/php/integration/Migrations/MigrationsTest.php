<?php
/**
 * MigrationsTest class file.
 *
 * @package Procaptcha\Tests
 */

namespace Procaptcha\Tests\Integration\Migrations;

use Procaptcha\Migrations\Migrations;
use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;
use Mockery;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;

/**
 * Test MigrationsTest class.
 *
 * @group migrations
 */
class MigrationsTest extends ProcaptchaWPTestCase {

	/**
	 * Tear down test.
	 *
	 * @return void
	 * @noinspection PhpLanguageLevelInspection
	 * @noinspection PhpUndefinedClassInspection
	 */
	public function tearDown(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		unset( $_GET['service-worker'], $GLOBALS['current_screen'] );

		parent::tearDown();
	}
	/**
	 * Test init() and init_hooks().
	 *
	 * @param bool     $worker   The service-worker is set.
	 * @param bool     $admin    In admin.
	 * @param bool|int $expected Expected value.
	 *
	 * @return void
	 * @dataProvider dp_test_init_and_init_hooks
	 */
	public function test_init_and_init_hooks( bool $worker, bool $admin, $expected ) {
		if ( $worker ) {
			$_GET['service-worker'] = 'some';
		}

		if ( $admin ) {
			set_current_screen( 'some-screen' );
		}

		$subject = new Migrations();

		self::assertSame( $expected, has_action( 'plugins_loaded', [ $subject, 'migrate' ] ) );
	}

	/**
	 * Data provider for test_init_and_init_hooks().
	 *
	 * @return array
	 */
	public function dp_test_init_and_init_hooks(): array {
		return [
			[ false, false, false ],
			[ true, false, false ],
			[ false, true, - PHP_INT_MAX ],
			[ true, true, false ],
		];
	}

	/**
	 * Test migrate().
	 *
	 * @return void
	 */
	public function test_migrate() {
		FunctionMocker::replace( 'time', time() );

		$time              = time();
		$size              = 'normal';
		$expected_option   = [
			'2.0.0'          => $time,
			'3.6.0'          => $time,
			PROCAPTCHA_VERSION => $time,
		];
		$expected_settings = [
			'site_key'                     => '',
			'secret_key'                   => '',
			'theme'                        => '',
			'size'                         => $size,
			'language'                     => '',
			'off_when_logged_in'           => [],
			'recaptcha_compat_off'         => [],
			'wp_status'                    => [],
			'bbp_status'                   => [],
			'bp_status'                    => [],
			'cf7_status'                   => [],
			'divi_status'                  => [],
			'elementor_pro_status'         => [],
			'fluent_status'                => [],
			'gravity_status'               => [],
			'jetpack_status'               => [],
			'mailchimp_status'             => [],
			'memberpress_status'           => [],
			'ninja_status'                 => [],
			'subscriber_status'            => [],
			'ultimate_member_status'       => [],
			'woocommerce_status'           => [],
			'woocommerce_wishlists_status' => [],
			'wpforms_status'               => [ 'form' ],
			'wpforo_status'                => [],
			'custom_themes'                => [],
			'_network_wide'                => [],
		];

		update_option( 'procaptcha_size', $size );
		update_option( 'procaptcha_wpforms_status', 'on' );

		self::assertSame( [], get_option( 'procaptcha_settings', [] ) );

		$subject = new Migrations();

		self::assertSame( [], get_option( $subject::MIGRATED_VERSIONS_OPTION_NAME, [] ) );

		$subject->migrate();

		self::assertTrue( $this->compare_migrated( $expected_option, get_option( $subject::MIGRATED_VERSIONS_OPTION_NAME, [] ) ) );
		self::assertSame( $expected_settings, get_option( 'procaptcha_settings', [] ) );
		self::assertFalse( get_option( 'procaptcha_size' ) );
		self::assertFalse( get_option( 'procaptcha_wpforms_status' ) );

		// No migrations on the second run.
		$subject = new Migrations();

		$subject->migrate();

		self::assertTrue( $this->compare_migrated( $expected_option, get_option( $subject::MIGRATED_VERSIONS_OPTION_NAME, [] ) ) );
	}

	/**
	 * Compare migrated option data.
	 *
	 * @param array $expected_option Expected option.
	 * @param array $option          Actual option.
	 *
	 * @return bool
	 */
	private function compare_migrated( array $expected_option, array $option ): bool {
		if ( array_keys( $expected_option ) !== array_keys( $option ) ) {
			return false;
		}

		foreach ( $expected_option as $version => $time ) {
			// Due to the glitch with mocking time(), let us allow 5 seconds time difference.
			if ( $option[ $version ] - $time > 5 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Test migrate_360() when WPForms status not set.
	 *
	 * @return void
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_migrate_360_when_wpforms_status_not_set() {
		$method  = 'migrate_360';
		$subject = Mockery::mock( Migrations::class )->makePartial();

		$this->set_method_accessibility( $subject, $method );

		$option = get_option( 'procaptcha_settings', [] );

		$subject->$method();

		self::assertSame( $option, get_option( 'procaptcha_settings', [] ) );
	}
}