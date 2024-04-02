const acfe = function() {
	function procap_ACFECallback( response, callback ) {
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

	function procap_ACFEOnLoad() {
		window.procap_OnLoad = procap_ACFEOnLoadSaved;
		window.procap_OnLoad();
	}

	const params = window.procap_.getParams();
	const savedCallback = params.callback;
	const savedErrorCallback = params[ 'error-callback' ];
	const savedExpiredCallback = params[ 'expired-callback' ];

	params.callback = ( response ) => {
		procap_ACFECallback( response, savedCallback );
	};
	params[ 'error-callback' ] = () => {
		procap_ACFECallback( '', savedErrorCallback );
	};
	params[ 'expired-callback' ] = () => {
		procap_ACFECallback( '', savedExpiredCallback );
	};

	window.procap_.setParams( params );

	const procap_ACFEOnLoadSaved = window.procap_OnLoad;

	window.procap_OnLoad = procap_ACFEOnLoad;
};

window.procap_ACFE = acfe;

acfe();
