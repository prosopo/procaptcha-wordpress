// noinspection JSUnresolvedFunction,JSUnresolvedVariable

describe( 'procap_ ACFE', () => {
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

	// Mock window.procap_ object and methods
	window.procap_ = {
		getParams: jest.fn( () => procaptchaParams ),
		setParams: jest.fn( ( params ) => {
			procaptchaParams = params;
		} ),
	};

	// Mock window.procap_OnLoad
	window.procap_OnLoad = jest.fn();

	require( '../../../assets/js/procaptcha-acfe.js' );

	afterEach( () => {
		// Initialize procaptchaParams
		procaptchaParams = initParams();
	} );

	test( 'sets custom callbacks and calls original procap_OnLoad', () => {
		window.procap_OnLoad();

		const params = window.procap_.getParams();
		params.callback();
		params[ 'error-callback' ]();
		params[ 'expired-callback' ]();

		expect( window.procap_.getParams ).toHaveBeenCalled();
		expect( window.procap_.setParams ).toHaveBeenCalled();
		expect( window.procap_OnLoad ).toHaveBeenCalled();
	} );
} );
