<?php
/**
 * LostPassword class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\LearnDash;

use Procaptcha\Helpers\Procaptcha;

/**
 * Class LostPassword.
 */
class LostPassword {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 */
	protected function init_hooks() {
		add_filter( 'do_shortcode_tag', [ $this, 'add_captcha' ], 10, 4 );
	}

	/**
	 * Filters the output created by a shortcode callback.
	 *
	 * @param string|mixed $output Shortcode output.
	 * @param string       $tag    Shortcode name.
	 * @param array|string $attr   Shortcode attributes array or empty string.
	 * @param array        $m      Regular expression match array.
	 *
	 * @return string|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_captcha( $output, string $tag, $attr, array $m ) {
		if ( 'ld_reset_password' !== $tag ) {
			return $output;
		}

		$args = [
			'action' => PROCAPTCHA_ACTION,
			'name'   => PROCAPTCHA_NONCE,
			'auto'   => true,
			'id'     => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => 'lost_password',
			],
		];

		$search  = '<input type="submit"';
		$replace = Procaptcha::form( $args ) . $search;

		$output = (string) str_replace(
			$search,
			$replace,
			(string) $output
		);

		/** This action is documented in src/php/Sendinblue/Sendinblue.php */
		do_action( 'procaptchaauto_verify_register', $output );

		return $output;
	}
}
