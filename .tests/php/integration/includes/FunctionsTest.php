<?php
/**
 * FunctionsTest class file.
 *
 * @package Procaptcha\Tests
 */

namespace Procaptcha\Tests\Integration\includes;

use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;

/**
 * Test functions file.
 *
 * @group functions
 */
class FunctionsTest extends ProcaptchaWPTestCase {

	/**
	 * Test procap_shortcode().
	 *
	 * @param string $action Action name for wp_nonce_field.
	 * @param string $name   Nonce name for wp_nonce_field.
	 * @param string $auto   Auto argument.
	 *
	 * @dataProvider dp_test_procap_shortcode
	 */
	public function test_procap_shortcode( string $action, string $name, string $auto ) {
		$filtered = ' filtered ';

		$form_action = empty( $action ) ? 'procaptcha_action' : $action;
		$form_name   = empty( $name ) ? 'procaptcha_nonce' : $name;
		$form_auto   = filter_var( $auto, FILTER_VALIDATE_BOOLEAN );

		$expected =
			$filtered .
			$this->get_procap_form(
				[
					'action' => $form_action,
					'name'   => $form_name,
					'auto'   => $form_auto,
				]
			);

		procaptcha()->init_hooks();

		add_filter(
			'procap_procaptcha_content',
			static function ( $procaptcha_content ) use ( $filtered ) {
				return $filtered . $procaptcha_content;
			}
		);

		$shortcode = '[procaptcha';

		$shortcode .= empty( $action ) ? '' : ' action="' . $action . '"';
		$shortcode .= empty( $name ) ? '' : ' name="' . $name . '"';
		$shortcode .= empty( $auto ) ? '' : ' auto="' . $auto . '"';

		$shortcode .= ']';

		self::assertSame( $expected, do_shortcode( $shortcode ) );
	}

	/**
	 * Data provider for test_procap_shortcode().
	 *
	 * @return array
	 */
	public function dp_test_procap_shortcode(): array {
		return [
			'no arguments'   => [ '', '', '' ],
			'action only'    => [ 'some_action', '', '' ],
			'name only'      => [ '', 'some_name', '' ],
			'with arguments' => [ 'some_action', 'some_name', '' ],
			'auto false'     => [ 'some_action', 'some_name', 'false' ],
			'auto 0'         => [ 'some_action', 'some_name', 'false' ],
			'auto wrong'     => [ 'some_action', 'some_name', 'false' ],
			'auto true'      => [ 'some_action', 'some_name', 'true' ],
			'auto 1'         => [ 'some_action', 'some_name', '1' ],
		];
	}
}
