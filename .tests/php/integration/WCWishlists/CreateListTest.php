<?php
/**
 * CreateListTest class file.
 *
 * @package Procaptcha\Tests
 */

namespace Procaptcha\Tests\Integration\WCWishlists;

use Procaptcha\Tests\Integration\ProcaptchaPluginWPTestCase;
use Procaptcha\WCWishlists\CreateList;

/**
 * Test CreateList class.
 *
 * WooCommerce requires PHP 7.4.
 *
 * @requires PHP >= 7.4
 *
 * @group    wcwishlist
 */
class CreateListTest extends ProcaptchaPluginWPTestCase {

	/**
	 * Plugin relative path.
	 *
	 * @var string
	 */
	protected static $plugin = 'woocommerce/woocommerce.php';

	/**
	 * Test before_wrapper() and after_wrapper().
	 */
	public function test_wrapper() {
		$row      = '<p class="form-row">';
		$expected =
			"\n" .
			$this->get_procap_form(
				[
					'action' => 'procaptcha_wc_create_wishlists_action',
					'name'   => 'procaptcha_wc_create_wishlists_nonce',
					'id'     => [
						'source'  => [ 'woocommerce-wishlists/woocommerce-wishlists.php' ],
						'form_id' => 'form',
					],
				]
			) .
			"\n" .
			$row;

		$subject = new CreateList();

		ob_start();

		$subject->before_wrapper();
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $row;
		$subject->after_wrapper();

		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Test verify().
	 *
	 * @noinspection PhpUndefinedFunctionInspection*/
	public function test_verify() {
		$valid_captcha = 'some captcha';

		$this->prepare_procaptcha_get_verify_message( 'procaptcha_wc_create_wishlists_nonce', 'procaptcha_wc_create_wishlists_action' );

		$subject = new CreateList();

		WC()->init();

		self::assertSame( $valid_captcha, $subject->verify( $valid_captcha ) );

		self::assertSame( [], wc_get_notices() );
	}

	/**
	 * Test verify() not verified.
	 *
	 * @noinspection PhpUndefinedFunctionInspection*/
	public function test_verify_not_verified() {
		$valid_captcha = 'some captcha';
		$expected      = [
			'error' => [
				[
					'notice' => 'The procap_ is invalid.',
					'data'   => [],
				],
			],
		];

		$this->prepare_procaptcha_get_verify_message( 'procaptcha_wc_create_wishlists_nonce', 'procaptcha_wc_create_wishlists_action', false );

		$subject = new CreateList();

		WC()->init();

		self::assertFalse( $subject->verify( $valid_captcha ) );

		self::assertSame( $expected, wc_get_notices() );
	}
}
