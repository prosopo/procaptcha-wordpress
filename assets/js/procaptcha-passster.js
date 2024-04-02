/* global jQuery */

( function( $ ) {
	// noinspection JSCheckFunctionSignatures
	$.ajaxPrefilter( function( options ) {
		const data = options.data ?? '';

		if ( ! data.startsWith( 'action=validate_input' ) ) {
			return;
		}

		const urlParams = new URLSearchParams( data );
		const area = urlParams.get( 'area' );
		const $node = $( '[data-area=' + area + ']' ).closest( 'form' );
		const nonceName = 'procaptcha_passster_nonce';
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
