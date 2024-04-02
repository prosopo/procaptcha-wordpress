<?php
/**
 * LostPassword class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\ClassifiedListing;

use Procaptcha\Abstracts\LostPasswordBase;

/**
 * Class LostPassword.
 */
class LostPassword extends LostPasswordBase {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_classified_listing_lost_password';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_classified_listing_lost_password_nonce';

	/**
	 * Add procaptcha action.
	 */
	const ADD_CAPTCHA_ACTION = 'rtcl_lost_password_form';

	/**
	 * $_POST key to check.
	 */
	const POST_KEY = 'rtcl-lost-password';

	/**
	 * $_POST value to check.
	 */
	const POST_VALUE = 'Reset Password';
}
