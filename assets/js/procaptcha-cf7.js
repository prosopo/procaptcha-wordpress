/* global procap_Reset */

document.addEventListener( 'DOMContentLoaded', function() {
	/**
	 * Reset procap_ widget.
	 *
	 * @param {CustomEvent} event Event.
	 */
	const procap_ResetCF7 = function( event ) {
		procap_Reset( event.target );
	};

	[ ...document.querySelectorAll( '.wpcf7' ) ].map( ( form ) => {
		form.addEventListener( 'wpcf7invalid', procap_ResetCF7, false );
		form.addEventListener( 'wpcf7spam', procap_ResetCF7, false );
		form.addEventListener( 'wpcf7mailsent', procap_ResetCF7, false );
		form.addEventListener( 'wpcf7mailfailed', procap_ResetCF7, false );
		form.addEventListener( 'wpcf7submit', procap_ResetCF7, false );

		return form;
	} );
} );
