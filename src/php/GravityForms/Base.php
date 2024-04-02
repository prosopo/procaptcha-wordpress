<?php
/**
 * Base class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\GravityForms;

/**
 * Class Base.
 */
abstract class Base {

	/**
	 * Nonce action.
	 */
	const ACTION = 'gravity_forms_form';

	/**
	 * Nonce name.
	 */
	const NONCE = 'gravity_forms_form_nonce';
}
