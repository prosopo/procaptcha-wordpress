/* global jQuery */

( function( $ ) {
	// noinspection JSCheckFunctionSignatures
	$.ajaxPrefilter( function( options ) {
		const data = options.data ?? '';
		let nonceName = '';

		if ( data.startsWith( 'action=et_pb_submit_subscribe_form' ) ) {
			nonceName = 'procaptcha_divi_email_optin_nonce';
		}

		if ( ! nonceName ) {
			return;
		}

		const $node = $( '.et_pb_newsletter_form form' );
		let response = $node.find( '[name="procaptcha-response"]' ).val();
		response = response ? response : '';
		let id = $node.find( '[name="procaptcha-widget-id"]' ).val();
		id = id ? id : '';
		let nonce = $node.find( '[name="' + nonceName + '"]' ).val();
		nonce = nonce ? nonce : '';
		options.data +=
			'&procaptcha-response=' + response + '&procaptcha-widget-id=' + id + '&' + nonceName + '=' + nonce;
	} );
}( jQuery ) );
