<?php
/**
 * CommentFormTest class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\Tests\Integration\WP;

use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;
use Procaptcha\WP\Comment;
use Mockery;
use ReflectionException;
use WP_Error;

/**
 * Test comment form file.
 *
 * @group wp-comment
 * @group wp
 */
class CommentTest extends ProcaptchaWPTestCase {

	/**
	 * Tear down test.
	 */
	public function tearDown(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		unset( $GLOBALS['current_screen'] );

		parent::tearDown();
	}

	/**
	 * Test constructor and init_hooks().
	 *
	 * @param bool $active Active flag.
	 *
	 * @dataProvider dp_test_constructor_and_init_hooks
	 */
	public function test_constructor_and_init_hooks( bool $active ) {
		if ( $active ) {
			update_option( 'procaptcha_settings', [ 'wp_status' => 'comment' ] );
		}

		procaptcha()->init_hooks();

		$subject = new Comment();

		self::assertSame( 10, has_filter( 'comment_form_submit_field', [ $subject, 'add_captcha' ] ) );
		self::assertSame( - PHP_INT_MAX, has_filter( 'preprocess_comment', [ $subject, 'verify' ] ) );
		self::assertSame( 20, has_filter( 'pre_comment_approved', [ $subject, 'pre_comment_approved' ] ) );
	}

	/**
	 * Data provider for test_constructor_and_init_hooks().
	 *
	 * @return array
	 */
	public function dp_test_constructor_and_init_hooks(): array {
		return [
			[ true ],
			[ false ],
		];
	}

	/**
	 * Test add_captcha().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_add_captcha() {
		$form_id      = '1';
		$submit_field =
			'<p class="form-submit"><input name="submit" type="submit" id="submit" class="submit et_pb_button" value="Submit Comment" />' .
			"<input type='hidden' name='comment_post_ID' value='$form_id' id='comment_post_ID' />" .
			"<input type='hidden' name='comment_parent' id='comment_parent' value='0' />" .
			'</p>';

		$expected =
			$this->get_procap_form(
				[
					'action' => 'procaptcha_comment',
					'name'   => 'procaptcha_comment_nonce',
					'id'     => [
						'source'  => [ 'WordPress' ],
						'form_id' => $form_id,
					],
				]
			) .
			$submit_field;

		$subject = Mockery::mock( Comment::class )->makePartial();
		$this->set_protected_property( $subject, 'active', true );

		// Test when procap_ plugin is active.
		self::assertSame( $expected, $subject->add_captcha( $submit_field, [] ) );
	}

	/**
	 * Test add_captcha() when not active.
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_add_captcha_when_NOT_active() {
		$form_id      = '1';
		$submit_field =
			'<p class="form-submit"><input name="submit" type="submit" id="submit" class="submit et_pb_button" value="Submit Comment" />' .
			"<input type='hidden' name='comment_post_ID' value='$form_id' id='comment_post_ID' />" .
			"<input type='hidden' name='comment_parent' id='comment_parent' value='0' />" .
			'</p>';
		$procap_widget  = $this->get_procap_widget(
			[
				'source'  => [ 'WordPress' ],
				'form_id' => $form_id,
			]
		);
		$expected     = $procap_widget . '
		' . $submit_field;

		$subject = Mockery::mock( Comment::class )->makePartial();

		// Test when procap_ plugin is not active.
		$this->set_protected_property( $subject, 'active', false );

		self::assertSame( $expected, $subject->add_captcha( $submit_field, [] ) );
	}

	/**
	 * Test verify().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_verify() {
		$commentdata = [ 'some comment data' ];

		$this->prepare_procaptcha_get_verify_message_html( 'procaptcha_comment_nonce', 'procaptcha_comment' );

		$subject = new Comment();

		self::assertSame( $commentdata, $subject->verify( $commentdata ) );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		self::assertFalse( isset( $_POST['procaptcha-response'], $_POST['g-recaptcha-response'] ) );
		self::assertNull( $this->get_protected_property( $subject, 'result' ) );
	}

	/**
	 * Test verify() in admin.
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_verify_in_admin() {
		$commentdata = [ 'some comment data' ];

		set_current_screen( 'edit-post' );

		$subject = new Comment();

		self::assertSame( $commentdata, $subject->verify( $commentdata ) );
		self::assertNull( $this->get_protected_property( $subject, 'result' ) );
	}

	/**
	 * Test verify() not verified.
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_verify_not_verified() {
		$commentdata = [ 'some comment data' ];
		$expected    = '<strong>procap_ error:</strong> The procap_ is invalid.';

		$this->prepare_procaptcha_get_verify_message_html( 'procaptcha_comment_nonce', 'procaptcha_comment', false );

		$subject = new Comment();

		self::assertSame( $commentdata, $subject->verify( $commentdata ) );
		self::assertSame( $expected, $this->get_protected_property( $subject, 'result' ) );
	}

	/**
	 * Test pre_comment_approved().
	 *
	 * @return void
	 */
	public function test_pre_comment_approved() {
		$approved    = 1;
		$commentdata = [ 'some comment data' ];

		$subject = new Comment();

		self::assertSame( $approved, $subject->pre_comment_approved( $approved, $commentdata ) );
	}

	/**
	 * Test pre_comment_approved() when not verified.
	 *
	 * @return void
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_pre_comment_approved_when_not_verified() {
		$approved      = 1;
		$commentdata   = [ 'some comment data' ];
		$error_message = '<strong>procap_ error:</strong> The procap_ is invalid.';
		$expected      = new WP_Error();

		$expected->add( 'invalid_procaptcha', $error_message, 400 );

		$subject = new Comment();

		$this->set_protected_property( $subject, 'result', $error_message );

		self::assertEquals( $expected, $subject->pre_comment_approved( $approved, $commentdata ) );
	}

	/**
	 * Test verify().
	 */
	public function est_verify() {
		$approved    = 1;
		$commentdata = [ 'some comment data' ];

		$this->prepare_procaptcha_get_verify_message_html( 'procaptcha_comment_nonce', 'procaptcha_comment' );

		$subject = new Comment();

		self::assertSame( $approved, $subject->pre_comment_approved( $approved, $commentdata ) );
	}

	/**
	 * Test verify() not verified in admin.
	 */
	public function est_verify_not_verified_in_admin() {
		$approved    = 1;
		$commentdata = [ 'some comment data' ];

		set_current_screen( 'edit-post' );

		$subject = new Comment();

		self::assertSame( $approved, $subject->pre_comment_approved( $approved, $commentdata ) );
	}

	/**
	 * Test verify() do not need to verify, not in admin.
	 */
	public function est_verify_do_not_need_to_verify_not_admin() {
		$approved    = 1;
		$commentdata = [ 'some comment data' ];
		$expected    = new WP_Error( 'invalid_procaptcha', '<strong>procap_ error:</strong> Please complete the procap_.', 400 );

		$subject = new Comment();

		self::assertEquals( $expected, $subject->pre_comment_approved( $approved, $commentdata ) );
	}

	/**
	 * Test verify() not verified, not in admin.
	 */
	public function est_verify_not_verified_not_admin() {
		$approved    = 1;
		$commentdata = [ 'some comment data' ];
		$expected    = new WP_Error( 'invalid_procaptcha', '<strong>procap_ error:</strong> The procap_ is invalid.', 400 );

		$this->prepare_procaptcha_get_verify_message_html( 'procaptcha_comment_nonce', 'procaptcha_comment', false );

		$subject = new Comment();

		self::assertEquals( $expected, $subject->pre_comment_approved( $approved, $commentdata ) );
	}
}
