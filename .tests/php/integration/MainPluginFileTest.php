<?php
/**
 * MainPluginFileTest class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\Tests\Integration;

/**
 * Test main plugin file.
 *
 * @group main-plugin-file
 */
class MainPluginFileTest extends ProcaptchaWPTestCase {

	/**
	 * Test main plugin file content.
	 *
	 * @noinspection HttpUrlsUsage
	 */
	public function test_main_file_content() {
		$expected    = [
			'version' => HCAPTCHA_VERSION,
		];
		$plugin_file = HCAPTCHA_FILE;

		$plugin_headers = get_file_data(
			$plugin_file,
			[ 'version' => 'Version' ],
			'plugin'
		);

		self::assertSame( $expected, $plugin_headers );

		self::assertSame( realpath( __DIR__ . '/../../../' ), HCAPTCHA_PATH );

		$config = include __DIR__ . '/../../../.codeception/_config/params.local.php';
		$wp_url = $config['WP_URL'];
		self::assertSame( 'http://' . $wp_url . '/wp-content/plugins/procaptcha-wordpress-plugin', HCAPTCHA_URL );

		self::assertSame( realpath( __DIR__ . '/../../../procaptcha.php' ), HCAPTCHA_FILE );

		self::assertSame( 'procaptcha_action', HCAPTCHA_ACTION );
		self::assertSame( 'procaptcha_nonce', HCAPTCHA_NONCE );

		// request.php was required.
		self::assertTrue( function_exists( 'procap_get_user_ip' ) );
		self::assertTrue( function_exists( 'procap_get_error_messages' ) );
		self::assertTrue( function_exists( 'procap_get_error_message' ) );
		self::assertTrue( function_exists( 'procaptcha_request_verify' ) );
		self::assertTrue( function_exists( 'procaptcha_verify_post' ) );
		self::assertTrue( function_exists( 'procaptcha_get_verify_output' ) );
		self::assertTrue( function_exists( 'procaptcha_get_verify_message' ) );
		self::assertTrue( function_exists( 'procaptcha_get_verify_message_html' ) );

		// functions.php was required.
		self::assertTrue( function_exists( 'procap_form' ) );
		self::assertTrue( function_exists( 'procap_form_display' ) );
		self::assertTrue( function_exists( 'procap_shortcode' ) );
		self::assertTrue( shortcode_exists( 'procaptcha' ) );
	}

	/**
	 * Test that readme.txt contains proper stable tag.
	 */
	public function test_readme_txt() {
		$expected    = [
			'stable_tag' => HCAPTCHA_VERSION,
		];
		$readme_file = HCAPTCHA_PATH . '/readme.txt';

		$readme_headers = get_file_data(
			$readme_file,
			[ 'stable_tag' => 'Stable tag' ],
			'plugin'
		);

		self::assertSame( $expected, $readme_headers );
	}
}
