<?php
/**
 * Base class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\UM;

use Procaptcha\Abstracts\LoginBase;
use Procaptcha\Helpers\Procaptcha;

/**
 * Class Base
 */
abstract class Base extends LoginBase {

	/**
	 * Field key.
	 */
	const KEY = 'procaptcha';

	/**
	 * UM action.
	 */
	const UM_ACTION = '';

	/**
	 * UM mode.
	 */
	const UM_MODE = '';

	/**
	 * Field key.
	 *
	 * @var string
	 */
	private $key;

	/**
	 * UM mode.
	 *
	 * @var string
	 */
	private $um_mode;

	/**
	 * The procaptcha action.
	 *
	 * @var string
	 */
	private $procaptcha_action;

	/**
	 * Form id.
	 *
	 * @var int
	 */
	protected $form_id = 0;

	/**
	 * The procaptcha nonce.
	 *
	 * @var string
	 */
	private $procaptcha_nonce;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->key             = self::KEY;
		$this->um_mode         = static::UM_MODE;
		$this->procaptcha_action = "procaptcha_um_$this->um_mode";
		$this->procaptcha_nonce  = "procaptcha_um_{$this->um_mode}_nonce";

		parent::__construct();
	}

	/**
	 * Init hooks.
	 */
	protected function init_hooks() {
		$mode = static::UM_MODE;

		add_action( "um_main_{$mode}_fields", [ $this, 'set_form_id' ] );
		add_filter( 'um_get_form_fields', [ $this, 'add_um_captcha' ], 100 );
		add_filter( "um_{$this->key}_form_edit_field", [ $this, 'display_captcha' ], 10, 2 );
		add_action( static::UM_ACTION, [ $this, 'verify' ], 10, 2 );
	}

	/**
	 * Set form id.
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 */
	public function set_form_id( array $args ) {
		$this->form_id = isset( $args['form_id'] ) ? (int) $args['form_id'] : 0;
	}

	/**
	 * Add procaptcha to form fields.
	 *
	 * @param array|mixed $fields Form fields.
	 *
	 * @return array|mixed
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function add_um_captcha( $fields ) {
		$um = UM();

		if ( ! $um ) {
			return $fields;
		}

		if ( static::UM_MODE !== $um->fields()->set_mode ) {
			return $fields;
		}

		$fields       = $fields ? (array) $fields : [];
		$max_position = 0;
		$in_row       = '_um_row_1';
		$in_sub_row   = '0';
		$in_column    = '1';
		$in_group     = '';

		foreach ( $fields as $field ) {
			if ( ! isset( $field['position'] ) ) {
				continue;
			}

			if ( $field['position'] <= $max_position ) {
				continue;
			}

			$max_position = $field['position'];
			$in_row       = $field['in_row'];
			$in_sub_row   = $field['in_sub_row'];
			$in_column    = $field['in_column'];
			$in_group     = $field['in_group'];
		}

		$fields[ self::KEY ] = [
			'title'        => __( 'procaptcha', 'procaptcha-wordpress' ),
			'metakey'      => self::KEY,
			'type'         => self::KEY,
			'label'        => __( 'procaptcha', 'procaptcha-wordpress' ),
			'required'     => 0,
			'public'       => 0,
			'editable'     => 0,
			'account_only' => true,
			'position'     => (string) ( $max_position + 1 ),
			'in_row'       => $in_row,
			'in_sub_row'   => $in_sub_row,
			'in_column'    => $in_column,
			'in_group'     => $in_group,
		];

		return $fields;
	}

	/**
	 * Display procaptcha.
	 *
	 * @param string|mixed $output Output.
	 * @param string       $mode   Mode.
	 *
	 * @return string|mixed
	 * @noinspection PhpUndefinedFunctionInspection
	 * @noinspection PhpUndefinedConstantInspection
	 * @noinspection PhpParamsInspection
	 */
	public function display_captcha( $output, string $mode ) {
		if ( $this->um_mode !== $mode || '' !== $output ) {
			return $output;
		}

		$output = "<div class=\"um-field um-field-$this->key\">";

		$args = [
			'action' => $this->procaptcha_action,
			'name'   => $this->procaptcha_nonce,
			'id'     => [
				'source'  => Procaptcha::get_class_source( static::class ),
				'form_id' => $this->form_id ?: $mode,
			],
		];

		$output .= Procaptcha::form( $args );
		$output .= '</div>';

		$um = UM();

		if ( ! $um ) {
			return $output;
		}

		$fields = $um->fields();

		if ( $fields->is_error( self::KEY ) ) {
			if ( version_compare( UM_VERSION, '2.7.0', '<' ) ) {
				$output .= $fields->field_error( $fields->show_error( self::KEY ) );
			} else {
				$output .= $fields->field_error( $fields->show_error( self::KEY ), self::KEY );
			}
		}

		return $output;
	}

	/**
	 * Verify procaptcha.
	 *
	 * @param array $submitted_data Submitted data.
	 * @param array $form_data      Form data.
	 *
	 * @return void
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function verify( array $submitted_data, array $form_data = [] ) {
		$um = UM();

		if (
			! $um ||
			( isset( $form_data['mode'] ) && $this->um_mode !== $form_data['mode'] )
		) {
			return;
		}

		$error_message = procaptcha_get_verify_message(
			$this->procaptcha_nonce,
			$this->procaptcha_action
		);

		if ( null === $error_message ) {
			return;
		}

		$um->form()->add_error( self::KEY, $error_message );
	}
}
