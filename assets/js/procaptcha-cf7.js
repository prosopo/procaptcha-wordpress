/* global ProcaptchaReset */

document.addEventListener( 'DOMContentLoaded', function() {
	/**
	 * Reset procaptcha widget.
	 *
	 * @param {CustomEvent} event Event.
	 */
	const ProcaptchaResetCF7 = function( event ) {
		ProcaptchaReset( event.target );
	};

	[ ...document.querySelectorAll( '.wpcf7' ) ].map( ( form ) => {
		form.addEventListener( 'wpcf7invalid', ProcaptchaResetCF7, false );
		form.addEventListener( 'wpcf7spam', ProcaptchaResetCF7, false );
		form.addEventListener( 'wpcf7mailsent', ProcaptchaResetCF7, false );
		form.addEventListener( 'wpcf7mailfailed', ProcaptchaResetCF7, false );
		form.addEventListener( 'wpcf7submit', ProcaptchaResetCF7, false );

		return form;
	} );
} );
