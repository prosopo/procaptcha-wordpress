<?php
/**
 * RegisterTest class file.
 *
 * @package Procaptcha\Tests
 */

namespace Procaptcha\Tests\Integration\UM;

use Procaptcha\Tests\Integration\ProcaptchaPluginWPTestCase;
use Procaptcha\UM\Register;

/**
 * Class RegisterTest.
 *
 * @group um-register
 * @group um
 */
class RegisterTest extends ProcaptchaPluginWPTestCase {

	/**
	 * Plugin relative path.
	 *
	 * @var string
	 */
	protected static $plugin = 'ultimate-member/ultimate-member.php';

	/**
	 * Tear down the test.
	 *
	 * @return void
	 * @noinspection PhpUndefinedFunctionInspection
	 * @noinspection PhpLanguageLevelInspection
	 * @noinspection PhpUndefinedClassInspection
	 */
	public function tearDown(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		UM()->form()->errors = null;

		parent::tearDown();
	}

	/**
	 * Test constructor and init_hooks().
	 */
	public function test_constructor_and_init_hooks() {
		$subject = $this->get_subject();

		self::assertSame(
			100,
			has_action( 'um_get_form_fields', [ $subject, 'add_um_captcha' ] )
		);
		self::assertSame(
			10,
			has_action( 'um_procaptcha_form_edit_field', [ $subject, 'display_captcha' ] )
		);
		self::assertSame(
			10,
			has_action( 'um_submit_form_errors_hook__registration', [ $subject, 'verify' ] )
		);
	}

	/**
	 * Get subject.
	 *
	 * @return Register
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	private function get_subject(): Register {
		$subject = new Register();

		UM()->fields()->set_mode = $subject::UM_MODE;

		return $subject;
	}
}
