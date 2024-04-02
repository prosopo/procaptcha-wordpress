<?php
/**
 * RegisterTest class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\Tests\Integration\BuddyPress;

use Procaptcha\BuddyPress\Register;
use Procaptcha\Tests\Integration\ProcaptchaPluginWPTestCase;

/**
 * Test Register.
 *
 * @group bp
 */
class RegisterTest extends ProcaptchaPluginWPTestCase {

	/**
	 * Plugin relative path.
	 *
	 * @var string
	 */
	protected static $plugin = 'buddypress/bp-loader.php';

	/**
	 * Tear down the test.
	 */
	public function tearDown(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		global $bp;

		unset( $bp->signup );

		parent::tearDown();
	}

	/**
	 * Test add_captcha().
	 */
	public function test_add_captcha() {
		$args     = [
			'action' => 'procaptcha_bp_register',
			'name'   => 'procaptcha_bp_register_nonce',
			'id'     => [
				'source'  => 'buddypress/bp-loader.php',
				'form_id' => 'register',
			],
		];
		$expected = $this->get_procaptchaform( $args );

		$subject = new Register();

		ob_start();

		$subject->add_captcha();

		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Test add_captcha() with error.
	 */
	public function test_register_error() {
		global $bp;

		$args                     = [
			'action' => 'procaptcha_bp_register',
			'name'   => 'procaptcha_bp_register_nonce',
			'id'     => [
				'source'  => 'buddypress/bp-loader.php',
				'form_id' => 'register',
			],
		];
		$procaptcha_response_verify = 'some response';

		$bp->signup = (object) [
			'errors' => [
				'procaptcha_response_verify' => $procaptcha_response_verify,
			],
		];

		$expected =
			'<div class="error">' .
			$procaptcha_response_verify .
			'</div>' .
			$this->get_procaptchaform( $args );
		$subject  = new Register();

		ob_start();

		$subject->add_captcha();

		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Test verify().
	 */
	public function test_verify() {
		$this->prepare_procaptcha_get_verify_message( 'procaptcha_bp_register_nonce', 'procaptcha_bp_register' );

		$subject = new Register();

		self::assertTrue( $subject->verify() );
	}

	/**
	 * Test verify() not verified.
	 */
	public function test_verify_not_verified() {
		global $bp;

		$bp->signup = (object) [
			'errors' => [],
		];
		$expected   = (object) [
			'errors' => [
				'procaptcha_response_verify' => 'Please complete the procaptcha.',
			],
		];
		$subject    = new Register();

		self::assertFalse( $subject->verify() );

		self::assertEquals( $expected, $bp->signup );
	}
}
