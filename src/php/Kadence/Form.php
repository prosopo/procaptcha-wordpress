<?php
/**
 * Form class file.
 *
 * @package procaptcha-wp
 */

namespace PROCAPTCHA\Kadence;

use PROCAPTCHA\Helpers\PROCAPTCHA;
use PROCAPTCHA\Helpers\Request;
use WP_Block;

/**
 * Class Form.
 */
class Form {

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
		add_action( 'wp_ajax_kb_process_ajax_submit', [ $this, 'process_ajax' ], 9 );
		add_action( 'wp_ajax_nopriv_kb_process_ajax_submit', [ $this, 'process_ajax' ], 9 );

		if ( ! Request::is_frontend() ) {
			return;
		}

		add_filter( 'render_block', [ $this, 'render_block' ], 10, 3 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
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
		if ( 'kadence/form' !== $block['blockName'] ) {
			return $block_content;
		}

		$args = [
			'id' => [
				'source'  => PROCAPTCHA::get_class_source( __CLASS__ ),
				'form_id' => isset( $block['attrs']['postID'] ) ? (int) $block['attrs']['postID'] : 0,
			],
		];

		$pattern       = '/(<div class="kadence-blocks-form-field google-recaptcha-checkout-wrap">).+?(<\/div>)/';
		$block_content = (string) $block_content;

		if ( preg_match( $pattern, $block_content ) ) {
			// Do not replace reCaptcha V2.
			return $block_content;
		}

		if ( false !== strpos( $block_content, 'recaptcha_response' ) ) {
			// Do not replace reCaptcha V3.
			return $block_content;
		}

		$search = '<div class="kadence-blocks-form-field kb-submit-field';

		return (string) str_replace(
			$search,
			PROCAPTCHA::form( $args ) . $search,
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

		// Nonce is checked by Kadence.

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$procaptcha_response = isset( $_POST['procaptcha-response'] ) ?
			filter_var( wp_unslash( $_POST['procaptcha-response'] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ) :
			'';

		$error = procaptcha_request_verify( $procaptcha_response );

		if ( null === $error ) {
			return;
		}

		unset( $_POST['procaptcha-response'], $_POST['g-recaptcha-response'] );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$data = [
			'html'         => '<div class="kadence-blocks-form-message kadence-blocks-form-warning">' . $error . '</div>',
			'console'      => __( 'procaptcha Failed', 'procaptcha-wordpress' ),
			'required'     => null,
			'headers_sent' => headers_sent(),
		];

		wp_send_json_error( $data );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {
		$min = procap_min_suffix();

		wp_enqueue_script(
			'procaptcha-kadence',
			PROCAPTCHA_URL . "/assets/js/procaptcha-kadence$min.js",
			[ 'procaptcha' ],
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
		// Nonce is checked by Kadence.

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$form_id = isset( $_POST['_kb_form_id'] ) ? sanitize_text_field( wp_unslash( $_POST['_kb_form_id'] ) ) : '';
		$post_id = isset( $_POST['_kb_form_post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['_kb_form_post_id'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$post = get_post( $post_id );

		if ( ! $post ) {
			return false;
		}

		foreach ( parse_blocks( $post->post_content ) as $block ) {
			if (
				isset( $block['blockName'], $block['attrs']['uniqueID'] ) &&
				'kadence/form' === $block['blockName'] &&
				$form_id === $block['attrs']['uniqueID'] &&
				! empty( $block['attrs']['recaptcha'] )
			) {
				return true;
			}
		}

		return false;
	}
}
