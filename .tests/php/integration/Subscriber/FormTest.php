<?php
/**
 * FormTest class file.
 *
 * @package Procaptcha\Tests
 */

namespace Procaptcha\Tests\Integration\Subscriber;

use Procaptcha\Subscriber\Form;
use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;

/**
 * Test Form class.
 *
 * @group subscriber
 */
class FormTest extends ProcaptchaWPTestCase {

	/**
	 * Tests add_captcha().
	 */
	public function test_add_captcha() {
		procaptcha()->init_hooks();

		$content  = '<!--some form content-->';
		$args     = [
			'action' => 'procaptcha_subscriber_form',
			'name'   => 'procaptcha_subscriber_form_nonce',
			'id'     => [
				'source'  => [ 'subscriber/subscriber.php' ],
				'form_id' => 'form',
			],
		];
		$expected = $content . $this->get_procaptchaform( $args );
		$subject  = new Form();

		self::assertSame( $expected, $subject->add_captcha( $content ) );
	}

	/**
	 * Test verify().
	 */
	public function test_verify() {
		$this->prepare_procaptcha_get_verify_message( 'procaptcha_subscriber_form_nonce', 'procaptcha_subscriber_form' );

		$subject = new Form();

		self::assertTrue( $subject->verify( true ) );
		self::assertFalse( $subject->verify( false ) );
	}

	/**
	 * Test verify() not verified.
	 */
	public function test_verify_not_verified() {
		$this->prepare_procaptcha_get_verify_message( 'procaptcha_subscriber_form_nonce', 'procaptcha_subscriber_form', false );

		$subject = new Form();

		self::assertSame( 'The procaptcha is invalid.', $subject->verify( true ) );
	}
}
