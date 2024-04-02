/* global jQuery */

const wc = function( $ ) {
	$( document.body ).on( 'checkout_error', () => window.procap_BindEvents() );
	$( document.body ).on( 'updated_checkout', () => window.procap_BindEvents() );
};

window.procap_WC = wc;

jQuery( document ).ready( wc );
