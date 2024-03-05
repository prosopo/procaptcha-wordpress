<?php
/**
 * LostPassword class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\ColorlibCustomizer;

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
	 */
	protected function get_style( string $procaptcha_size ): string {
		ob_start();

		switch ( $procaptcha_size ) {
			case 'normal':
				?>
				<!--suppress CssUnusedSymbol -->
				<style>
					.ml-container #login {
						min-width: 350px;
					}
					.ml-container #lostpasswordform {
						height: unset;
					}
				</style>
				<?php
				break;
			case 'compact':
				?>
				<style>
					.ml-container #lostpasswordform {
						height: unset;
					}
				</style>
				<?php
				break;
			case 'invisible':
			default:
				break;
		}

		return ob_get_clean();
	}
}
