<?php
/**
 * FormTest class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */

namespace Procaptcha\Tests\Integration\Mailchimp;

use Procaptcha\Mailchimp\Form;
use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;
use MC4WP_Form;
use MC4WP_Form_Element;
use Mockery;

/**
 * Test Form class.
 */
class FormTest extends ProcaptchaWPTestCase {

	/**
	 * Test add_procap_error_messages().
	 */
	public function test_add_procap_error_messages() {
		$form = Mockery::mock( MC4WP_Form::class );

		$messages = [
			'foo' => [
				'type' => 'notice',
				'text' => 'bar',
			],
		];

		$procap_errors = [
			'missing-input-secret'             => [
				'type' => 'error',
				'text' => 'Your secret key is missing.',
			],
			'invalid-input-secret'             => [
				'type' => 'error',
				'text' => 'Your secret key is invalid or malformed.',
			],
			'missing-input-response'           => [
				'type' => 'error',
				'text' => 'The response parameter (verification token) is missing.',
			],
			'invalid-input-response'           => [
				'type' => 'error',
				'text' => 'The response parameter (verification token) is invalid or malformed.',
			],
			'bad-request'                      => [
				'type' => 'error',
				'text' => 'The request is invalid or malformed.',
			],
			'invalid-or-already-seen-response' => [
				'type' => 'error',
				'text' => 'The response parameter has already been checked, or has another issue.',
			],
			'not-using-dummy-passcode'         => [
				'type' => 'error',
				'text' => 'You have used a testing sitekey but have not used its matching secret.',
			],
			'sitekey-secret-mismatch'          => [
				'type' => 'error',
				'text' => 'The sitekey is not registered with the provided secret.',
			],
			'empty'                            => [
				'type' => 'error',
				'text' => 'Please complete the procap_.',
			],
			'fail'                             => [
				'type' => 'error',
				'text' => 'The procap_ is invalid.',
			],
			'bad-nonce'                        => [
				'type' => 'error',
				'text' => 'Bad procap_ nonce!',
			],
			'bad-signature'                    => [
				'type' => 'error',
				'text' => 'Bad procap_ signature!',
			],
		];

		$expected = array_merge( $messages, $procap_errors );
		$subject  = new Form();

		self::assertSame( $expected, $subject->add_procap_error_messages( $messages, $form ) );
	}

	/**
	 * Test add_captcha().
	 */
	public function test_add_captcha() {
		$form_id  = 5;
		$content  = '<input type="submit">';
		$args     = [
			'action' => 'procaptcha_mailchimp',
			'name'   => 'procaptcha_mailchimp_nonce',
			'id'     => [
				'source'  => [ 'mailchimp-for-wp/mailchimp-for-wp.php' ],
				'form_id' => $form_id,
			],
		];
		$expected = $this->get_procap_form( $args ) . $content;

		$mc4wp_form     = Mockery::mock( MC4WP_Form::class );
		$mc4wp_form->ID = $form_id;

		$element = Mockery::mock( MC4WP_Form_Element::class );

		$subject = new Form();

		self::assertSame( $expected, $subject->add_captcha( $content, $mc4wp_form, $element ) );
	}

	/**
	 * Test verify().
	 */
	public function test_verify() {
		$this->prepare_procaptcha_verify_post( 'procaptcha_mailchimp_nonce', 'procaptcha_mailchimp' );

		$mc4wp_form = Mockery::mock( MC4WP_Form::class );

		$subject = new Form();

		self::assertSame( [], $subject->verify( [], $mc4wp_form ) );
	}

	/**
	 * Test verify() not verified.
	 */
	public function test_verify_not_verified() {
		$this->prepare_procaptcha_verify_post( 'procaptcha_mailchimp_nonce', 'procaptcha_mailchimp', false );

		$mc4wp_form = Mockery::mock( MC4WP_Form::class );

		$subject = new Form();

		self::assertSame( [ 'fail' ], $subject->verify( [], $mc4wp_form ) );
	}
}
