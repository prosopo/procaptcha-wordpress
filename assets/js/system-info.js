/* global ProcaptchaSystemInfoObject */

document.addEventListener( 'DOMContentLoaded', function() {
	document.querySelector( '#procaptcha-system-info-wrap .helper' ).addEventListener(
		'click',
		function() {
			const systemInfoTextArea = document.getElementById( 'procaptcha-system-info' );

			navigator.clipboard.writeText( systemInfoTextArea.value ).then(
				() => {
					// Clipboard successfully set.
				},
				() => {
					// Clipboard write failed.
				},
			);

			// noinspection JSUnresolvedVariable
			const message = ProcaptchaSystemInfoObject.copiedMsg;

			// eslint-disable-next-line no-alert
			alert( message );
		},
	);
} );
