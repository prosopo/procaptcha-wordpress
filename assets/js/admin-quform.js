/* global jQuery, ProcaptchaQuformObject */

/**
 * @param ProcaptchaQuformObject.noticeLabel
 * @param ProcaptchaQuformObject.noticeDescription
 */
jQuery( document ).ready( function( $ ) {
	if ( ! window.location.href.includes( 'page=quform.settings' ) ) {
		return;
	}

	const settingSelector = '.qfb-setting';
	const $procaptchaHeading = $( '.qfb-icon.qfb-icon-hand-paper-o' ).closest( '.qfb-settings-heading' );

	const html = $procaptchaHeading.html();
	const text = $procaptchaHeading.text();

	$procaptchaHeading.html( html.replace( text, ProcaptchaQuformObject.noticeLabel ) );
	$procaptchaHeading
		.next( 'p' ).html( ProcaptchaQuformObject.noticeDescription )
		.next( settingSelector ).hide()
		.next( settingSelector ).hide();
} );

jQuery( document ).ready( function( $ ) {
	const blockProcaptchaSettings = () => {
		if ( $provider.val() === 'procaptcha' ) {
			$size.hide();
			$theme.hide();
			$lang.hide();

			// Remove label and description which can be here from the previous opening of the captcha field settings panel.
			$( '.' + noticeLabelClass ).remove();
			$( '.' + noticeDescriptionClass ).remove();

			$( descriptionHtml ).insertAfter( $provider );
			$( labelHtml ).insertAfter( $provider );
		} else {
			$size.show();
			$theme.show();
			$lang.show();

			$( '.' + noticeLabelClass ).remove();
			$( '.' + noticeDescriptionClass ).remove();
		}
	};

	const providerId = 'qfb_recaptcha_provider';
	const $provider = $( '#' + providerId );
	const settingSelector = '.qfb-setting';
	const $size = $( '#qfb_recaptcha_size' ).closest( settingSelector );
	const $theme = $( '#qfb_recaptcha_theme' ).closest( settingSelector );
	const $lang = $( '#qfb_procaptcha_lang' ).closest( settingSelector );
	const noticeLabelClass = 'procaptcha-notice-label';
	const noticeDescriptionClass = 'procaptcha-notice-description';
	const labelHtml = '<div class="qfb-setting-label ' + noticeLabelClass + '" style="float:none;">' +
		'<label>' + ProcaptchaQuformObject.noticeLabel + '</label></div>';
	const descriptionHtml = '<div class="qfb-setting-inner ' + noticeDescriptionClass + '">' +
		ProcaptchaQuformObject.noticeDescription + '</div>';

	if ( ! window.location.href.includes( 'page=quform.forms' ) ) {
		return;
	}

	// We need observer for the first opening of the captcha field settings panel.
	const observer = new MutationObserver( blockProcaptchaSettings );

	observer.observe(
		document.getElementById( providerId ).closest( settingSelector ),
		{
			attributes: true,
		}
	);

	$provider.on( 'change', blockProcaptchaSettings );
} );
