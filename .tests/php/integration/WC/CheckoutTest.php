<?php
/**
 * CheckoutTest class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\Tests\Integration\WC;

use Procaptcha\Tests\Integration\ProcaptchaPluginWPTestCase;
use Procaptcha\WC\Checkout;

/**
 * Test Checkout class.
 *
 * WooCommerce requires PHP 7.4.
 *
 * @requires PHP >= 7.4
 *
 * @group    wc-checkout
 * @group    wc
 */
class CheckoutTest extends ProcaptchaPluginWPTestCase {

	/**
	 * Plugin relative path.
	 *
	 * @var string
	 */
	protected static $plugin = 'woocommerce/woocommerce.php';

	/**
	 * Test tear down.
	 *
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function tearDown(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		if ( did_action( 'woocommerce_init' ) ) {
			wc_clear_notices();
		}

		wp_dequeue_script( 'procaptcha-wc-checkout' );

		parent::tearDown();
	}

	/**
	 * Test constructor and init_hooks().
	 */
	public function test_constructor_and_init_hooks() {
		$subject = new Checkout();

		self::assertSame(
			10,
			has_action( 'woocommerce_review_order_before_submit', [ $subject, 'add_captcha' ] )
		);
		self::assertSame(
			10,
			has_action( 'woocommerce_checkout_process', [ $subject, 'verify' ] )
		);
		self::assertSame(
			9,
			has_action( 'wp_print_footer_scripts', [ $subject, 'enqueue_scripts' ] )
		);
	}

	/**
	 * Tests add_captcha().
	 */
	public function test_add_captcha() {
		$args     = [
			'action' => 'procaptcha_wc_checkout',
			'name'   => 'procaptcha_wc_checkout_nonce',
			'id'     => [
				'source'  => [ 'woocommerce/woocommerce.php' ],
				'form_id' => 'checkout',
			],
		];
		$expected = $this->get_procaptchaform( $args );

		$subject = new Checkout();

		ob_start();

		$subject->add_captcha();

		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Test verify().
	 *
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function test_verify() {
		$this->prepare_procaptcha_get_verify_message( 'procaptcha_wc_checkout_nonce', 'procaptcha_wc_checkout' );

		WC()->init();
		wc_clear_notices();

		$subject = new Checkout();
		$subject->verify();

		self::assertSame( [], wc_get_notices() );
	}

	/**
	 * Test verify() not verified.
	 *
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function test_verify_not_verified() {
		$expected = [
			'error' => [
				[
					'notice' => 'The procaptcha is invalid.',
					'data'   => [],
				],
			],
		];

		$this->prepare_procaptcha_get_verify_message( 'procaptcha_wc_checkout_nonce', 'procaptcha_wc_checkout', false );

		WC()->init();
		wc_clear_notices();

		$subject = new Checkout();
		$subject->verify();

		self::assertSame( $expected, wc_get_notices() );
	}

	/**
	 * Test enqueue_scripts().
	 */
	public function test_enqueue_scripts() {
		$subject = new Checkout();

		self::assertFalse( wp_script_is( 'procaptcha-wc-checkout' ) );

		ob_start();
		$subject->add_captcha();
		ob_end_clean();

		$subject->enqueue_scripts();

		self::assertTrue( wp_script_is( 'procaptcha-wc-checkout' ) );
	}

	/**
	 * Test enqueue_scripts() when captcha was NOT added.
	 */
	public function test_enqueue_scripts_when_captcha_was_NOT_added() {
		$subject = new Checkout();

		self::assertFalse( wp_script_is( 'procaptcha-wc-checkout' ) );

		$subject->enqueue_scripts();

		self::assertFalse( wp_script_is( 'procaptcha-wc-checkout' ) );
	}
}
