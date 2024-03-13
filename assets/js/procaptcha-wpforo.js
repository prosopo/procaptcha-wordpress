/* global jQuery */

const ProCaptchaWPForo = function( $ ) {
	$( '.wpforo-section .add_wpftopic:not(.not_reg_user)' ).click( function() {
		window.ProCaptchaBindEvents();
	} );
};

window.ProCaptchaWPForo = ProCaptchaWPForo;

jQuery( document ).ready( ProCaptchaWPForo );
