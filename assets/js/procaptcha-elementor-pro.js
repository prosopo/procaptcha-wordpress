/* global jQuery, procaptchaBindEvents, elementorFrontend */

jQuery( document ).on( 'ajaxSuccess', function( event, xhr, settings ) {
	const params = new URLSearchParams( settings.data );

	if ( params.get( 'action' ) !== 'elementor_pro_forms_send_form' ) {
		return;
	}

	const formId = params.get( 'form_id' );
	const form = jQuery( 'input[name="form_id"][value="' + formId + '"]' ).closest( 'form' );

	window.procaptchaReset( form[ 0 ] );
} );

const procaptchaElementorPro = function() {
	if ( 'undefined' === typeof elementorFrontend ) {
		return;
	}

	elementorFrontend.hooks.addAction(
		'frontend/element_ready/widget',
		function( $scope ) {
			if ( $scope[ 0 ].classList.contains( 'elementor-widget-form' ) ) {
				// Elementor reinserts element during editing, so we need to bind events again.
				procaptchaBindEvents();
			}
		}
	);
};

window.procaptchaElementorPro = procaptchaElementorPro;

jQuery( document ).ready( procaptchaElementorPro );
