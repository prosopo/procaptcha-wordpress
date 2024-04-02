<?php
/**
 * ReplyTest class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\Tests\Integration\WPForo;

use Procaptcha\Tests\Integration\ProcaptchaPluginWPTestCase;
use Procaptcha\WPForo\Reply;
use tad\FunctionMocker\FunctionMocker;
use wpforo\classes\Notices;

/**
 * Test Reply class.
 *
 * @group wpforo
 * @requires PHP >= 7.1
 */
class ReplyTest extends ProcaptchaPluginWPTestCase {

	/**
	 * Plugin relative path.
	 *
	 * @var string
	 */
	protected static $plugin = 'wpforo/wpforo.php';

	/**
	 * Set up test.
	 *
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function setUp(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		set_current_screen( 'edit-post' );

		parent::setUp();

		WPF()->notice = new Notices();
	}

	/**
	 * Tear down test.
	 *
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function tearDown(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		WPF()->session_token = '';
		WPF()->notice->clear();
		WPF()->session_token = '';

		parent::tearDown();
	}

	/**
	 * Test add_captcha().
	 */
	public function test_add_captcha() {
		$topic_id = 21;
		$topic    = [
			'forumid'  => 2,
			'topicid'  => $topic_id,
			'some key' => 'some value',
		];
		$args     = [
			'action' => 'procaptcha_wpforo_reply',
			'name'   => 'procaptcha_wpforo_reply_nonce',
			'id'     => [
				'source'  => [ 'wpforo/wpforo.php' ],
				'form_id' => $topic_id,
			],
		];
		$expected = $this->get_procap_form( $args );

		new Reply();

		ob_start();

		do_action( Reply::ADD_CAPTCHA_HOOK, $topic );

		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Test verify().
	 *
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function test_verify() {
		$data    = [ 'some data' ];
		$subject = new Reply();

		$this->prepare_procaptcha_get_verify_message( 'procaptcha_wpforo_reply_nonce', 'procaptcha_wpforo_reply' );

		self::assertSame( '', WPF()->notice->get_notices() );
		self::assertSame( $data, $subject->verify( $data ) );
		self::assertSame( '', WPF()->notice->get_notices() );
	}

	/**
	 * Test verify() when not verified.
	 *
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function test_verify_not_verified() {
		$expected = '<p class="error">The procap_ is invalid.</p>';
		$subject  = new Reply();

		$this->prepare_procaptcha_get_verify_message( 'procaptcha_wpforo_reply_nonce', 'procaptcha_wpforo_reply', false );

		FunctionMocker::replace( 'wpforo_is_ajax', true );

		WPF()->session_token = '23';

		self::assertSame( '', WPF()->notice->get_notices() );
		self::assertFalse( $subject->verify( [] ) );

		WPF()->session_token = '';

		self::assertSame( $expected, WPF()->notice->get_notices() );
	}
}
