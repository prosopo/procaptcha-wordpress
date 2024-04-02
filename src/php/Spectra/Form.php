<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\Spectra;

use Procaptcha\Helpers\Procaptcha;
use Procaptcha\Helpers\Request;
use WP_Block;

/**
 * Class Form.
 */
class Form {

	/**
	 * Nonce action.
	 */
	const ACTION = 'procaptcha_spectra_form';

	/**
	 * Nonce name.
	 */
	const NONCE = 'procaptcha_spectra_form_nonce';

	/**
	 * Script handle.
	 */
	const HANDLE = 'procaptcha-spectra';

	/**
	 * Whether form has reCaptcha field.
	 *
	 * @var bool
	 */
	private $has_recaptcha_field;

	/**
	 * Form constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'wp_ajax_uagb_process_forms', [ $this, 'process_ajax' ], 9 );
		add_action( 'wp_ajax_nopriv_uagb_process_forms', [ $this, 'process_ajax' ], 9 );

		if ( ! Request::is_frontend() ) {
			return;
		}

		add_filter( 'render_block', [ $this, 'render_block' ], 10, 3 );
		add_action( 'wp_head', [ $this, 'print_inline_styles' ], 20 );
		add_action( 'procaptchaprint_procaptcha_scripts', [ $this, 'print_procaptcha_scripts' ] );
		add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_scripts' ], 9 );
	}

	/**
	 * Render block filter.
	 *
	 * @param string|mixed $block_content Block content.
	 * @param array        $block         Block.
	 * @param WP_Block     $instance      Instance.
	 *
	 * @return string|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function render_block( $block_content, array $block, WP_Block $instance ) {
		if ( 'uagb/forms' !== $block['blockName'] ) {
			return $block_content;
		}

		$args = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'id'     => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => isset( $block['attrs']['block_id'] ) ? (int) $block['attrs']['block_id'] : 0,
			],
		];

		$block_content = (string) $block_content;

		$this->has_recaptcha_field = false;

		if ( false !== strpos( $block_content, 'uagb-forms-recaptcha' ) ) {
			$this->has_recaptcha_field = true;

			// Do not replace reCaptcha.
			return $block_content;
		}

		$search = '<div class="uagb-forms-main-submit-button-wrap';

		return (string) str_replace(
			$search,
			Procaptcha::form( $args ) . $search,
			$block_content
		);
	}

	/**
	 * Process ajax.
	 *
	 * @return void
	 */
	public function process_ajax() {
		if ( $this->has_recaptcha() ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$form_data = isset( $_POST['form_data'] ) ?
			json_decode( sanitize_text_field( wp_unslash( $_POST['form_data'] ) ), true ) :
			[];
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$_POST['procaptcha-response'] = $form_data['procaptcha-response'] ?? '';
		$_POST[ self::NONCE ]        = $form_data[ self::NONCE ] ?? '';

		$error_message = procaptcha_verify_post( self::NONCE, self::ACTION );

		unset( $_POST['procaptcha-response'], $_POST[ self::NONCE ] );

		if ( null === $error_message ) {
			return;
		}

		// Spectra cannot process error messages from the backend.
		wp_send_json_error( 400 );
	}

	/**
	 * Print inline styles.
	 *
	 * @return void
	 * @noinspection CssUnusedSymbol
	 */
	public function print_inline_styles() {
		static $style_shown;

		if ( $style_shown ) {
			return;
		}

		$style_shown = true;

		$css = <<<CSS
	.uagb-forms-main-form .procaptcha {
		margin-bottom: 20px;
	}
CSS;

		Procaptcha::css_display( $css );
	}

	/**
	 * Filter print procaptcha scripts status and return true if no reCaptcha is in the form.
	 *
	 * @param bool|mixed $status Print scripts status.
	 *
	 * @return bool
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function print_procaptcha_scripts( $status ): bool {
		return ! $this->has_recaptcha_field;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$min = procaptchamin_suffix();

		wp_enqueue_script(
			self::HANDLE,
			PROCAPTCHA_URL . "/assets/js/procaptcha-spectra$min.js",
			[],
			PROCAPTCHA_VERSION,
			true
		);
	}

	/**
	 * Whether form has recaptcha.
	 *
	 * @return bool
	 */
	private function has_recaptcha(): bool {
		// Spectra check nonce.

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$post_id  = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
		$block_id = isset( $_POST['block_id'] ) ? sanitize_text_field( wp_unslash( $_POST['block_id'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$post_content = get_post_field( 'post_content', sanitize_text_field( $post_id ) );

		foreach ( parse_blocks( $post_content ) as $block ) {
			if (
				isset( $block['blockName'], $block['attrs']['block_id'] ) &&
				'uagb/forms' === $block['blockName'] &&
				$block_id === $block['attrs']['block_id'] &&
				! empty( $block['attrs']['reCaptchaEnable'] )
			) {
				return true;
			}
		}

		return false;
	}
}