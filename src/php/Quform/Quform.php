<?php
/**
 * Quform class file.
 *
 * @package procaptcha-wp
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */

namespace Procaptcha\Quform;

use Procaptcha\Helpers\Procaptcha;
use Quform_Element_Field;
use Quform_Element_Page;
use Quform_Form;

/**
 * Class Quform.
 */
class Quform {

	/**
	 * Verify action.
	 */
	const ACTION = 'procaptcha_quform';

	/**
	 * Verify nonce.
	 */
	const NONCE = 'procaptcha_quform_nonce';

	/**
	 * Script handle.
	 */
	const HANDLE = 'procaptcha-quform';

	/**
	 * Admin script handle.
	 */
	const ADMIN_HANDLE = 'admin-quform';

	/**
	 * Script localization object.
	 */
	const OBJECT = 'ProcaptchaQuformObject';

	/**
	 * Max form element id.
	 */
	const MAX_ID = '9999_9999';

	/**
	 * Quform constructor.
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
		add_filter( 'do_shortcode_tag', [ $this, 'add_procaptcha' ], 10, 4 );
		add_filter( 'quform_pre_validate', [ $this, 'verify' ], 10, 2 );
		add_filter( 'quform_element_valid', [ $this, 'element_valid' ], 10, 3 );
		add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_scripts' ], 9 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
	}

	/**
	 * Filters the output created by a shortcode callback and adds procaptcha.
	 *
	 * @param string|mixed $output Shortcode output.
	 * @param string       $tag    Shortcode name.
	 * @param array|string $attr   Shortcode attributes array or empty string.
	 * @param array        $m      Regular expression match array.
	 *
	 * @return string|mixed
	 */
	public function add_procaptcha( $output, string $tag, $attr, array $m ) {
		if ( 'quform' !== $tag ) {
			return $output;
		}

		$output  = (string) $output;
		$form_id = (int) $attr['id'];

		if ( false !== strpos( $output, 'quform-procaptcha' ) ) {
			return $this->replace_procaptcha( $output, $form_id );
		}

		$max_id = self::MAX_ID;

		if ( preg_match_all( '/quform-element-(\d+?)_(\d+)\D/', $output, $m ) ) {
			$element_ids = array_map( 'intval', array_unique( $m[2] ) );
			$max_id      = $m[1][0] . '_' . ( max( $element_ids ) + 1 );
		}

		ob_start();
		?>
		<div class="quform-element quform-element-procaptcha quform-element-<?php echo esc_attr( $max_id ); ?> quform-cf quform-element-required quform-procaptcha-no-size">
			<div class="quform-spacer">
				<div class="quform-inner quform-inner-procaptcha quform-inner-<?php echo esc_attr( $max_id ); ?>">
					<div class="quform-input quform-input-procaptcha quform-input-<?php echo esc_attr( $max_id ); ?> quform-cf">
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $this->get_procaptcha( $form_id );
						?>
						<noscript><?php esc_html_e( 'Please enable JavaScript to submit this form.', 'procaptcha-wordpress' ); ?></noscript>
					</div>
				</div>
			</div>
		</div>
		<?php
		$procaptcha = ob_get_clean();

		return (string) preg_replace(
			'/(<div class="quform-element quform-element-submit)/',
			$procaptcha . '$1',
			$output
		);
	}

	/**
	 * Replace embedded procaptcha.
	 *
	 * @param string $output  Form output.
	 * @param int    $form_id Form id.
	 *
	 * @return string
	 * @noinspection HtmlUnknownAttribute
	 */
	private function replace_procaptcha( string $output, int $form_id ): string {
		return (string) preg_replace(
			'#<div class="quform-procaptcha"(.+?)>(.*?)</div>#',
			'<div class="quform-procaptcha"$1>' . $this->get_procaptcha( $form_id ) . '</div>',
			$output
		);
	}

	/**
	 * Verify.
	 *
	 * @param array|mixed $result Result.
	 * @param Quform_Form $form   Form.
	 *
	 * @return array|mixed
	 */
	public function verify( $result, Quform_Form $form ) {
		$page           = $form->getCurrentPage();
		$page_id        = $page ? $page->getId() : 0;
		$procaptcha_name  = $this->get_element_id( $page );
		$procaptcha_error = [
			'type'   => 'error',
			'error'  =>
				[
					'enabled' => false,
					'title'   => '',
					'content' => '',
				],
			'errors' => [ $procaptcha_name => '' ],
			'page'   => $page_id,
		];

		$error_message = procaptcha_get_verify_message(
			self::NONCE,
			self::ACTION
		);

		if ( null !== $error_message ) {
			$procaptcha_error['errors'] = [ $procaptcha_name => $error_message ];

			return $procaptcha_error;
		}

		return $result;
	}

	/**
	 * Fix Quform bug with procaptcha.
	 * Validate procaptcha element.
	 *
	 * @param bool|mixed           $valid   Element is valid.
	 * @param string               $value   Value.
	 * @param Quform_Element_Field $element Element instance.
	 *
	 * @return bool|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function element_valid( $valid, string $value, Quform_Element_Field $element ) {
		$config = $element->config();

		if ( ! $this->is_procaptcha_element( $config ) ) {
			return $valid;
		}

		return true;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_dequeue_script( 'quform-procaptcha' );
		wp_deregister_script( 'quform-procaptcha' );

		$min = procaptchamin_suffix();

		wp_enqueue_script(
			self::HANDLE,
			PROCAPTCHA_URL . "/assets/js/procaptcha-quform$min.js",
			[ 'jquery' ],
			PROCAPTCHA_VERSION,
			true
		);
	}

	/**
	 * Enqueue script in admin.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		if ( ! $this->is_quform_admin_page() ) {
			return;
		}

		$min = procaptchamin_suffix();

		wp_enqueue_script(
			self::ADMIN_HANDLE,
			constant( 'PROCAPTCHA_URL' ) . "/assets/js/admin-quform$min.js",
			[ 'jquery' ],
			constant( 'PROCAPTCHA_VERSION' ),
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
	}

	/**
	 * Whether we are on the Quform admin pages.
	 *
	 * @return bool
	 */
	private function is_quform_admin_page(): bool {
		if ( ! is_admin() ) {
			return false;
		}

		$screen = get_current_screen();

		if ( ! $screen ) {
			return false;
		}

		$quform_admin_pages = [
			'forms_page_quform.settings',
			'forms_page_quform.forms',
		];

		return in_array( $screen->id, $quform_admin_pages, true );
	}

	/**
	 * Get element id in the form.
	 *
	 * @param Quform_Element_Page|null $page Current page.
	 *
	 * @return string
	 * @noinspection PhpMissingParamTypeInspection
	 */
	private function get_element_id( $page ): string {
		$id = self::MAX_ID;

		if ( null === $page ) {
			return $id;
		}

		$quform_elements = $page->getElements();

		foreach ( $quform_elements as $quform_element ) {
			$config = $quform_element->config();
			if ( $this->is_procaptcha_element( $config ) ) {
				return isset( $config['id'] ) ? $page->getForm()->getId() . '_' . $config['id'] : $id;
			}
		}

		$ids = array_map(
			static function ( $element ) {
				return $element->getId();
			},
			$quform_elements
		);

		return $page->getForm()->getId() . '_' . ( max( $ids ) + 1 );
	}

	/**
	 * Check if it is procaptcha element.
	 *
	 * @param mixed $config Element config.
	 *
	 * @return bool
	 */
	private function is_procaptcha_element( $config ): bool {
		return (
			isset( $config['type'], $config['provider'] ) &&
			'recaptcha' === $config['type'] &&
			'procaptcha' === $config['provider']
		);
	}

	/**
	 * Get procaptcha.
	 *
	 * @param int $form_id Form id.
	 *
	 * @return string
	 */
	private function get_procaptcha( int $form_id ): string {
		$args = [
			'action' => self::ACTION,
			'name'   => self::NONCE,
			'id'     => [
				'source'  => Procaptcha::get_class_source( static::class ),
				'form_id' => $form_id,
			],
		];

		return Procaptcha::form( $args );
	}
}
