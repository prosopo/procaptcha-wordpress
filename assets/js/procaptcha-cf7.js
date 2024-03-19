/* global ProCaptchaReset */

document.addEventListener( 'DOMContentLoaded', function() {
	/**
	 * Reset procaptcha widget.
	 *
	 * @param {CustomEvent} event Event.
	 */
	const ProCaptchaResetCF7 = function( event ) {
		ProCaptchaReset( event.target );
	};

	[ ...document.querySelectorAll( '.wpcf7' ) ].map( ( form ) => {
		form.addEventListener( 'wpcf7invalid', ProCaptchaResetCF7, false );
		form.addEventListener( 'wpcf7spam', ProCaptchaResetCF7, false );
		form.addEventListener( 'wpcf7mailsent', ProCaptchaResetCF7, false );
		form.addEventListener( 'wpcf7mailfailed', ProCaptchaResetCF7, false );
		form.addEventListener( 'wpcf7submit', ProCaptchaResetCF7, false );

		return form;
	} );
} );
