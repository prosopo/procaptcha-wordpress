<?php
/**
 * GeneralTest class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\Tests\Unit\Settings;

use Procaptcha\Admin\Notifications;
use Procaptcha\Main;
use Procaptcha\Settings\PluginSettingsBase;
use KAGG\Settings\Abstracts\SettingsBase;
use Procaptcha\Settings\General;
use Procaptcha\Settings\Settings;
use Procaptcha\Tests\Unit\ProcaptchaTestCase;
use Mockery;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class GeneralTest
 *
 * @group settings
 * @group settings-general
 */
class GeneralTest extends ProcaptchaTestCase {

	/**
	 * Test screen_id().
	 */
	public function test_screen_id() {
		$subject = Mockery::mock( General::class )->makePartial()->shouldAllowMockingProtectedMethods();

		self::assertSame( 'settings_page_procaptcha', $subject->screen_id() );
	}

	/**
	 * Test option_group().
	 */
	public function test_option_group() {
		$subject = Mockery::mock( General::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$method = 'option_group';
		self::assertSame( 'procaptcha_group', $subject->$method() );
	}

	/**
	 * Test option_page().
	 */
	public function test_option_page() {
		$subject = Mockery::mock( General::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$method = 'option_page';
		self::assertSame( 'procaptcha', $subject->$method() );
	}

	/**
	 * Test option_name().
	 */
	public function test_option_name() {
		$subject = Mockery::mock( General::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$method = 'option_name';
		self::assertSame( 'procaptcha_settings', $subject->$method() );
	}

	/**
	 * Test page_title().
	 */
	public function test_page_title() {
		$subject = Mockery::mock( General::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$method = 'page_title';
		self::assertSame( 'General', $subject->$method() );
	}

	/**
	 * Test menu_title().
	 */
	public function test_menu_title() {
		$subject = Mockery::mock( General::class )->makePartial()->shouldAllowMockingProtectedMethods();

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
		$expected = '<img class="kagg-settings-menu-image" src="https://site.org/wp-content/plugins/procaptcha-wordpress-plugin/assets/images/procaptcha-icon.svg" alt="procaptcha icon"><span class="kagg-settings-menu-title">procaptcha</span>';

		self::assertSame( $expected, $subject->$method() );
	}

	/**
	 * Test section_title().
	 */
	public function test_section_title() {
		$subject = Mockery::mock( General::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$method = 'section_title';
		self::assertSame( 'general', $subject->$method() );
	}

	/**
	 * Test init_form_fields()
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_init_form_fields() {
		$subject = Mockery::mock( General::class )->makePartial()->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'is_multisite' )->andReturn( false );
		$expected = $this->get_test_general_form_fields();

		$subject->init_form_fields();
		self::assertSame( $expected, $this->get_protected_property( $subject, 'form_fields' ) );
	}

	/**
	 * Test setup_fields().
	 *
	 * @param string $mode procaptcha mode.
	 *
	 * @return void
	 * @throws ReflectionException ReflectionException.
	 * @dataProvider dp_test_setup_fields
	 */
	public function test_setup_fields( string $mode ) {
		$settings = Mockery::mock( Settings::class )->makePartial();
		$settings->shouldReceive( 'get_mode' )->andReturn( $mode );

		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldReceive( 'settings' )->andReturn( $settings );

		$subject = Mockery::mock( General::class )->makePartial();
		$subject->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->andReturn( true );
		$this->set_protected_property( $subject, 'form_fields', $this->get_test_form_fields() );

		WP_Mock::passthruFunction( 'register_setting' );
		WP_Mock::passthruFunction( 'add_settings_field' );

		WP_Mock::userFunction( 'procaptcha' )->with()->once()->andReturn( $main );

		$subject->setup_fields();

		$form_fields = $this->get_protected_property( $subject, 'form_fields' );

		foreach ( $form_fields as $form_field ) {
			self::assertArrayHasKey( 'class', $form_field );
		}

		if ( General::MODE_LIVE === $mode ) {
			$form_fields['site_key']['disabled']   = true;
			$form_fields['secret_key']['disabled'] = true;
		} else {
			$form_fields['site_key']['disabled']   = false;
			$form_fields['secret_key']['disabled'] = false;
		}
	}

	/**
	 * Data provider for test_setup_fields().
	 *
	 * @return array
	 */
	public function dp_test_setup_fields(): array {
		return [
			[ General::MODE_LIVE ],
			[ 'other_mode' ],
		];
	}

	/**
	 * Test setup_fields() not on options screen.
	 *
	 * @return void
	 */
	public function test_setup_fields_not_on_options_screen() {
		$subject = Mockery::mock( General::class )->makePartial();
		$subject->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'is_options_screen' )->andReturn( false );

		$subject->setup_fields();
	}

	/**
	 * Test section_callback().
	 *
	 * @param string $section_id Section id.
	 * @param string $expected   Expected.
	 *
	 * @return void
	 * @dataProvider dp_test_section_callback
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_section_callback( string $section_id, string $expected ) {
		$user          = (object) [ 'ID' => 1 ];
		$notifications = Mockery::mock( Notifications::class )->makePartial();
		$subject       = Mockery::mock( General::class )->makePartial()->shouldAllowMockingProtectedMethods();

		$this->set_protected_property( $subject, 'notifications', $notifications );

		if ( General::SECTION_KEYS === $section_id ) {
			$notifications->shouldReceive( 'show' )->once();
		}

		WP_Mock::userFunction( 'wp_get_current_user' )->andReturn( $user );
		WP_Mock::userFunction( 'get_user_meta' )->andReturn( [] );
		WP_Mock::passthruFunction( 'wp_kses_post' );

		ob_start();
		$subject->section_callback( [ 'id' => $section_id ] );
		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Data provider for test_section_callback().
	 *
	 * @return array
	 */
	public function dp_test_section_callback(): array {
		return [
			'keys'       => [
				General::SECTION_KEYS,
				'				<h2>
					General				</h2>
				<div id="procaptcha-message"></div>
						<h3 class="procaptcha-section-keys">
			<span class="procaptcha-section-header-title">
				Keys			</span>
			<span class="procaptcha-section-header-toggle">
			</span>
		</h3>
		',
			],
			'appearance' => [
				General::SECTION_APPEARANCE,
				'		<h3 class="procaptcha-section-appearance">
			<span class="procaptcha-section-header-title">
				Appearance			</span>
			<span class="procaptcha-section-header-toggle">
			</span>
		</h3>
		',
			],
			'custom'     => [
				General::SECTION_CUSTOM,
				'		<h3 class="procaptcha-section-custom">
			<span class="procaptcha-section-header-title">
				Custom			</span>
			<span class="procaptcha-section-header-toggle">
			</span>
		</h3>
		',
			],
			'enterprise' => [
				General::SECTION_ENTERPRISE,
				'		<h3 class="procaptcha-section-enterprise">
			<span class="procaptcha-section-header-title">
				Enterprise			</span>
			<span class="procaptcha-section-header-toggle">
			</span>
		</h3>
		',
			],
			'other'      => [
				General::SECTION_OTHER,
				'		<h3 class="procaptcha-section-other">
			<span class="procaptcha-section-header-title">
				Other			</span>
			<span class="procaptcha-section-header-toggle">
			</span>
		</h3>
		',
			],
			'wrong'      => [ 'wrong', '' ],
		];
	}

	/**
	 * Test admin_enqueue_scripts().
	 *
	 * @throws ReflectionException ReflectionException.
	 */
	public function test_admin_enqueue_scripts() {
		$plugin_url          = 'http://test.test/wp-content/plugins/procaptcha-wordpress-plugin';
		$plugin_version      = '1.0.0';
		$min_prefix          = '.min';
		$ajax_url            = 'https://test.test/wp-admin/admin-ajax.php';
		$nonce               = 'some_nonce';
		$site_key            = 'some key';
		$check_config_notice =
			'Credentials changed.' . "\n" .
			'Please complete procaptcha and check the site config.';

		$settings = Mockery::mock( Settings::class )->makePartial();
		$settings->shouldReceive( 'get' )->with( 'site_key' )->andReturn( $site_key );

		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldReceive( 'settings' )->andReturn( $settings );

		$subject = Mockery::mock( General::class )->makePartial();
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

		WP_Mock::userFunction( 'procaptcha' )->with()->once()->andReturn( $main );

		WP_Mock::userFunction( 'wp_enqueue_script' )
			->with(
				General::DIALOG_HANDLE,
				$plugin_url . "/assets/js/kagg-dialog$min_prefix.js",
				[],
				$plugin_version,
				true
			)
			->once();

		WP_Mock::userFunction( 'wp_enqueue_script' )
			->with(
				General::HANDLE,
				$plugin_url . "/assets/js/general$min_prefix.js",
				[ 'jquery', General::DIALOG_HANDLE ],
				$plugin_version,
				true
			)
			->once();

		WP_Mock::userFunction( 'admin_url' )
			->with( 'admin-ajax.php' )
			->andReturn( $ajax_url )
			->once();

		WP_Mock::userFunction( 'wp_create_nonce' )
			->with( General::CHECK_CONFIG_ACTION )
			->andReturn( $nonce )
			->once();

		WP_Mock::userFunction( 'wp_create_nonce' )
			->with( General::TOGGLE_SECTION_ACTION )
			->andReturn( $nonce )
			->once();

		WP_Mock::userFunction( 'wp_localize_script' )
			->with(
				General::HANDLE,
				General::OBJECT,
				[
					'ajaxUrl'                              => $ajax_url,
					'checkConfigAction'                    => General::CHECK_CONFIG_ACTION,
					'checkConfigNonce'                     => $nonce,
					'toggleSectionAction'                  => General::TOGGLE_SECTION_ACTION,
					'toggleSectionNonce'                   => $nonce,
					'modeLive'                             => General::MODE_LIVE,
					'modeTestPublisher'                    => General::MODE_TEST_PUBLISHER,
					'modeTestEnterpriseSafeEndUser'        => General::MODE_TEST_ENTERPRISE_SAFE_END_USER,
					'modeTestEnterpriseBotDetected'        => General::MODE_TEST_ENTERPRISE_BOT_DETECTED,
					'siteKey'                              => $site_key,
					'modeTestPublisherSiteKey'             => General::MODE_TEST_PUBLISHER_SITE_KEY,
					'modeTestEnterpriseSafeEndUserSiteKey' => General::MODE_TEST_ENTERPRISE_SAFE_END_USER_SITE_KEY,
					'modeTestEnterpriseBotDetectedSiteKey' => General::MODE_TEST_ENTERPRISE_BOT_DETECTED_SITE_KEY,
					'checkConfigNotice'                    => $check_config_notice,
					'checkingConfigMsg'                    => 'Checking site config...',
					'completeProcaptchaTitle'                => 'Please complete the procaptcha.',
					'completeProcaptchaContent'              => 'Before checking the site config, please complete the Active procaptcha in the current section.',
					'OKBtnText'                            => 'OK',
				]
			)
			->once();

		WP_Mock::userFunction( 'wp_enqueue_style' )
			->with(
				General::DIALOG_HANDLE,
				$plugin_url . "/assets/css/kagg-dialog$min_prefix.css",
				[],
				$plugin_version
			)
			->once();

		WP_Mock::userFunction( 'wp_enqueue_style' )
			->with(
				General::HANDLE,
				$plugin_url . "/assets/css/general$min_prefix.css",
				[ PluginSettingsBase::PREFIX . '-' . SettingsBase::HANDLE, General::DIALOG_HANDLE ],
				$plugin_version
			)
			->once();

		$subject->admin_enqueue_scripts();
	}
}
