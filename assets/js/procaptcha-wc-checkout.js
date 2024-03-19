/* global jQuery, ProCaptchaReset */

const wc = function( $ ) {
	function reset() {
		ProCaptchaReset( document.querySelector( 'form.woocommerce-checkout' ) );
	}

	$( document.body ).on( 'checkout_error', function() {
		reset();
	} );

	$( document.body ).on( 'updated_checkout', function() {
		window.ProCaptchaBindEvents();
		reset();
	} );
};

window.ProCaptchaWC = wc;

jQuery( document ).ready( wc );
