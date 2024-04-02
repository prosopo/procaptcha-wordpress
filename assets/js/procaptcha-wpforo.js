/* global jQuery */

const procaptchaWPForo = function( $ ) {
	$( '.wpforo-section .add_wpftopic:not(.not_reg_user)' ).click( function() {
		window.procaptchaBindEvents();
	} );
};

window.procaptchaWPForo = procaptchaWPForo;

jQuery( document ).ready( procaptchaWPForo );
