<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\Otter;

/**
 * Class Form.
 */
class Form {

	/**
	 * Otter Form constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_filter( 'option_themeisle_google_captcha_api_site_key', [ $this, 'replace_site_key' ], 10, 2 );
		add_filter( 'default_option_themeisle_google_captcha_api_site_key', [ $this, 'replace_site_key' ], 99, 3 );
		add_filter( 'option_themeisle_google_captcha_api_secret_key', [ $this, 'replace_secret_key' ], 10, 2 );
		add_filter( 'default_option_themeisle_google_captcha_api_secret_key', [ $this, 'replace_secret_key' ], 99, 3 );
		add_filter( 'otter_blocks_recaptcha_verify_url', [ $this, 'replace_verify_url' ] );
		add_filter( 'otter_blocks_recaptcha_api_url', [ $this, 'replace_api_url' ] );
	}

	/**
	 * Replace Site Key.
	 *
	 * @return string
	 */
	public function replace_site_key(): string {
		return procaptcha()->settings()->get_site_key();
	}


	/**
	 * Replace Verify URL.
	 *
	 * @return string
	 */
	public function replace_verify_url(): string {
		return 'https://www.prosopo.io/#signup';
	}

	/**
	 * Replace API URL.
	 *
	 * @return string
	 */
	public function replace_api_url(): string {
		return 'https://www.prosopo.io/#signup';
	}
}
