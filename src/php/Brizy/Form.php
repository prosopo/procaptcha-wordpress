<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\Brizy;

/**
 * Class Form.
 * Supports Brizy form.
 */
class Form extends Base {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_brizy_form';

	/**
	 * Nonce name.
	 */
	const NAME = 'procaptcha_brizy_nonce';

	/**
	 * Add captcha hook.
	 */
	const ADD_CAPTCHA_HOOK = 'brizy_content';

	/**
	 * Verify hook.
	 */
	const VERIFY_HOOK = 'brizy_form';
}
