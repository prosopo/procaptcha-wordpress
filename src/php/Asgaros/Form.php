<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\Asgaros;

/**
 * Class Form.
 * Supports New Topic and Reply forms.
 */
class Form extends Base {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_asgaros_new_topic';

	/**
	 * Nonce name.
	 */
	const NAME = 'procaptcha_asgaros_new_topic_nonce';

	/**
	 * Add captcha hook.
	 */
	const ADD_CAPTCHA_HOOK = 'do_shortcode_tag';

	/**
	 * Verify hook.
	 */
	const VERIFY_HOOK = 'asgarosforum_filter_insert_custom_validation';
}
