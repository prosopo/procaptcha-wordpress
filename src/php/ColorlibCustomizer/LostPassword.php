<?php
/**
 * LostPassword class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\ColorlibCustomizer;

/**
 * Class LostPassword
 */
class LostPassword extends Base {

	/**
	 * Get login style.
	 *
	 * @param string $procaptcha_size procaptcha widget size.
	 *
	 * @return string
	 * @noinspection CssUnusedSymbol
	 */
	protected function get_style( string $procaptcha_size ): string {
		$css = parent::get_style( $procaptcha_size );

		switch ( $procaptcha_size ) {
			case 'compact':
			case 'normal':
				$css .= <<<CSS
	.ml-container #lostpasswordform {
		height: unset;
	}
CSS;
				break;
			case 'invisible':
			default:
				break;
		}

		return $css;
	}
}
