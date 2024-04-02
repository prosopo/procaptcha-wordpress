/* global jQuery */

const wc = function( $ ) {
	$( document.body ).on( 'checkout_error', () => window.procaptchaBindEvents() );
	$( document.body ).on( 'updated_checkout', () => window.procaptchaBindEvents() );
};

window.procaptchaWC = wc;

jQuery( document ).ready( wc );
