<?php
/**
 * IntegrationsTest class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\Tests\Unit\Settings;

use Procaptcha\Admin\Dialog;
use Procaptcha\Settings\PluginSettingsBase;
use KAGG\Settings\Abstracts\SettingsBase;
use Procaptcha\Settings\Integrations;
use Procaptcha\Tests\Unit\ProcaptchaTestCase;
use Mockery;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class IntegrationsTest
 *
 * @group settings
 * @group settings-integrations
 */
class IntegrationsTest extends ProcaptchaTestCase {

	/**
	 * Test screen_id().
	 */
	public function test_screen_id() {
		$subject = Mockery::mock( Integrations::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'settings_page_procaptcha', $subject->screen_id() );
	}

	/**
	 * Test option_group().
	 */
	public function test_option_group() {
		$subject = Mockery::mock( Integrations::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$method = 'option_group';
		self::assertSame( 'procaptcha_group', $subject->$method() );
	}

	/**
	 * Test option_page().
	 */
	public function test_option_page() {
		$subject = Mockery::mock( Integrations::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$method = 'option_page';
		self::assertSame( 'procaptcha', $subject->$method() );
	}

	/**
	 * Test option_name().
	 */
	public function test_option_name() {
		$subject = Mockery::mock( Integrations::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$method = 'option_name';
		self::assertSame( 'procaptcha_settings', $subject->$method() );
	}

	/**
	 * Test page_title().
	 */
	public function test_page_title() {
		$subject = Mockery::mock( Integrations::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$method = 'page_title';
		self::assertSame( 'Integrations', $subject->$method() );
	}

	/**
	 * Test menu_title().
	 */
	public function test_menu_title() {
		$subject = Mockery::mock( Integrations::class )->makePartial()->shouldAllowMockingProtectedMethods();

		FunctionMocker::replace(
			'constant',
			static function ( $name ) {
				if ( 'PROCAPTCHA_URL' === $name ) {
					return PROCAPTCHA_TEST_URL;
				}

				return '';
			}
		);

		$method   = 'menu_title';
		$expected = '<img class="kagg-settings-menu-image" src="https://site.org/wp-content/plugins/procaptcha-wordpress-plugin/assets/images/procaptcha-icon.svg" alt="procap_ icon"><span class="kagg-settings-menu-title">procap_</span>';

		self::assertSame( $expected, $subject->$method() );
	}

	/**
	 * Test section_title().
	 */
	public function test_section_title() {
		$subject = Mockery::mock( Integrations::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$method = 'section_title';
		self::assertSame( 'integrations', $subject->$method() );
	}

	/**
	 * Test init_hooks().
	 *
	 * @return void
	 */
	public function test_init_hooks() {
		$plugin_base_name = 'procaptcha-wordpress-plugin/procaptcha.php';

		$subject = Mockery::mock( Integrations::class )->makePartial();
		$subject->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'plugin_basename' )->andReturn( $plugin_base_name );

		WP_Mock::expectActionAdded( 'wp_ajax_' . Integrations::ACTIVATE_ACTION, [ $subject, 'activate' ] );

		$subject->init_hooks();
	}

	/**
	 * Test init_form_fields().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_init_form_fields() {
		$expected = $this->get_test_integrations_form_fields();

		$mock = Mockery::mock( Integrations::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$mock->init_form_fields();

		self::assertSame( $expected, $this->get_protected_property( $mock, 'form_fields' ) );
	}

	/**
	 * Test setup_fields().
	 *
	 * @return void
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_setup_fields() {
		$plugin_url  = 'http://test.test/wp-content/plugins/procaptcha-wordpress-plugin';
		$form_fields = $this->get_test_form_fields();

		foreach ( $form_fields as &$form_field ) {
			$form_field['disabled'] = true;
		}

		unset( $form_field );

		$form_fields['wp_status']['disabled'] = false;

		$subject = Mockery::mock( Integrations::class )->makePartial();
		$subject->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->andReturn( true );

		$this->set_protected_property( $subject, 'form_fields', $form_fields );

		WP_Mock::passthruFunction( 'register_setting' );
		WP_Mock::passthruFunction( 'add_settings_field' );
		WP_Mock::passthruFunction( 'sanitize_file_name' );

		FunctionMocker::replace(
			'constant',
			static function ( $name ) use ( $plugin_url ) {
				if ( 'PROCAPTCHA_URL' === $name ) {
					return $plugin_url;
				}

				return '';
			}
		);

		$subject->setup_fields();

		$form_fields = $this->get_protected_property( $subject, 'form_fields' );

		reset( $form_fields );
		$first_key = key( $form_fields );

		self::assertSame( 'wp_status', $first_key );

		foreach ( $form_fields as $form_field ) {
			$section = $form_field['disabled'] ? Integrations::SECTION_DISABLED : Integrations::SECTION_ENABLED;

			self::assertTrue( (bool) preg_match( '<img src="' . $plugin_url . '/assets/images/.+?" alt=".+?">', $form_field['label'] ) );
			self::assertArrayHasKey( 'class', $form_field );
			self::assertSame( $section, $form_field['section'] );
		}
	}

	/**
	 * Test setup_fields() not on options screen.
	 *
	 * @return void
	 */
	public function test_setup_fields_not_on_options_screen() {
		$subject = Mockery::mock( Integrations::class )->makePartial();
		$subject->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->andReturn( false );

		$subject->setup_fields();
	}

	/**
	 * Test section_callback()
	 *
	 * @param string $id       Section id.
	 * @param string $expected Expected value.
	 *
	 * @dataProvider dp_test_section_callback
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_section_callback( string $id, string $expected ) {
		WP_Mock::passthruFunction( 'wp_kses_post' );
		WP_Mock::userFunction( 'submit_button' );

		$subject = Mockery::mock( Integrations::class )->makePartial()->shouldAllowMockingProtectedMethods();

		ob_start();
		$subject->section_callback( [ 'id' => $id ] );
		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Data provider for test_section_callback().
	 *
	 * @return array
	 */
	public function dp_test_section_callback(): array {
		return [
			'disabled' => [
				Integrations::SECTION_DISABLED,
				'			<hr class="procaptcha-disabled-section">
			<h3>Inactive plugins and themes</h3>
			',
			],
			'default'  => [
				'',
				'		<h2>
			Integrations		</h2>
		<div id="procaptcha-message"></div>
		<p>
			Manage integrations with popular plugins such as Contact Form 7, WPForms, Gravity Forms, and more.		</p>
		<p>
			You can activate and deactivate a plugin by clicking on its logo.		</p>
		<p>
			Don\'t see your plugin here? Use the `[procaptcha]` <a href="https://wordpress.org/plugins/procaptcha-wordpress/#does%20the%20%5Bprocaptcha%5D%20shortcode%20have%20arguments%3F" target="_blank">shortcode</a> or <a href="https://github.com/procap_/procaptcha-wordpress-plugin/issues" target="_blank">request an integration</a>.		</p>
		<h3>Active plugins and themes</h3>
		',
			],
		];
	}

	/**
	 * Test admin_enqueue_scripts().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_admin_enqueue_scripts() {
		$plugin_url     = 'http://test.test/wp-content/plugins/procaptcha-wordpress-plugin';
		$plugin_version = '1.0.0';
		$min_prefix     = '.min';
		$ajax_url       = 'https://test.test/wp-admin/admin-ajax.php';
		$nonce          = 'some_nonce';

		$theme         = Mockery::mock( 'WP_Theme' );
		$default_theme = Mockery::mock( 'WP_Theme' );

		FunctionMocker::replace(
			'\WP_Theme::get_core_default_theme',
			static function () use ( $default_theme ) {
				return $default_theme;
			}
		);

		$theme->shouldReceive( 'get_stylesheet' )->andReturn( 'Divi' );
		$theme->shouldReceive( 'get' )->with( 'Name' )->andReturn( 'Divi' );
		$default_theme->shouldReceive( 'get_stylesheet' )->andReturn( 'twentytwentyone' );
		$default_theme->shouldReceive( 'get' )->andReturn( 'Twenty Twenty-One' );

		$themes = [
			'Divi'            => $theme,
			'twentytwentyone' => $default_theme,
		];

		$subject = Mockery::mock( Integrations::class )->makePartial();
		$subject->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->with()->andReturn( true );
		$this->set_protected_property( $subject, 'min_prefix', $min_prefix );

		FunctionMocker::replace(
			'constant',
			static function ( $name ) use ( $plugin_url, $plugin_version ) {
				if ( 'PROCAPTCHA_URL' === $name ) {
					return $plugin_url;
				}

				if ( 'PROCAPTCHA_VERSION' === $name ) {
					return $plugin_version;
				}

				return '';
			}
		);

		WP_Mock::userFunction( 'wp_enqueue_script' )
			->with(
				Integrations::DIALOG_HANDLE,
				$plugin_url . "/assets/js/kagg-dialog$min_prefix.js",
				[],
				$plugin_version,
				true
			)
			->once();

		WP_Mock::userFunction( 'wp_enqueue_style' )
			->with(
				Integrations::DIALOG_HANDLE,
				$plugin_url . "/assets/css/kagg-dialog$min_prefix.css",
				[],
				$plugin_version
			)
			->once();

		WP_Mock::userFunction( 'wp_enqueue_script' )
			->with(
				Integrations::HANDLE,
				$plugin_url . "/assets/js/integrations$min_prefix.js",
				[ 'jquery', Integrations::DIALOG_HANDLE ],
				$plugin_version,
				true
			)
			->once();

		WP_Mock::userFunction( 'wp_get_themes' )->with()->andReturn( $themes )->once();
		WP_Mock::userFunction( 'wp_get_theme' )->with()->andReturn( $theme )->once();

		WP_Mock::userFunction( 'admin_url' )
			->with( 'admin-ajax.php' )
			->andReturn( $ajax_url )
			->once();

		WP_Mock::userFunction( 'wp_create_nonce' )
			->with( Integrations::ACTIVATE_ACTION )
			->andReturn( $nonce )
			->once();

		WP_Mock::userFunction( 'wp_localize_script' )
			->with(
				Integrations::HANDLE,
				Integrations::OBJECT,
				[
					'ajaxUrl'            => $ajax_url,
					'action'             => Integrations::ACTIVATE_ACTION,
					'nonce'              => $nonce,
					'activateMsg'        => 'Activate %s plugin?',
					'deactivateMsg'      => 'Deactivate %s plugin?',
					'activateThemeMsg'   => 'Activate %s theme?',
					'deactivateThemeMsg' => 'Deactivate %s theme?',
					'selectThemeMsg'     => 'Select theme to activate:',
					'onlyOneThemeMsg'    => 'Cannot deactivate the only theme on the site.',
					'unexpectedErrorMsg' => 'Unexpected error.',
					'OKBtnText'          => 'OK',
					'CancelBtnText'      => 'Cancel',
					'themes'             => [ 'twentytwentyone' => 'Twenty Twenty-One' ],
					'defaultTheme'       => 'twentytwentyone',
				]
			)
			->once();

		WP_Mock::userFunction( 'wp_enqueue_style' )
			->with(
				Integrations::HANDLE,
				$plugin_url . "/assets/css/integrations$min_prefix.css",
				[ PluginSettingsBase::PREFIX . '-' . SettingsBase::HANDLE, Integrations::DIALOG_HANDLE ],
				$plugin_version
			)
			->once();

		$subject->admin_enqueue_scripts();
	}
}
