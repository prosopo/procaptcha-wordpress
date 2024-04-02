/* global jQuery */

( function( $ ) {
	// noinspection JSCheckFunctionSignatures
	$.ajaxPrefilter( function( options ) {
		const data = options.data ?? '';
		let nonceName = '';

		if ( data.startsWith( 'action=fl_builder_email' ) ) {
			nonceName = 'procaptcha_beaver_builder_nonce';
		}

		if ( data.startsWith( 'action=fl_builder_login_form_submit' ) ) {
			nonceName = 'procaptcha_login_nonce';
		}

		if ( ! nonceName ) {
			return;
		}

		const urlParams = new URLSearchParams( data );
		const nodeId = urlParams.get( 'node_id' );
		const $node = $( '[data-node=' + nodeId + ']' );
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
