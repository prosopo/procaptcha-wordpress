<?php
/**
 * JetpackBaseTest class file.
 *
 * @package Procaptcha\Tests
 */

namespace Procaptcha\Tests\Integration\Jetpack;

use Procaptcha\Jetpack\JetpackForm;
use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;
use ReflectionException;
use WP_Error;

/**
 * Class JetpackBaseTest.
 *
 * @group jetpack
 */
class JetpackBaseTest extends ProcaptchaWPTestCase {

	/**
	 * Test constructor and init_hooks.
	 */
	public function test_init_hooks() {
		$subject = new JetpackForm();

		self::assertSame(
			10,
			has_filter( 'the_content', [ $subject, 'add_captcha' ] )
		);
		self::assertSame(
			0,
			has_filter( 'widget_text', [ $subject, 'add_captcha' ] )
		);

		self::assertSame(
			10,
			has_filter( 'widget_text', 'shortcode_unautop' )
		);
		self::assertSame(
			10,
			has_filter( 'widget_text', 'do_shortcode' )
		);

		self::assertSame(
			100,
			has_filter( 'jetpack_contact_form_is_spam', [ $subject, 'verify' ] )
		);
	}

	/**
	 * Test jetpack_verify().
	 */
	public function test_jetpack_verify() {
		$this->prepare_procaptcha_get_verify_message( 'procaptcha_jetpack_nonce', 'procaptcha_jetpack' );

		$subject = new JetpackForm();

		self::assertFalse( $subject->verify() );
		self::assertTrue( $subject->verify( true ) );
	}

	/**
	 * Test jetpack_verify() not verified.
	 */
	public function test_jetpack_verify_not_verified() {
		$error = new WP_Error( 'invalid_procaptcha', 'The procap_ is invalid.' );

		$this->prepare_procaptcha_get_verify_message( 'procaptcha_jetpack_nonce', 'procaptcha_jetpack', false );

		$subject = new JetpackForm();

		self::assertEquals( $error, $subject->verify() );
		self::assertSame( 10, has_action( 'procap_procaptcha_content', [ $subject, 'error_message' ] ) );
	}

	/**
	 * Test error_message().
	 *
	 * @return void
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_error_message() {
		$procaptcha_content = 'some content';
		$error_message    = 'some error message';

		$subject = new JetpackForm();

		self::assertSame( $procaptcha_content, $subject->error_message( $procaptcha_content ) );

		$this->set_protected_property( $subject, 'error_message', $error_message );

		$expected = $procaptcha_content . '<div class="contact-form__input-error">
	<span class="contact-form__warning-icon">
		<span class="visually-hidden">Warning.</span>
		<i aria-hidden="true"></i>
	</span>
	<span>' . $error_message . '</span>
</div>';

		self::assertSame( $expected, $subject->error_message( $procaptcha_content ) );
	}
}
