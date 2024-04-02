<?php
/**
 * MainTest class file.
 *
 * @package Procaptcha\Tests
 */

namespace Procaptcha\Tests\Unit;

use Procaptcha\Main;
use Mockery;
use tad\FunctionMocker\FunctionMocker;

/**
 * Test Main class.
 *
 * @group main
 */
class MainTest extends ProcaptchaTestCase {

	/**
	 * Test init().
	 */
	public function test_is_xml_rpc() {
		$mock = Mockery::mock( Main::class )->makePartial();

		$mock->shouldAllowMockingProtectedMethods();

		self::assertFalse( $mock->is_xml_rpc() );

		FunctionMocker::replace(
			'defined',
			static function ( $constant_name ) {
				return 'XMLRPC_REQUEST' === $constant_name;
			}
		);

		FunctionMocker::replace(
			'constant',
			static function ( $name ) {
				return 'XMLRPC_REQUEST' === $name;
			}
		);

		self::assertTrue( $mock->is_xml_rpc() );
	}
	/**
	 * Test declare_wc_compatibility().
	 *
	 * @return void
	 * @noinspection UnusedFunctionResultInspection
	 */
	public function test_declare_wc_compatibility() {
		$mock = Mockery::mock( 'alias:Automattic\WooCommerce\Utilities\FeaturesUtil' );
		$mock->shouldReceive( 'declare_compatibility' )
			->with( 'custom_order_tables', PROCAPTCHA_TEST_FILE, true )
			->andReturn( true );

		FunctionMocker::replace(
			'constant',
			static function ( $name ) {
				if ( 'PROCAPTCHA_FILE' === $name ) {
					return PROCAPTCHA_TEST_FILE;
				}

				return '';
			}
		);

		$subject = new Main();
		$subject->declare_wc_compatibility();
	}
}
