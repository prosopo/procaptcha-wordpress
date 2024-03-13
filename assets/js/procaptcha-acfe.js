const acfe = function() {
	function ProCaptchaACFECallback( response, callback ) {
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

	function ProCaptchaACFEOnLoad() {
		window.ProCaptchaOnLoad = ProCaptchaACFEOnLoadSaved;
		window.ProCaptchaOnLoad();
	}

	const params = window.procaptcha.getParams();
	const savedCallback = params.callback;
	const savedErrorCallback = params[ 'error-callback' ];
	const savedExpiredCallback = params[ 'expired-callback' ];

	params.callback = ( response ) => {
		ProCaptchaACFECallback( response, savedCallback );
	};
	params[ 'error-callback' ] = () => {
		ProCaptchaACFECallback( '', savedErrorCallback );
	};
	params[ 'expired-callback' ] = () => {
		ProCaptchaACFECallback( '', savedExpiredCallback );
	};

	window.procaptcha.setParams( params );

	const ProCaptchaACFEOnLoadSaved = window.ProCaptchaOnLoad;

	window.ProCaptchaOnLoad = ProCaptchaACFEOnLoad;
};

window.ProCaptchaACFE = acfe;

acfe();
