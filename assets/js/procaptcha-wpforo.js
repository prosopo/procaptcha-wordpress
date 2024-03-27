/* global jQuery */

const ProcaptchaWPForo = function( $ ) {
	$( '.wpforo-section .add_wpftopic:not(.not_reg_user)' ).click( function() {
		window.ProcaptchaBindEvents();
	} );
};

window.ProcaptchaWPForo = ProcaptchaWPForo;

jQuery( document ).ready( ProcaptchaWPForo );
