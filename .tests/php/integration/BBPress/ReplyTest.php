<?php
/**
 * ReplyTest class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\Tests\Integration\BBPress;

use Procaptcha\BBPress\Reply;
use Procaptcha\Tests\Integration\ProcaptchaPluginWPTestCase;
use WP_Error;

/**
 * Test Reply class.
 *
 * @group bbpress
 */
class ReplyTest extends ProcaptchaPluginWPTestCase {

	/**
	 * Plugin relative path.
	 *
	 * @var string
	 */
	protected static $plugin = 'bbpress/bbpress.php';

	/**
	 * Tear down test.
	 *
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function tearDown(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		unset( $_POST );
		bbpress()->errors = new WP_Error();

		parent::tearDown();
	}

	/**
	 * Test add_captcha().
	 */
	public function test_add_captcha() {
		$args = [
			'action' => 'procaptcha_bbp_reply',
			'name'   => 'procaptcha_bbp_reply_nonce',
			'id'     => [
				'source'  => [ 'bbpress/bbpress.php' ],
				'form_id' => 'reply',
			],
		];

		$expected = $this->get_procaptchaform( $args );
		$subject  = new Reply();

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
		$this->prepare_procaptcha_get_verify_message( 'procaptcha_bbp_reply_nonce', 'procaptcha_bbp_reply' );

		$expected = new WP_Error();
		$subject  = new Reply();

		self::assertTrue( $subject->verify() );

		self::assertEquals( $expected, bbpress()->errors );
	}

	/**
	 * Test verify() when not verified.
	 *
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function test_verify_not_verified() {
		$expected = new WP_Error( 'procaptchaerror', 'Please complete the procaptcha.' );
		$subject  = new Reply();

		self::assertFalse( $subject->verify() );

		self::assertEquals( $expected, bbpress()->errors );
	}
}
