/* global jQuery, ProcaptchaReset */

const wc = function( $ ) {
	function reset() {
		ProcaptchaReset( document.querySelector( 'form.woocommerce-checkout' ) );
	}

	$( document.body ).on( 'checkout_error', function() {
		reset();
	} );

	$( document.body ).on( 'updated_checkout', function() {
		window.ProcaptchaBindEvents();
		reset();
	} );
};

window.ProcaptchaWC = wc;

jQuery( document ).ready( wc );
