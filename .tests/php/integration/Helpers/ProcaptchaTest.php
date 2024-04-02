<?php
/**
 * ProcaptchaTest class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\Tests\Integration\Helpers;

use Procaptcha\Helpers\Procaptcha;
use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;
use tad\FunctionMocker\FunctionMocker;

/**
 * Test Procaptcha class.
 *
 * @group helpers
 * @group helpers-procaptcha
 */
class ProcaptchaTest extends ProcaptchaWPTestCase {

	/**
	 * Tear down test.
	 */
	public function tearDown(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		unset( $_POST[ Procaptcha::HCAPTCHA_WIDGET_ID ] );

		procaptcha()->form_shown = false;

		parent::tearDown();
	}

	/**
	 * Test Procaptcha::form().
	 *
	 * @noinspection PhpConditionAlreadyCheckedInspection
	 */
	public function test_form() {
		procaptcha()->init_hooks();

		self::assertSame( $this->get_procap_form(), Procaptcha::form() );

		$action = 'some_action';
		$name   = 'some_name';
		$auto   = true;
		$args   = [
			'action' => $action,
			'name'   => $name,
			'auto'   => $auto,
		];

		self::assertSame(
			$this->get_procap_form(
				[
					'action' => $action,
					'name'   => $name,
					'auto'   => $auto,
				]
			),
			Procaptcha::form( $args )
		);
	}

	/**
	 * Test Procaptcha::form_display().
	 *
	 * @noinspection PhpConditionAlreadyCheckedInspection
	 */
	public function test_form_display() {
		self::assertFalse( procaptcha()->form_shown );

		ob_start();
		Procaptcha::form_display();
		self::assertSame( $this->get_procap_form(), ob_get_clean() );
		self::assertTrue( procaptcha()->form_shown );

		$action = 'some_action';
		$name   = 'some_name';
		$auto   = true;
		$args   = [
			'action' => $action,
			'name'   => $name,
			'auto'   => $auto,
		];

		ob_start();
		Procaptcha::form_display( $args );
		self::assertSame(
			$this->get_procap_form(
				[
					'action' => $action,
					'name'   => $name,
					'auto'   => $auto,
				]
			),
			ob_get_clean()
		);

		update_option( 'procaptcha_settings', [ 'size' => 'invisible' ] );

		procaptcha()->init_hooks();

		ob_start();
		Procaptcha::form_display( $args );
		self::assertSame(
			$this->get_procap_form(
				[
					'action' => $action,
					'name'   => $name,
					'auto'   => $auto,
					'size'   => 'invisible',
				]
			),
			ob_get_clean()
		);
	}

	/**
	 * Test check_signature().
	 *
	 * @return void
	 */
	public function test_check_signature() {
		$const      = Procaptcha::HCAPTCHA_SIGNATURE;
		$class_name = 'SomeClass';
		$form_id    = 'some_id';

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$name = $const . '-' . base64_encode( $class_name );

		// False when no signature.
		self::assertFalse( Procaptcha::check_signature( $class_name, $form_id ) );

		$_POST[ $name ] = $this->get_encoded_signature( [], $form_id, true );

		// False when wrong form_id.
		self::assertFalse( Procaptcha::check_signature( $class_name, 'wrong_form_id' ) );

		// Null when procap_ shown.
		self::assertNull( Procaptcha::check_signature( $class_name, $form_id ) );

		$_POST[ $name ] = $this->get_encoded_signature( [], $form_id, false );

		// True when procap_ not shown.
		self::assertTrue( Procaptcha::check_signature( $class_name, $form_id ) );

		unset( $_POST[ $name ] );
	}

	/**
	 * Test get_widget_id().
	 *
	 * @return void
	 */
	public function test_get_widget_id() {
		$default_id = [
			'source'  => [],
			'form_id' => 0,
		];
		$id         = [
			'source' => [ 'some source' ],
		];
		$expected   = [
			'source'  => [ 'some source' ],
			'form_id' => 0,
		];
		$hash       = 'some hash';

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.json_encode_json_encode
		$encoded_id = base64_encode( json_encode( $id ) );

		self::assertSame( $default_id, Procaptcha::get_widget_id() );

		$_POST[ Procaptcha::HCAPTCHA_WIDGET_ID ] = $encoded_id . '-' . $hash;

		self::assertSame( $expected, Procaptcha::get_widget_id() );
	}

	/**
	 * Test get_procaptcha_plugin_notice().
	 *
	 * @return void
	 */
	public function test_get_procaptcha_plugin_notice() {
		$expected = [
			'label'       => 'procap_ plugin is active',
			'description' => 'When procap_ plugin is active and integration is on, procap_ settings must be modified on the <a href="http://test.test/wp-admin/options-general.php?page=procaptcha&#038;tab=general" target="_blank">General settings page</a>.',
		];

		self::assertSame( $expected, Procaptcha::get_procaptcha_plugin_notice() );
	}

	/**
	 * Test js_display().
	 *
	 * @return void
	 */
	public function test_js_display() {
		$js       = <<<JS
	var a = 1;
	console.log( a );
JS;
		$expected = "var a=1;console.log(a)\n";

		// Not wrapped.
		ob_start();
		Procaptcha::js_display( $js, false );
		self::assertSame( $expected, ob_get_clean() );

		$expected_wrapped = "<script>\n" . $expected . "</script>\n";

		// Wrapped.
		ob_start();
		Procaptcha::js_display( $js, true );
		self::assertSame( $expected_wrapped, ob_get_clean() );

		// Not minified.
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

		ob_start();
		Procaptcha::js_display( $js, false );
		self::assertSame( $js . "\n", ob_get_clean() );
	}

	/**
	 * Test get_procap_locale().
	 *
	 * @param string $locale   Locale.
	 * @param string $expected Expected value.
	 *
	 * @return void
	 * @dataProvider dp_test_get_procap_locale
	 */
	public function test_get_procap_locale( string $locale, string $expected ) {
		add_filter(
			'locale',
			static function () use ( $locale ) {
				return $locale;
			}
		);

		self::assertSame( $expected, Procaptcha::get_procap_locale() );
	}

	/**
	 * Data provider for test_get_procap_locale().
	 *
	 * @return array
	 */
	public function dp_test_get_procap_locale(): array {
		return [
			[ 'en', 'en' ],
			[ 'en_US', 'en' ],
			[ 'en_UK', 'en' ],
			[ 'zh_CN', 'zh-CN' ],
			[ 'zh_SG', 'zh' ],
			[ 'bal', 'ca' ],
			[ 'hau', 'ha' ],
			[ 'some', '' ],
		];
	}
}
