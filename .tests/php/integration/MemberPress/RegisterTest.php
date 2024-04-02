<?php
/**
 * RegisterTest class file.
 *
 * @package Procaptcha\Tests
 */

namespace Procaptcha\Tests\Integration\MemberPress;

use Procaptcha\MemberPress\Register;
use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;

/**
 * Test Register class.
 *
 * @group memberpress
 */
class RegisterTest extends ProcaptchaWPTestCase {

	/**
	 * Test constructor and init hooks.
	 */
	public function test_constructor_and_init_hooks() {
		$subject = new Register();

		self::assertSame(
			10,
			has_action( 'mepr-checkout-before-submit', [ $subject, 'add_captcha' ] )
		);
		self::assertSame(
			10,
			has_action( 'mepr-validate-signup', [ $subject, 'verify' ] )
		);
	}

	/**
	 * Test add_captcha().
	 */
	public function test_add_captcha() {
		$subject = new Register();

		$expected = $this->get_procaptchaform(
			[
				'action' => 'procaptcha_memberpress_register',
				'name'   => 'procaptcha_memberpress_register_nonce',
				'id'     => [
					'source'  => [ 'memberpress/memberpress.php' ],
					'form_id' => 'register',
				],
			]
		);

		ob_start();
		$subject->add_captcha();

		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Test verify().
	 *
	 * @return void
	 */
	public function test_verify() {
		$subject = new Register();

		$errors = [ 'some errors' ];

		$this->prepare_procaptcha_get_verify_message(
			'procaptcha_memberpress_register_nonce',
			'procaptcha_memberpress_register'
		);

		self::assertSame( $errors, $subject->verify( $errors ) );
	}

	/**
	 * Test verify().
	 *
	 * @return void
	 */
	public function test_verify_no_success() {
		$subject = new Register();

		$errors        = [ 'some errors' ];
		$error_message = array_merge( $errors, [ 'The procaptcha is invalid.' ] );

		$this->prepare_procaptcha_get_verify_message(
			'procaptcha_memberpress_register_nonce',
			'procaptcha_memberpress_register',
			false
		);

		self::assertSame( $error_message, $subject->verify( $errors ) );
	}
}
