<?php
/**
 * Reply class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\BBPress;

/**
 * Class Reply.
 */
class Reply extends Base {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_bbp_reply';

	/**
	 * Nonce name.
	 */
	const NAME = 'procaptcha_bbp_reply_nonce';

	/**
	 * Add captcha hook.
	 */
	const ADD_CAPTCHA_HOOK = 'bbp_theme_after_reply_form_content';

	/**
	 * Verify hook.
	 */
	const VERIFY_HOOK = 'bbp_new_reply_pre_extras';
}
