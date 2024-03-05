<?php
/**
 * JetpackContactForm class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\Jetpack;

use PROCAPTCHA\Helpers\PROCAPTCHA;

/**
 * Class JetpackContactForm
 */
class JetpackForm extends JetpackBase {

	/**
	 * Add procaptcha to a Jetpack contact form.
	 *
	 * @param string|mixed $content Content.
	 *
	 * @return string
	 */
	public function add_captcha( $content ): string {
		$content = (string) $content;

		// Jetpack classic form.
		$content = (string) preg_replace_callback(
			'~(\[contact-form[\s\S]*?][\s\S]*?)(\[/contact-form])~',
			[ $this, 'classic_callback' ],
			$content
		);

		// Jetpack block form.
		return (string) preg_replace_callback(
			'~<form [\s\S]*?wp-block-jetpack-contact-form[\s\S]*?(<button [\s\S]*?type="submit"[\s\S]*?</button>)[\s\S]*?</form>~',
			[ $this, 'block_callback' ],
			$content
		);
	}

	/**
	 * Add procaptcha shortcode to the provided shortcode for a Jetpack classic contact form.
	 *
	 * @param array $matches Matches.
	 *
	 * @return string
	 */
	public function classic_callback( array $matches ): string {
		if ( has_shortcode( $matches[0], 'procaptcha' ) ) {
			return $matches[0];
		}

		$args = [
			'action' => self::ACTION,
			'name'   => self::NAME,
			'id'     => [
				'source'  => PROCAPTCHA::get_class_source( __CLASS__ ),
				'form_id' => 'contact',
			],
		];

		return $matches[1] . $this->error_message( PROCAPTCHA::form( $args ) ) . $matches[2];
	}

	/**
	 * Add procaptcha shortcode to the provided shortcode for a Jetpack block contact form.
	 *
	 * @param array $matches Matches.
	 *
	 * @return string
	 */
	public function block_callback( array $matches ): string {
		if ( has_shortcode( $matches[0], 'procaptcha' ) ) {
			return $matches[0];
		}

		$args = [
			'action' => self::ACTION,
			'name'   => self::NAME,
			'id'     => [
				'source'  => PROCAPTCHA::get_class_source( __CLASS__ ),
				'form_id' => 'contact',
			],
		];

		return str_replace(
			$matches[1],
			$this->error_message( PROCAPTCHA::form( $args ) ) . $matches[1],
			$matches[0]
		);
	}
}
