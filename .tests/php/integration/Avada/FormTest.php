<?php
/**
 * FormTest class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\Tests\Integration\Avada;

use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;
use Procaptcha\Avada\Form;

/**
 * Test FormTest class.
 *
 * @group avada
 */
class FormTest extends ProcaptchaWPTestCase {

	/**
	 * Tear down test.
	 *
	 * @return void
	 */
	public function tearDown(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		unset( $_POST['formData'], $_POST['procaptcha-widget-id'] );

		parent::tearDown();
	}

	/**
	 * Test init_hooks().
	 *
	 * @return void
	 */
	public function test_init_hooks() {
		$subject = new Form();

		self::assertSame( 10, has_action( 'fusion_form_after_open', [ $subject, 'form_after_open' ] ) );
		self::assertSame( 10, has_action( 'fusion_element_button_content', [ $subject, 'add_procaptcha' ] ) );
		self::assertSame( 10, has_filter( 'fusion_form_demo_mode', [ $subject, 'verify' ] ) );
	}

	/**
	 * Test add_procaptcha().
	 *
	 * @return void
	 */
	public function test_add_procaptcha() {
		$form_id    = 5;
		$args       = [
			'id' =>
				[
					'source'  => [ 'Avada' ],
					'form_id' => $form_id,
				],
		];
		$params     = [ 'id' => $form_id ];
		$wrong_html = 'some html';
		$html       = '<button type="submit">';
		$form       = $this->get_procaptchaform( $args );
		$expected   = $form . $html;

		$subject = new Form();

		$subject->form_after_open( $args, $params );
		self::assertSame( $wrong_html, $subject->add_procaptcha( $wrong_html, $args ) );
		self::assertSame( $expected, $subject->add_procaptcha( $html, $args ) );
	}

	/**
	 * Test verify().
	 *
	 * @return void
	 * @noinspection PhpConditionAlreadyCheckedInspection
	 */
	public function test_verify() {
		$demo_mode         = true;
		$procaptcha_response = 'some_response';
		$form_data         = "procaptcha-response=$procaptcha_response";

		$this->prepare_procaptcha_request_verify( $procaptcha_response );

		$_POST['formData'] = $form_data;

		$subject = new Form();

		self::assertSame( $demo_mode, $subject->verify( $demo_mode ) );
	}

	/**
	 * Test verify() when not verified.
	 *
	 * @return void
	 */
	public function test_verify_not_verified() {
		$procaptcha_response = 'some_response';
		$die_arr           = [];
		$expected          = [
			'{"status":"error","info":{"procaptcha":"Please complete the procaptcha."}}',
			'',
			[],
		];

		$this->prepare_procaptcha_request_verify( $procaptcha_response, false );

		$subject = new Form();

		add_filter(
			'wp_die_handler',
			static function ( $name ) use ( &$die_arr ) {
				return static function ( $message, $title, $args ) use ( &$die_arr ) {
					$die_arr = [ $message, $title, $args ];
				};
			}
		);

		$subject->verify( true );

		self::assertSame( $expected, $die_arr );
	}
}
