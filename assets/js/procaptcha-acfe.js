const acfe = function() {
	function ProcaptchaACFECallback( response, callback ) {
		[
			...document.querySelectorAll(
				'.acfe-field-recaptcha input[type="hidden"]'
			),
		].map( ( el ) => {
			el.value = response;
			return el;
		} );

		if ( callback !== undefined ) {
			callback( response );
		}
	}

	function ProcaptchaACFEOnLoad() {
		window.ProcaptchaOnLoad = ProcaptchaACFEOnLoadSaved;
		window.ProcaptchaOnLoad();
	}

	const params = window.procaptchawp.getParams();
	const savedCallback = params.callback;
	const savedErrorCallback = params[ 'error-callback' ];
	const savedExpiredCallback = params[ 'expired-callback' ];

	params.callback = ( response ) => {
		ProcaptchaACFECallback( response, savedCallback );
	};
	params[ 'error-callback' ] = () => {
		ProcaptchaACFECallback( '', savedErrorCallback );
	};
	params[ 'expired-callback' ] = () => {
		ProcaptchaACFECallback( '', savedExpiredCallback );
	};

	window.procaptchawp.setParams( params );

	const ProcaptchaACFEOnLoadSaved = window.ProcaptchaOnLoad;

	window.ProcaptchaOnLoad = ProcaptchaACFEOnLoad;
};

window.ProcaptchaACFE = acfe;

acfe();
