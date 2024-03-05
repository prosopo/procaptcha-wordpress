/* global gform, GetFieldsByType, PROCAPTCHAGravityFormsObject */

/**
 * @param PROCAPTCHAGravityFormsObject.onlyOne
 */

window.SetDefaultValues_procaptcha = function( field ) {
	field.inputs = null;
	field.displayOnly = true;
	field.label = 'procaptcha';
	field.labelPlacement = 'hidden_label';

	return field;
};

document.addEventListener( 'DOMContentLoaded', function() {
	gform.addFilter(
		'gform_form_editor_can_field_be_added', ( value, type ) => {
			if ( type === 'procaptcha' && GetFieldsByType( [ 'procaptcha' ] ).length > 0 ) {
				// eslint-disable-next-line no-alert
				alert( PROCAPTCHAGravityFormsObject.onlyOne );
				return false;
			}

			return value;
		} );
} );
