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
			'version' => PROCAPTCHA_VERSION,
		];
		$plugin_file = PROCAPTCHA_FILE;

		$plugin_headers = get_file_data(
			$plugin_file,
			[ 'version' => 'Version' ],
			'plugin'
		);

		self::assertSame( $expected, $plugin_headers );

		self::assertSame( realpath( __DIR__ . '/../../../' ), PROCAPTCHA_PATH );

		$config = include __DIR__ . '/../../../.codeception/_config/params.local.php';
		$wp_url = $config['WP_URL'];
		self::assertSame( 'http://' . $wp_url . '/wp-content/plugins/procaptcha-wordpress-plugin', PROCAPTCHA_URL );

		self::assertSame( realpath( __DIR__ . '/../../../procaptcha.php' ), PROCAPTCHA_FILE );

		self::assertSame( 'procaptcha_action', PROCAPTCHA_ACTION );
		self::assertSame( 'procaptcha_nonce', PROCAPTCHA_NONCE );

		// request.php was required.
		self::assertTrue( function_exists( 'procaptchaget_user_ip' ) );
		self::assertTrue( function_exists( 'procaptchaget_error_messages' ) );
		self::assertTrue( function_exists( 'procaptchaget_error_message' ) );
		self::assertTrue( function_exists( 'procaptcha_request_verify' ) );
		self::assertTrue( function_exists( 'procaptcha_verify_post' ) );
		self::assertTrue( function_exists( 'procaptcha_get_verify_output' ) );
		self::assertTrue( function_exists( 'procaptcha_get_verify_message' ) );
		self::assertTrue( function_exists( 'procaptcha_get_verify_message_html' ) );

		// functions.php was required.
		self::assertTrue( function_exists( 'procaptchaform' ) );
		self::assertTrue( function_exists( 'procaptchaform_display' ) );
		self::assertTrue( function_exists( 'procaptchashortcode' ) );
		self::assertTrue( shortcode_exists( 'procaptcha' ) );
	}

	/**
	 * Test that readme.txt contains proper stable tag.
	 */
	public function test_readme_txt() {
		$expected    = [
			'stable_tag' => PROCAPTCHA_VERSION,
		];
		$readme_file = PROCAPTCHA_PATH . '/readme.txt';

		$readme_headers = get_file_data(
			$readme_file,
			[ 'stable_tag' => 'Stable tag' ],
			'plugin'
		);

		self::assertSame( $expected, $readme_headers );
	}
}
