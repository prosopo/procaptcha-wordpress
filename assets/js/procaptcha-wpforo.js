/* global jQuery */

const procap_WPForo = function( $ ) {
	$( '.wpforo-section .add_wpftopic:not(.not_reg_user)' ).click( function() {
		window.procap_BindEvents();
	} );
};

window.procap_WPForo = procap_WPForo;

jQuery( document ).ready( procap_WPForo );
