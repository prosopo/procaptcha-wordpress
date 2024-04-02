/**
 * Ninja Forms controller file.
 */

/* global procaptcha, Marionette, Backbone */

// On Document Ready.
document.addEventListener( 'DOMContentLoaded', function() {
	const ProcaptchaFieldController = Marionette.Object.extend( {
		initialize() {
			// On the Form Submission's field validation.
			const submitChannel = Backbone.Radio.channel( 'submit' );
			this.listenTo( submitChannel, 'validate:field', this.updateHcaptcha );
			this.listenTo( submitChannel, 'validate:field', this.updateHcaptcha );

			// On the Field's model value change.
			const fieldsChannel = Backbone.Radio.channel( 'fields' );
			this.listenTo( fieldsChannel, 'change:modelValue', this.updateHcaptcha );
		},

		updateHcaptcha( model ) {
			// Only validate a specific fields type.
			if ( 'procaptcha-for-ninja-forms' !== model.get( 'type' ) ) {
				return;
			}

			// Check if the Model has a value.
			if ( model.get( 'value' ) ) {
				// Remove Error from Model.
				Backbone.Radio.channel( 'fields' ).request(
					'remove:error',
					model.get( 'id' ),
					'required-error'
				);
			} else {
				const fieldId = model.get( 'id' );
				const widget = document.querySelector( '.procaptcha[data-fieldId="' + fieldId + '"] iframe' );

				if ( ! widget ) {
					return;
				}

				const widgetId = widget.dataset.procaptchaWidgetId;
				const hcapResponse = procaptcha.getResponse( widgetId );
				model.set( 'value', hcapResponse );
			}
		},
	} );

	// Instantiate our custom field's controller, defined above.
	window.procap_FieldController = new ProcaptchaFieldController();
} );

/* global jQuery */

( function( $ ) {
	// noinspection JSCheckFunctionSignatures
	$.ajaxPrefilter( function( options ) {
		const data = options.data ?? '';

		if ( ! data.startsWith( 'action=nf_ajax_submit' ) ) {
			return;
		}

		const urlParams = new URLSearchParams( data );
		const formId = JSON.parse( urlParams.get( 'formData' ) ).id;
		const $form = $( '#nf-form-' + formId + '-cont' );
		let id = $form.find( '[name="procaptcha-widget-id"]' ).val();
		id = id ? id : '';
		options.data += '&procaptcha-widget-id=' + id;
	} );
}( jQuery ) );
