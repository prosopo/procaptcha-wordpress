<?php
/**
 * EmailOptinTest class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\Tests\Integration\Divi;

use Procaptcha\Divi\EmailOptin;
use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;
use tad\FunctionMocker\FunctionMocker;

/**
 * Class EmailOptinTest
 *
 * @group divi
 */
class EmailOptinTest extends ProcaptchaWPTestCase {

	/**
	 * Tear down test.
	 */
	public function tearDown(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		wp_dequeue_script( EmailOptin::HANDLE );

		parent::tearDown();
	}

	/**
	 * Test constructor and init_hooks().
	 */
	public function test_constructor_and_init_hooks() {
		$subject = new EmailOptin();

		self::assertSame( 10, has_filter( 'et_pb_signup_form_field_html_submit_button', [ $subject, 'add_captcha' ] ) );
		self::assertSame( 9, has_action( 'wp_ajax_et_pb_submit_subscribe_form', [ $subject, 'verify' ] ) );
		self::assertSame( 9, has_action( 'wp_ajax_nopriv_et_pb_submit_subscribe_form', [ $subject, 'verify' ] ) );
		self::assertSame( 9, has_action( 'wp_print_footer_scripts', [ $subject, 'enqueue_scripts' ] ) );
	}

	/**
	 * Test add_captcha().
	 */
	public function test_add_captcha() {
		$wrap              = '<p class="et_pb_newsletter_button_wrap">';
		$html              = <<<HTML
<form>
	$wrap
</form>
HTML;
		$procap_form         = $this->get_procap_form(
			[
				'action' => EmailOptin::ACTION,
				'name'   => EmailOptin::NONCE,
				'id'     => [
					'source'  => [ 'Divi' ],
					'form_id' => 'email_optin',
				],
			]
		);
		$expected          = str_replace( $wrap, $procap_form . "\n" . $wrap, $html );
		$single_name_field = 'some';

		FunctionMocker::replace( 'et_core_is_fb_enabled', false );

		$subject = new EmailOptin();

		self::assertSame( $expected, $subject->add_captcha( $html, $single_name_field ) );
	}

	/**
	 * Test verify().
	 *
	 * @return void
	 */
	public function test_verify() {
		$this->prepare_procaptcha_get_verify_message_html( EmailOptin::NONCE, EmailOptin::ACTION );

		$subject = new EmailOptin();

		$subject->verify();
	}

	/**
	 * Test verify() when not verified.
	 *
	 * @return void
	 */
	public function test_verify_not_verified() {
		$error_message = '<strong>procap_ error:</strong> The procap_ is invalid.';

		$et_core_die = FunctionMocker::replace( 'et_core_die' );

		$this->prepare_procaptcha_get_verify_message_html( EmailOptin::NONCE, EmailOptin::ACTION, false );

		$subject = new EmailOptin();

		$subject->verify();

		$et_core_die->wasCalledWithOnce( [ esc_html( $error_message ) ] );
	}

	/**
	 * Test enqueue_scripts().
	 *
	 * @return void
	 */
	public function test_enqueue_scripts() {
		procaptcha()->form_shown = true;

		self::assertFalse( wp_script_is( EmailOptin::HANDLE ) );

		$subject = new EmailOptin();

		$subject->enqueue_scripts();

		self::assertTrue( wp_script_is( EmailOptin::HANDLE ) );
	}

	/**
	 * Test enqueue_scripts() when form was not shown.
	 *
	 * @return void
	 */
	public function test_enqueue_scripts_when_form_was_not_shown() {
		self::assertFalse( wp_script_is( EmailOptin::HANDLE ) );

		$subject = new EmailOptin();

		$subject->enqueue_scripts();

		self::assertFalse( wp_script_is( EmailOptin::HANDLE ) );
	}
}
