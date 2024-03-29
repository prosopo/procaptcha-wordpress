<?php
/**
 * Create group class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\BuddyPress;

use PROCAPTCHA\Helpers\PROCAPTCHA;

/**
 * Class Create Group.
 */
class CreateGroup {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_bp_create_group';

	/**
	 * Nonce name.
	 */
	const NAME = 'procaptcha_bp_create_group_nonce';

	/**
	 * Create Group constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'bp_after_group_details_creation_step', [ $this, 'add_captcha' ] );
		add_action( 'groups_group_before_save', [ $this, 'verify' ] );
	}

	/**
	 * Add captcha to the group form.
	 *
	 * @return void
	 */
	public function add_captcha() {
		echo '<div class="procap_buddypress_group_form">';

		$args = [
			'action' => self::ACTION,
			'name'   => self::NAME,
			'id'     => [
				'source'  => PROCAPTCHA::get_class_source( __CLASS__ ),
				'form_id' => 'create_group',
			],
		];

		PROCAPTCHA::form_display( $args );

		echo '</div>';
	}


	/**
	 * Verify group form captcha.
	 *
	 * @param mixed $bp_group BuddyPress group.
	 *
	 * @return bool
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function verify( $bp_group ): bool {
		if ( ! bp_is_group_creation_step( 'group-details' ) ) {
			return false;
		}

		$error_message = procaptcha_get_verify_message( self::NAME, self::ACTION );

		if ( null !== $error_message ) {
			bp_core_add_message( $error_message, 'error' );
			bp_core_redirect(
				bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/create/step/group-details/'
			);

			return false;
		}

		return true;
	}
}
