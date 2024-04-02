<?php
/**
 * NFTest class file.
 *
 * @package Procaptcha\Tests
 */

namespace Procaptcha\Tests\Integration\NF;

use Procaptcha\NF\Field;
use Procaptcha\NF\NF;
use Procaptcha\Tests\Integration\ProcaptchaPluginWPTestCase;
use tad\FunctionMocker\FunctionMocker;

/**
 * Test ninja-forms-procaptcha.php file.
 *
 * Ninja Forms requires PHP 7.2.
 *
 * @requires PHP >= 7.2
 * @requires PHP <= 8.2
 */
class NFTest extends ProcaptchaPluginWPTestCase {

	/**
	 * Plugin relative path.
	 *
	 * @var string
	 */
	protected static $plugin = 'ninja-forms/ninja-forms.php';

	/**
	 * Test init_hooks().
	 */
	public function test_init_hooks() {
		$subject = new NF();

		self::assertSame(
			10,
			has_filter( 'ninja_forms_register_fields', [ $subject, 'register_fields' ] )
		);
		self::assertSame(
			10,
			has_filter( 'ninja_forms_field_template_file_paths', [ $subject, 'template_file_paths' ] )
		);
		self::assertSame(
			10,
			has_filter( 'ninja_forms_localize_field_procaptcha-for-ninja-forms', [ $subject, 'localize_field' ] )
		);
		self::assertSame( 9, has_action( 'wp_print_footer_scripts', [ $subject, 'nf_captcha_script' ] ) );
	}

	/**
	 * Test register_fields.
	 */
	public function test_register_fields() {
		$fields = [ 'some field' ];

		$fields = ( new NF() )->register_fields( $fields );

		self::assertInstanceOf( Field::class, $fields['procaptcha-for-ninja-forms'] );
	}

	/**
	 * Test template_file_paths().
	 */
	public function test_template_file_paths() {
		$paths    = [ 'some path' ];
		$expected = array_merge( $paths, [ str_replace( '\\', '/', PROCAPTCHA_PATH . '/src/php/NF/templates/' ) ] );

		$paths = ( new NF() )->template_file_paths( $paths );
		array_walk(
			$paths,
			static function ( &$item ) {
				$item = str_replace( '\\', '/', $item );
			}
		);

		self::assertSame( $expected, $paths );
	}

	/**
	 * Test localize_field().
	 */
	public function test_localize_field() {
		$form_id  = 1;
		$field_id = 5;
		$field    = [
			'id'       => $field_id,
			'settings' => [],
		];

		$procaptcha_site_key = 'some key';
		$procaptcha_theme    = 'some theme';
		$procaptcha_size     = 'some size';
		$uniqid            = 'procaptcha-nf-625d3b9b318fc0.86180601';

		update_option(
			'procaptcha_settings',
			[
				'site_key' => $procaptcha_site_key,
				'theme'    => $procaptcha_theme,
				'size'     => $procaptcha_size,
			]
		);

		procaptcha()->init_hooks();

		FunctionMocker::replace(
			'uniqid',
			static function ( $prefix, $more_entropy ) use ( $uniqid ) {
				if ( 'procaptcha-nf-' === $prefix && $more_entropy ) {
					return $uniqid;
				}

				return null;
			}
		);

		$expected                         = $field;
		$procap_widget                      = $this->get_procap_widget(
			[
				'source'  => [ 'ninja-forms/ninja-forms.php' ],
				'form_id' => $form_id,
			]
		);
		$expected['settings']['procaptcha'] =
			$procap_widget . "\n" . '				<div id="' . $uniqid . '" data-fieldId="' . $field_id . '"
			class="procaptcha"
			data-sitekey="some key"
			data-theme="some theme"
			data-size="some size"
			data-auto="false"
			data-force="false">
		</div>
		';

		$subject = new NF();
		$subject->set_form_id( $form_id );

		self::assertSame( $expected, $subject->localize_field( $field ) );
	}

	/**
	 * Test nf_captcha_script().
	 */
	public function test_nf_captcha_script() {
		$subject = new NF();

		$subject->nf_captcha_script();

		self::assertTrue( wp_script_is( 'procaptcha-nf' ) );
	}
}
