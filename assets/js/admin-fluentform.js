/* global jQuery, ProcaptchaFluentFormObject */

/**
 * @param ProcaptchaFluentFormObject.noticeLabel
 * @param ProcaptchaFluentFormObject.noticeDescription
 */
jQuery( document ).ready( function( $ ) {
	if ( ! window.location.href.includes( 'page=fluent_forms_settings' ) ) {
		return;
	}

	const $procaptchaWrap = $( '.ff_procaptcha_wrap' );

	$procaptchaWrap.find( '.ff_card_head h5' )
		.html( ProcaptchaFluentFormObject.noticeLabel ).css( 'display', 'block' );
	$procaptchaWrap.find( '.ff_card_head p' ).first()
		.html( ProcaptchaFluentFormObject.noticeDescription ).css( 'display', 'block' );
} );
