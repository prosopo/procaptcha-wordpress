<?php
/**
 * Field class file.
 *
 * @package procaptcha-wp
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUnused */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\NF;

use NF_Abstracts_Field;

/**
 * Class Field
 */
class Field extends NF_Abstracts_Field {

	// phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore

	/**
	 * Name.
	 *
	 * @var string
	 */
	protected $_name = 'procaptcha-for-ninja-forms';

	/**
	 * Type.
	 *
	 * @var string
	 */
	protected $_type = 'procaptcha';

	/**
	 * Section.
	 *
	 * @var string
	 */
	protected $_section = 'misc';

	/**
	 * Icon.
	 *
	 * @var string
	 */
	protected $_icon = 'hand-paper-o';

	/**
	 * Templates.
	 *
	 * @var string
	 */
	protected $_templates = 'procaptcha';

	/**
	 * Settings.
	 *
	 * @var string[]
	 */
	protected $_settings = [ 'label', 'classes' ];

	// phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore

	/**
	 * Fields constructor.
	 *
	 * @noinspection PhpDynamicFieldDeclarationInspection
	 */
	public function __construct() {
		parent::__construct();

		$this->_nicename = __( 'procap_', 'ninja-forms' );

		add_filter( 'nf_sub_hidden_field_types', [ $this, 'hide_field_type' ] );
	}

	/**
	 * Validate form.
	 *
	 * @param array|mixed $field Field.
	 * @param mixed       $data  Data.
	 *
	 * @return null|string
	 */
	public function validate( $field, $data ) {
		$value = $field['value'] ?? '';

		return procaptcha_request_verify( $value );
	}

	/**
	 * Hide the field type.
	 *
	 * @param array|mixed $hidden_field_types Field types.
	 *
	 * @return array
	 */
	public function hide_field_type( $hidden_field_types ): array {
		$hidden_field_types   = (array) $hidden_field_types;
		$hidden_field_types[] = $this->_name;

		return $hidden_field_types;
	}
}
