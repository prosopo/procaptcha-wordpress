/* global jQuery */

( function( $ ) {
	// noinspection JSCheckFunctionSignatures
	$.ajaxPrefilter( function( options ) {
		const data = options.data ?? '';
		let nonceName = '';

		if ( data.startsWith( 'action=mailpoet' ) ) {
			nonceName = 'procaptcha_mailpoet_nonce';
		}

		if ( ! nonceName ) {
			return;
		}

		const urlParams = new URLSearchParams( data );
		const formId = urlParams.get( 'data[form_id]' );
		const $form = $( 'input[name="data[form_id]"][value=' + formId + ']' ).parent( 'form' );
		let response = $form.find( '[name="procaptcha-response"]' ).val();
		response = response ? response : '';
		let id = $form.find( '[name="procaptcha-widget-id"]' ).val();
		id = id ? id : '';
		let nonce = $form.find( '[name="' + nonceName + '"]' ).val();
		nonce = nonce ? nonce : '';
		options.data +=
			'&procaptcha-response=' + response + '&procaptcha-widget-id=' + id + '&' + nonceName + '=' + nonce;
	} );
}( jQuery ) );
