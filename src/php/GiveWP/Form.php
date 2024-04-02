<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\GiveWP;

/**
 * Class Form.
 * Supports a Donation form.
 */
class Form extends Base {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_give_wp_form';

	/**
	 * Nonce name.
	 */
	const NAME = 'procaptcha_give_wp_form_nonce';

	/**
	 * Add captcha hook.
	 */
	const ADD_CAPTCHA_HOOK = 'give_donation_form_user_info';

	/**
	 * Verify hook.
	 */
	const VERIFY_HOOK = 'give_checkout_error_checks';
}
