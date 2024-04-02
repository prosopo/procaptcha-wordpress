/* global jQuery */

( function( $ ) {
	$( 'a.sdm_download' ).on( 'click', function( e ) {
		e.preventDefault();

		let location = e.target.href;
		const $item = $( e.target ).closest( 'div.sdm_download_item ' );
		const nonce = $item.find( '#procaptcha_simple_download_monitor_nonce' ).val();
		const response = $item.find( '[name="procaptcha-response"]' ).val();

		location += '&procaptcha_simple_download_monitor_nonce=' + nonce + '&procaptcha-response=' + response;

		window.location.href = location;
	} );
}( jQuery ) );
