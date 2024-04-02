/* global procaptchaReset */

document.addEventListener( 'DOMContentLoaded', function() {
	/**
	 * Reset procaptcha widget.
	 *
	 * @param {CustomEvent} event Event.
	 */
	const procaptchaResetCF7 = function( event ) {
		procaptchaReset( event.target );
	};

	[ ...document.querySelectorAll( '.wpcf7' ) ].map( ( form ) => {
		form.addEventListener( 'wpcf7invalid', procaptchaResetCF7, false );
		form.addEventListener( 'wpcf7spam', procaptchaResetCF7, false );
		form.addEventListener( 'wpcf7mailsent', procaptchaResetCF7, false );
		form.addEventListener( 'wpcf7mailfailed', procaptchaResetCF7, false );
		form.addEventListener( 'wpcf7submit', procaptchaResetCF7, false );

		return form;
	} );
} );
