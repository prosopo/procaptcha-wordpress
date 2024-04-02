/* global jQuery, ProcaptchaFormidableFormsObject */

/**
 * @param ProcaptchaFluentFormObject.noticeLabel
 * @param ProcaptchaFluentFormObject.noticeDescription
 */
jQuery( document ).ready( function( $ ) {
	if ( ! window.location.href.includes( 'page=formidable-settings' ) ) {
		return;
	}

	const $howTo = $( '#procaptcha_settings .howto' );

	$howTo.html( ProcaptchaFormidableFormsObject.noticeLabel );
	$( '<p class="howto">' + ProcaptchaFormidableFormsObject.noticeDescription + '</p>' ).insertAfter( $howTo );

	$( '#procaptcha_settings input' ).attr( {
		disabled: true,
		class: 'frm_noallow',
	} );
} );
