<?php
/**
 * JetpackFormTest class file.
 *
 * @package Procaptcha\Tests
 */

namespace Procaptcha\Tests\Integration\Jetpack;

use Procaptcha\Jetpack\JetpackForm;
use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;

/**
 * Class JetpackFormTest.
 *
 * @group jetpack
 */
class JetpackFormTest extends ProcaptchaWPTestCase {

	/**
	 * Test add_captcha().
	 *
	 * @param string $content  Form content.
	 * @param string $expected Expected content.
	 *
	 * @dataProvider dp_test_add_captcha
	 */
	public function test_add_captcha( string $content, string $expected ) {
		$subject = new JetpackForm();

		self::assertSame( $expected, $subject->add_captcha( $content ) );
	}

	/**
	 * Data provider for test_add_captcha().
	 *
	 * @return array
	 * @noinspection HtmlUnknownAttribute
	 */
	public function dp_test_add_captcha(): array {
		$_SERVER['REQUEST_URI'] = 'http://test.test/';

		$args     = [
			'action' => 'procaptcha_jetpack',
			'name'   => 'procaptcha_jetpack_nonce',
			'force'  => true,
			'id'     => [
				'source'  => [ 'jetpack/jetpack.php' ],
				'form_id' => 'contact',
			],
		];
		$procaptcha = $this->get_procap_form( $args );

		return [
			'Empty contact form'                 => [ '', '' ],
			'Classic contact form'               => [
				'[contact-form] Some contact form [/contact-form]',
				'[contact-form] Some contact form <div class="grunion-field-wrap">' . $procaptcha . '</div>[/contact-form]',
			],
			'Classic contact form with procaptcha' => [
				'[contact-form] Some contact form [procaptcha][/contact-form]',
				'[contact-form] Some contact form [procaptcha][/contact-form]',
			],
			'Block contact form'                 => [
				'<form class="wp-block-jetpack-contact-form" <div class="wp-block-jetpack-button wp-block-button" <button type="submit">Contact Us</button></form>',
				'<form class="wp-block-jetpack-contact-form" <div class="grunion-field-wrap">' . $procaptcha . '</div><div class="wp-block-jetpack-button wp-block-button" <button type="submit">Contact Us</button></form>',
			],
			'Block contact form with procaptcha'   => [
				'<form class="wp-block-jetpack-contact-form" [procaptcha]<button type="submit">Contact Us</button></form>',
				'<form class="wp-block-jetpack-contact-form" [procaptcha]<button type="submit">Contact Us</button></form>',
			],
			'Block contact form and search form' => [
				'<form class="wp-block-jetpack-contact-form" <div class="wp-block-jetpack-button wp-block-button" <button type="submit">Contact Us</button></form>' .
				'<form class="search-form" <input type="submit" class="search-submit" value="Search"></form>',
				'<form class="wp-block-jetpack-contact-form" <div class="grunion-field-wrap">' . $procaptcha . '</div><div class="wp-block-jetpack-button wp-block-button" <button type="submit">Contact Us</button></form>' .
				'<form class="search-form" <input type="submit" class="search-submit" value="Search"></form>',
			],
		];
	}
}
