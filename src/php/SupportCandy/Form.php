<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\SupportCandy;

/**
 * Class Form.
 * Supports a New Ticket form.
 */
class Form extends Base {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_support_candy_new_topic';

	/**
	 * Nonce name.
	 */
	const NAME = 'procaptcha_support_candy_new_topic_nonce';

	/**
	 * Add captcha hook.
	 */
	const ADD_CAPTCHA_HOOK = 'wpsc_print_tff';

	/**
	 * Verify hook.
	 */
	const VERIFY_HOOK = 'wpsc_set_ticket_form';
}
