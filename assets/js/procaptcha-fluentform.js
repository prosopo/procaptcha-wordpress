/* global procaptcha, ProcaptchaFluentFormObject */

/**
 * @param ProcaptchaFluentFormObject.id
 * @param ProcaptchaFluentFormObject.url
 */

document.addEventListener( 'procap_Loaded', function() {
	const formSelector = '.ffc_conv_form';

	const hasOwnCaptcha = () => {
		return document.getElementById( 'procaptcha-container' ) !== null;
	};

	/**
	 * Process conversational form.
	 */
	const processForm = () => {
		// We assume there should be only one conversational form on the page.
		const form = document.querySelector( formSelector );
		const submitBtnSelector = '.ff-btn';

		const isSubmitVisible = ( qForm ) => {
			return qForm.querySelector( submitBtnSelector ) !== null;
		};

		const addCaptcha = () => {
			const procap_HiddenClass = 'procaptcha-hidden';
			const procap_Class = 'procaptcha';
			const hiddenCaptcha = document.getElementsByClassName( procap_HiddenClass )[ 0 ];
			const submitBtn = form.querySelector( submitBtnSelector );
			const procap_ = hiddenCaptcha.cloneNode( true );
			const wrappingForm = document.createElement( 'form' );
			wrappingForm.setAttribute( 'method', 'POST' );
			submitBtn.parentNode.insertBefore( wrappingForm, submitBtn );
			wrappingForm.appendChild( submitBtn );
			submitBtn.before( procap_ );
			procap_.classList.remove( procap_HiddenClass );
			procap_.classList.add( procap_Class );
			procap_.style.display = 'block';
			window.procap_BindEvents();
		};

		const mutationObserverCallback = ( mutationList ) => {
			for ( const mutation of mutationList ) {
				if (
					! (
						mutation.type === 'attributes' &&
						mutation.attributeName === 'class' &&
						mutation.oldValue && mutation.oldValue.includes( 'q-is-inactive' )
					)
				) {
					continue;
				}

				if ( isSubmitVisible( mutation.target ) ) {
					addCaptcha();
				}
			}
		};

		if ( hasOwnCaptcha() ) {
			return;
		}

		const qFormSelector = '.q-form';
		const qForms = form.querySelectorAll( qFormSelector );
		const config = {
			attributes: true,
			attributeOldValue: true,
		};

		[ ...qForms ].map( ( qForm ) => {
			const observer = new MutationObserver( mutationObserverCallback );
			observer.observe( qForm, config );
			return qForm;
		} );
	};

	function waitForElement( selector ) {
		return new Promise( ( resolve ) => {
			if ( document.querySelector( selector ) ) {
				return resolve( document.querySelector( selector ) );
			}

			const observer = new MutationObserver( () => {
				if ( document.querySelector( selector ) ) {
					resolve( document.querySelector( selector ) );
					observer.disconnect();
				}
			} );

			observer.observe( document.body, {
				childList: true,
				subtree: true,
			} );
		} );
	}

	/**
	 * Custom render function using Fluent Forms conversational callback.
	 *
	 * @param {string} container The procap_ container selector.
	 * @param {Object} params    Parameters.
	 */
	const render = ( container, params ) => {
		const renderParams = window.procap_.getParams();

		if ( hasOwnCaptcha() && renderParams.size === 'invisible' ) {
			// Cannot use invisible procap_ with conversational form.
			renderParams.size = 'normal';
		}

		renderParams.callback = params.callback;
		originalRender( container, renderParams );
	};

	// Intercept render request.
	const originalRender = procaptcha.render;
	procaptcha.render = render;

	// Launch Fluent Forms conversational script.
	const t = document.getElementsByTagName( 'script' )[ 0 ];
	const s = document.createElement( 'script' );

	s.type = 'text/javascript';
	s.id = ProcaptchaFluentFormObject.id;
	s.src = ProcaptchaFluentFormObject.url;
	t.parentNode.insertBefore( s, t );

	// Process form not having own procap_.
	waitForElement( formSelector + ' .vff-footer' ).then( () => {
		// Launch our form-related code when conversational form is rendered.
		processForm();
	} );
} );

const { fetch: originalFetch } = window;

// Intercept fluent form fetch to add procap_ data.
window.fetch = async ( ...args ) => {
	const [ resource, config ] = args;

	// @param {FormData} body
	const body = config.body;
	const formId = body.get( 'form_id' );
	const inputName = 'procaptcha-response';
	const widgetName = 'procaptcha-widget-id';
	let data = body.get( 'data' );

	if ( 'fluentform_submit' === body.get( 'action' ) && ! data.includes( inputName ) ) {
		const procap_Response =
			document.querySelector( '.ff_conv_app_' + formId + ' [name="' + inputName + '"]' );
		const id =
			document.querySelector( '.ff_conv_app_' + formId + ' [name="' + widgetName + '"]' );

		if ( procap_Response ) {
			data = data + '&' + inputName + '=' + procap_Response.value;
		}

		if ( id ) {
			data = data + '&' + widgetName + '=' + id.value;
		}

		body.set( 'data', data );
		config.body = body;
	}

	// noinspection JSCheckFunctionSignatures
	return await originalFetch( resource, config );
};
