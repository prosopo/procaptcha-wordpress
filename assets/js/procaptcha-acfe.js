const acfe = function() {
	function procaptchaACFECallback( response, callback ) {
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

	function procaptchaACFEOnLoad() {
		window.procaptchaOnLoad = procaptchaACFEOnLoadSaved;
		window.procaptchaOnLoad();
	}

	const params = window.procaptcha.getParams();
	const savedCallback = params.callback;
	const savedErrorCallback = params[ 'error-callback' ];
	const savedExpiredCallback = params[ 'expired-callback' ];

	params.callback = ( response ) => {
		procaptchaACFECallback( response, savedCallback );
	};
	params[ 'error-callback' ] = () => {
		procaptchaACFECallback( '', savedErrorCallback );
	};
	params[ 'expired-callback' ] = () => {
		procaptchaACFECallback( '', savedExpiredCallback );
	};

	window.procaptcha.setParams( params );

	const procaptchaACFEOnLoadSaved = window.procaptchaOnLoad;

	window.procaptchaOnLoad = procaptchaACFEOnLoad;
};

window.procaptchaACFE = acfe;

acfe();
