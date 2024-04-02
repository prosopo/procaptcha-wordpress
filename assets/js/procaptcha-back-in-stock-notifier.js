/* global jQuery */

( function( $ ) {
	// noinspection JSCheckFunctionSignatures
	$.ajaxPrefilter( function( options ) {
		const data = options.data ?? '';

		if ( ! data.startsWith( 'action=cwginstock_product_subscribe' ) ) {
			return;
		}

		const nonceName = 'procaptcha_back_in_stock_notifier_nonce';
		const $node = $( '.cwginstock-subscribe-form' );
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

jQuery( document ).on( 'ajaxSuccess', function( event, xhr, settings ) {
	const params = new URLSearchParams( settings.data );

	if ( params.get( 'action' ) !== 'cwg_trigger_popup_ajax' ) {
		return;
	}

	const input = document.querySelector( 'input[name="cwg-product-id"][value="' + params.get( 'product_id' ) + '"]' );

	if ( ! input ) {
		return;
	}

	window.procaptchaBindEvents();
} );
