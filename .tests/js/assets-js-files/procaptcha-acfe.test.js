// noinspection JSUnresolvedFunction,JSUnresolvedVariable

describe( 'procaptcha ACFE', () => {
	let procaptchaParams;

	function initParams() {
		return {
			callback: jest.fn(),
			'error-callback': jest.fn(),
			'expired-callback': jest.fn(),
		};
	}

	// Init params
	procaptchaParams = initParams();

	// Mock window.procaptcha object and methods
	window.procaptcha = {
		getParams: jest.fn( () => procaptchaParams ),
		setParams: jest.fn( ( params ) => {
			procaptchaParams = params;
		} ),
	};

	// Mock window.procaptchaOnLoad
	window.procaptchaOnLoad = jest.fn();

	require( '../../../assets/js/procaptcha-acfe.js' );

	afterEach( () => {
		// Initialize procaptchaParams
		procaptchaParams = initParams();
	} );

	test( 'sets custom callbacks and calls original procaptchaOnLoad', () => {
		window.procaptchaOnLoad();

		const params = window.procaptcha.getParams();
		params.callback();
		params[ 'error-callback' ]();
		params[ 'expired-callback' ]();

		expect( window.procaptcha.getParams ).toHaveBeenCalled();
		expect( window.procaptcha.setParams ).toHaveBeenCalled();
		expect( window.procaptchaOnLoad ).toHaveBeenCalled();
	} );
} );
