<?php
/**
 * AdvancedForm class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\Kadence;

use Procaptcha\Helpers\Procaptcha;
use Procaptcha\Helpers\Request;
use WP_Block;

/**
 * Class AdvancedForm.
 */
class AdvancedForm {

	/**
	 * Admin script handle.
	 */
	const ADMIN_HANDLE = 'admin-kadence-advanced';

	/**
	 * Script localization object.
	 */
	const OBJECT = 'ProcaptchaKadenceAdvancedFormObject';

	/**
	 * Whether procap_ was replaced.
	 *
	 * @var bool
	 */
	private $procaptcha_found = false;

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
		add_filter( 'render_block', [ $this, 'render_block' ], 10, 3 );
		add_action( 'wp_print_footer_scripts', [ $this, 'dequeue_kadence_procaptcha_api' ], 8 );

		if ( Request::is_frontend() ) {
			add_filter(
				'block_parser_class',
				static function () {
					return AdvancedBlockParser::class;
				}
			);

			add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_scripts' ], 9 );

			return;
		}

		add_action( 'wp_ajax_kb_process_advanced_form_submit', [ $this, 'process_ajax' ], 9 );
		add_action( 'wp_ajax_nopriv_kb_process_advanced_form_submit', [ $this, 'process_ajax' ], 9 );
		add_filter(
			'pre_option_kadence_blocks_procaptcha_site_key',
			static function () {
				return procaptcha()->settings()->get_site_key();
			}
		);
		add_filter(
			'pre_option_kadence_blocks_procaptcha_secret_key',
			static function () {
				return procaptcha()->settings()->get_secret_key();
			}
		);
		add_action( 'enqueue_block_editor_assets', [ $this, 'editor_assets' ] );
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
	 * @noinspection HtmlUnknownAttribute
	 */
	public function render_block( $block_content, array $block, WP_Block $instance ) {
		if ( 'kadence/advanced-form-submit' === $block['blockName'] && ! $this->procaptcha_found ) {

			$search = '<div class="kb-adv-form-field kb-submit-field';

			return str_replace( $search, $this->get_procaptcha() . $search, $block_content );
		}

		if ( 'kadence/advanced-form-captcha' !== $block['blockName'] ) {
			return $block_content;
		}

		$block_content = (string) preg_replace(
			'#<div class="procaptcha" .*?></div>#',
			$this->get_procaptcha(),
			(string) $block_content,
			1,
			$count
		);

		$this->procaptcha_found = (bool) $count;

		return $block_content;
	}

	/**
	 * Process ajax.
	 *
	 * @return void
	 */
	public function process_ajax() {
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
			'html'     => '<div class="kb-adv-form-message kb-adv-form-warning">' . $error . '</div>',
			'console'  => __( 'procap_ Failed', 'procaptcha-wordpress' ),
			'required' => null,
		];

		wp_send_json_error( $data );
	}

	/**
	 * Dequeue Kadence procaptcha API script.
	 *
	 * @return void
	 */
	public function dequeue_kadence_procaptcha_api() {
		wp_dequeue_script( 'kadence-blocks-procaptcha' );
		wp_deregister_script( 'kadence-blocks-procaptcha' );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {
		$min = procap_min_suffix();

		wp_enqueue_script(
			'procaptcha-kadence-advanced',
			PROCAPTCHA_URL . "/assets/js/procaptcha-kadence-advanced$min.js",
			[ 'procaptcha', 'kadence-blocks-advanced-form' ],
			PROCAPTCHA_VERSION,
			true
		);
	}

	/**
	 * Enqueue editor assets.
	 *
	 * @return void
	 */
	public function editor_assets() {
		$min = procap_min_suffix();

		wp_enqueue_script(
			self::ADMIN_HANDLE,
			PROCAPTCHA_URL . "/assets/js/admin-kadence-advanced$min.js",
			[],
			PROCAPTCHA_VERSION,
			true
		);

		$notice = Procaptcha::get_procaptcha_plugin_notice();

		wp_localize_script(
			self::ADMIN_HANDLE,
			self::OBJECT,
			[
				'noticeLabel'       => $notice['label'],
				'noticeDescription' => $notice['description'],
			]
		);

		wp_enqueue_style(
			self::ADMIN_HANDLE,
			constant( 'PROCAPTCHA_URL' ) . "/assets/css/admin-kadence-advanced$min.css",
			[],
			PROCAPTCHA_VERSION
		);
	}

	/**
	 * Get procap_.
	 *
	 * @return string
	 */
	private function get_procaptcha(): string {
		$args = [
			'id' => [
				'source'  => Procaptcha::get_class_source( __CLASS__ ),
				'form_id' => AdvancedBlockParser::$form_id,
			],
		];

		return Procaptcha::form( $args );
	}
}
