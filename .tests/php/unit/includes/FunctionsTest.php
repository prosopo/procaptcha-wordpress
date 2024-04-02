<?php
/**
 * FunctionsTest class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

// phpcs:disable PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound

namespace Procaptcha\Tests\Unit\includes;

use Procaptcha\Tests\Unit\ProcaptchaTestCase;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Test functions file.
 *
 * @group functions
 */
class FunctionsTest extends ProcaptchaTestCase {

	/**
	 * Setup test class.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		WP_Mock::userFunction( 'add_shortcode' )->with( 'procaptcha', 'procap_shortcode' )->once();

		require_once PLUGIN_PATH . '/src/php/includes/functions.php';
	}

	/**
	 * Test procap_shortcode().
	 *
	 * @param array $atts     Attributes.
	 * @param array $expected Expected.
	 *
	 * @return void
	 * @dataProvider dp_test_procap_shortcode
	 */
	public function test_procap_shortcode( array $atts, array $expected ) {
		$pairs = [
			'action'  => PROCAPTCHA_ACTION,
			'name'    => PROCAPTCHA_NONCE,
			'auto'    => false,
			'force'   => false,
			'size'    => '',
			'id'      => [],
			'protect' => true,
		];
		$form  = 'some procaptcha form content';

		WP_Mock::userFunction( 'shortcode_atts' )
			->with( $pairs, $atts )
			->andReturnUsing(
				static function ( $pairs, $atts ) {
					return array_merge( $pairs, $atts );
				}
			);

		$procap_form = FunctionMocker::replace(
			'\Procaptcha\Helpers\Procaptcha::form',
			static function () use ( $form ) {
				return $form;
			}
		);

		self::assertSame( $form, procap_shortcode( $atts ) );

		$procap_form->wasCalledWithOnce( [ $expected ] );
	}

	/**
	 * Data provider for test_procap_shortcode().
	 *
	 * @return array
	 */
	public function dp_test_procap_shortcode(): array {
		return [
			'empty atts'  => [
				[],
				[
					'action'  => PROCAPTCHA_ACTION,
					'name'    => PROCAPTCHA_NONCE,
					'auto'    => false,
					'force'   => false,
					'size'    => '',
					'id'      => [],
					'protect' => true,
				],
			],
			'auto truly'  => [
				[
					'auto' => '1',
				],
				[
					'action'  => PROCAPTCHA_ACTION,
					'name'    => PROCAPTCHA_NONCE,
					'auto'    => '1',
					'force'   => false,
					'size'    => '',
					'id'      => [],
					'protect' => true,
				],
			],
			'force truly' => [
				[
					'force' => true,
				],
				[
					'action'  => PROCAPTCHA_ACTION,
					'name'    => PROCAPTCHA_NONCE,
					'auto'    => false,
					'force'   => true,
					'size'    => '',
					'id'      => [],
					'protect' => true,
				],
			],
			'some atts'   => [
				[
					'some' => 'some attribute',
				],
				[
					'action'  => PROCAPTCHA_ACTION,
					'name'    => PROCAPTCHA_NONCE,
					'auto'    => false,
					'force'   => false,
					'size'    => '',
					'id'      => [],
					'protect' => true,
					'some'    => 'some attribute',
				],
			],
		];
	}

	/**
	 * Test procap_min_suffix().
	 *
	 * @return void
	 */
	public function test_procap_min_suffix() {
		FunctionMocker::replace(
			'defined',
			static function ( $constant_name ) use ( &$script_debug ) {
				if ( 'SCRIPT_DEBUG' === $constant_name ) {
					return $script_debug;
				}

				return false;
			}
		);

		FunctionMocker::replace(
			'constant',
			static function ( $name ) use ( &$script_debug ) {
				if ( 'SCRIPT_DEBUG' === $name ) {
					return $script_debug;
				}

				return false;
			}
		);

		$script_debug = false;

		self::assertSame( '.min', procap_min_suffix() );

		$script_debug = true;

		self::assertSame( '', procap_min_suffix() );
	}
}
