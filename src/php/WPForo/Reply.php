<?php
/**
 * Reply class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\WPForo;

/**
 * Class Reply.
 */
class Reply extends Base {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_wpforo_reply';

	/**
	 * Nonce name.
	 */
	const NAME = 'procaptcha_wpforo_reply_nonce';

	/**
	 * Add captcha hook.
	 */
	const ADD_CAPTCHA_HOOK = 'wpforo_reply_form_buttons_hook';

	/**
	 * Verify hook.
	 */
	const VERIFY_HOOK = 'wpforo_add_post_data_filter';
}
