<?php
/**
 * CommentTest class file.
 *
 * @package Procaptcha\Tests
 */

namespace Procaptcha\Tests\Integration\Divi;

use Procaptcha\Divi\Comment;
use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;
use tad\FunctionMocker\FunctionMocker;

/**
 * Class CommentTest
 *
 * @group divi
 */
class CommentTest extends ProcaptchaWPTestCase {

	/**
	 * Test constructor and init_hooks().
	 */
	public function test_constructor_and_init_hooks() {
		$subject = new Comment();

		self::assertSame( 10, has_filter( Comment::TAG . '_shortcode_output', [ $subject, 'add_captcha' ] ) );
	}

	/**
	 * Test add_captcha().
	 */
	public function test_add_captcha() {
		$form_id     = '3075';
		$output      = <<<HTML
<form>
	<button name="submit" type="submit" id="et_pb_submit" class="submit">Submit Comment</button>
	<input type='hidden' name='comment_post_ID' value='$form_id' id='comment_post_ID' />
</form>
HTML;
		$procaptchaform   = $this->get_procaptchaform(
			[
				'action' => Comment::ACTION,
				'name'   => Comment::NONCE,
				'id'     => [
					'source'  => [ 'Divi' ],
					'form_id' => $form_id,
				],
			]
		);
		$expected    = str_replace( '<button', $procaptchaform . "\n<button", $output );
		$module_slug = 'et_pb_comments';

		FunctionMocker::replace( 'et_core_is_fb_enabled', false );

		$subject = new Comment();

		self::assertSame( $expected, $subject->add_captcha( $output, $module_slug ) );
	}

	/**
	 * Test add_captcha() when output is not a string.
	 */
	public function test_add_captcha_when_output_is_not_a_string() {
		$output      = [ 'some string' ];
		$module_slug = 'et_pb_comments';

		$subject = new Comment();

		self::assertSame( $output, $subject->add_captcha( $output, $module_slug ) );
	}

	/**
	 * Test add_captcha() when output has procaptcha.
	 */
	public function test_add_captcha_when_output_has_procaptcha() {
		$output      = 'some output with procaptcha attr';
		$module_slug = 'et_pb_comments';

		$subject = new Comment();

		self::assertSame( $output, $subject->add_captcha( $output, $module_slug ) );
	}

	/**
	 * Test add_captcha() in frontend builder.
	 */
	public function test_add_captcha_in_frontend_builder() {
		$output      = 'some string';
		$module_slug = 'et_pb_comments';

		FunctionMocker::replace( 'et_core_is_fb_enabled', true );

		$subject = new Comment();

		self::assertSame( $output, $subject->add_captcha( $output, $module_slug ) );
	}
}
