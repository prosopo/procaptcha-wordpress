<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\WPDiscuz;

use Procaptcha\Helpers\Procaptcha;
use WP_User;

/**
 * Class Form.
 */
class Comment extends Base {

	/**
	 * Add hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		parent::init_hooks();

		add_filter( 'wpdiscuz_form_render', [ $this, 'add_procaptcha' ], 10, 3 );
		add_filter( 'preprocess_comment', [ $this, 'verify' ], 9 );
		add_action( 'wp_head', [ $this, 'print_inline_styles' ], 20 );
	}

	/**
	 * Add procaptcha to wpDiscuz form.
	 *
	 * @param string|mixed  $output         Output.
	 * @param int|string    $comments_count Comments count.
	 * @param WP_User|false $current_user   Current user.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_procaptcha( $output, $comments_count, $current_user ): string {
		global $post;

		$args = [
			'id' => [
				'source'  => Procaptcha::get_class_source( static::class ),
				'form_id' => $post->ID ?? 0,
			],
		];

		ob_start();
		?>
		<div class="wpd-field-procaptcha wpdiscuz-item">
			<div class="wpdiscuz-procaptcha" id="wpdiscuz-procaptcha"></div>
			<?php Procaptcha::form_display( $args ); ?>
			<div class="clearfix"></div>
		</div>
		<?php
		$form = ob_get_clean();

		$search = '<div class="wc-field-submit">';

		return str_replace( $search, $form . $search, (string) $output );
	}

	/**
	 * Verify request.
	 *
	 * @param array|mixed $comment_data Comment data.
	 *
	 * @return array|mixed
	 * @noinspection PhpUndefinedFunctionInspection
	 * @noinspection ForgottenDebugOutputInspection
	 */
	public function verify( $comment_data ) {
		$wp_discuz = wpDiscuz();

		remove_filter( 'preprocess_comment', [ $wp_discuz, 'validateRecaptcha' ] );

		// Nonce is checked by wpDiscuz.

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$procaptcha_response = isset( $_POST['procaptcha-response'] ) ?
			filter_var( wp_unslash( $_POST['procaptcha-response'] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ) :
			'';

		$result = procaptcha_request_verify( $procaptcha_response );

		if ( null === $result ) {
			return $comment_data;
		}

		unset( $_POST['procaptcha-response'], $_POST['g-recaptcha-response'] );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		wp_die( esc_html( $result ) );
	}

	/**
	 * Print inline styles.
	 *
	 * @return void
	 * @noinspection CssUnusedSymbol
	 */
	public function print_inline_styles() {
		$css = <<<CSS
	.wpd-field-procaptcha .procaptcha {
		margin-left: auto;
	}
CSS;

		Procaptcha::css_display( $css );
	}
}
