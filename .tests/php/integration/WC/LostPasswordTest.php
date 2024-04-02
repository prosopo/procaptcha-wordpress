<?php
/**
 * LostPasswordTest class file.
 *
 * @package Procaptcha\Tests
 */

namespace Procaptcha\Tests\Integration\WC;

use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;
use Procaptcha\WC\LostPassword;
use tad\FunctionMocker\FunctionMocker;

/**
 * LostPasswordTest class.
 *
 * @group wc-lost-password
 * @group wc
 */
class LostPasswordTest extends ProcaptchaWPTestCase {

	/**
	 * Test constructor and init_hooks().
	 */
	public function test_constructor_and_init_hooks() {
		$subject = new LostPassword();

		self::assertSame(
			10,
			has_action( 'woocommerce_lostpassword_form', [ $subject, 'add_captcha' ] )
		);
	}

	/**
	 * Test add_captcha().
	 */
	public function test_add_captcha() {
		$args     = [
			'action' => 'procaptcha_wc_lost_password',
			'name'   => 'procaptcha_wc_lost_password_nonce',
			'id'     => [
				'source'  => [ 'woocommerce/woocommerce.php' ],
				'form_id' => 'lost_password',
			],
		];
		$expected = $this->get_procaptchaform( $args );

		$subject = new LostPassword();

		ob_start();

		$subject->add_captcha();

		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Test print_inline_styles().
	 *
	 * @return void
	 */
	public function test_print_inline_styles() {
		FunctionMocker::replace(
			'defined',
			static function ( $constant_name ) {
				return 'SCRIPT_DEBUG' === $constant_name;
			}
		);

		FunctionMocker::replace(
			'constant',
			static function ( $name ) {
				return 'SCRIPT_DEBUG' === $name;
			}
		);

		$expected = <<<CSS
	.woocommerce-ResetPassword .procaptcha {
		margin-top: 0.5rem;
	}
CSS;
		$expected = "<style>\n$expected\n</style>\n";

		$subject = new LostPassword();

		ob_start();

		$subject->print_inline_styles();

		self::assertSame( $expected, ob_get_clean() );
	}
}
