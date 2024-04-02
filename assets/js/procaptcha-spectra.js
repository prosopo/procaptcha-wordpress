const { fetch: originalFetch } = window;

// Intercept Spectra form fetch to add procaptcha data.
window.fetch = async ( ...args ) => {
	const [ resource, config ] = args;

	// @param {FormData} body
	const body = config.body;
	const blockId = body.get( 'block_id' );
	const inputName = 'procaptcha-response';
	const widgetName = 'procaptcha-widget-id';
	const nonceName = 'procaptcha_spectra_form_nonce';
	const formData = JSON.parse( body.get( 'form_data' ) );

	if ( 'uagb_process_forms' === body.get( 'action' ) && ! formData.hasOwnProperty( inputName ) ) {
		const procaptchaResponse = document.querySelector( '.uagb-block-' + blockId + ' [name="' + inputName + '"]' );
		const id = document.querySelector( '.uagb-block-' + blockId + ' [name="' + widgetName + '"]' );
		const nonce = document.querySelector( '.uagb-block-' + blockId + ' [name="' + nonceName + '"]' );

		if ( procaptchaResponse ) {
			formData[ inputName ] = procaptchaResponse.value;
		}

		if ( id ) {
			formData[ widgetName ] = id.value;
		}

		formData[ nonceName ] = nonce.value;

		body.set( 'form_data', JSON.stringify( formData ) );
		config.body = body;
	}

	// noinspection JSCheckFunctionSignatures
	return await originalFetch( resource, config );
};
