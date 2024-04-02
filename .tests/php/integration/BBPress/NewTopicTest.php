<?php
/**
 * NewTopic class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\Tests\Integration\BBPress;

use Procaptcha\BBPress\NewTopic;
use Procaptcha\Tests\Integration\ProcaptchaPluginWPTestCase;
use WP_Error;

/**
 * Test NewTopic class.
 *
 * @group bbpress
 */
class NewTopicTest extends ProcaptchaPluginWPTestCase {

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
			'action' => 'procaptcha_bbp_new_topic',
			'name'   => 'procaptcha_bbp_new_topic_nonce',
			'id'     => [
				'source'  => [ 'bbpress/bbpress.php' ],
				'form_id' => 'new_topic',
			],
		];

		$expected = $this->get_procap_form( $args );

		$subject = new NewTopic();

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
		$this->prepare_procaptcha_get_verify_message( 'procaptcha_bbp_new_topic_nonce', 'procaptcha_bbp_new_topic' );

		$expected = new WP_Error();
		$subject  = new NewTopic();

		self::assertTrue( $subject->verify() );

		self::assertEquals( $expected, bbpress()->errors );
	}

	/**
	 * Test verify() when not verified.
	 *
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function test_verify_not_verified() {
		$expected = new WP_Error( 'procap_error', 'Please complete the procap_.' );
		$subject  = new NewTopic();

		self::assertFalse( $subject->verify() );

		self::assertEquals( $expected, bbpress()->errors );
	}
}
