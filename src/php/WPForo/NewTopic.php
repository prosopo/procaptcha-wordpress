<?php
/**
 * NewTopic class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\WPForo;

/**
 * Class NewTopic.
 */
class NewTopic extends Base {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_wpforo_new_topic';

	/**
	 * Nonce name.
	 */
	const NAME = 'procaptcha_wpforo_new_topic_nonce';

	/**
	 * Add captcha hook.
	 */
	const ADD_CAPTCHA_HOOK = 'wpforo_topic_form_buttons_hook';

	/**
	 * Verify hook.
	 */
	const VERIFY_HOOK = 'wpforo_add_topic_data_filter';
}
