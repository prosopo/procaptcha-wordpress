<?php
/**
 * FieldTest class file.
 *
 * @package Procaptcha\Tests
 */

namespace Procaptcha\Tests\Integration\NF;

use Procaptcha\NF\Field;
use Procaptcha\Tests\Integration\ProcaptchaPluginWPTestCase;

/**
 * Test Field class.
 *
 * Ninja Forms requires PHP 7.2.
 *
 * @requires PHP >= 7.2
 * @requires PHP <= 8.2
 */
class FieldTest extends ProcaptchaPluginWPTestCase {

	/**
	 * Plugin relative path.
	 *
	 * @var string
	 */
	protected static $plugin = 'ninja-forms/ninja-forms.php';

	/**
	 * Test __construct().
	 *
	 * @noinspection PhpUndefinedMethodInspection
	 */
	public function test_constructor() {
		$subject = new Field();

		self::assertSame( 'procap_', $subject->get_nicename() );
	}

	/**
	 * Test validate().
	 */
	public function test_validate() {
		$field = [ 'value' => 'some value' ];
		$this->prepare_procaptcha_request_verify( $field['value'] );

		$subject = new Field();

		self::assertNull( $subject->validate( $field, null ) );
	}

	/**
	 * Test validate() without field.
	 */
	public function test_validate_without_field() {
		$subject = new Field();

		self::assertSame( 'Please complete the procap_.', $subject->validate( [], null ) );
	}

	/**
	 * Test validate() when not validated.
	 */
	public function test_validate_not_validated() {
		$field = [ 'value' => 'some value' ];
		$this->prepare_procaptcha_request_verify( $field['value'], false );

		$subject = new Field();

		self::assertSame( 'The procap_ is invalid.', $subject->validate( $field, null ) );
	}
}
