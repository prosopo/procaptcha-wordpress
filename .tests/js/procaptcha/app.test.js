// noinspection JSUnresolvedFunction,JSUnresolvedVariable

// Import the app.js file to ensure global functions are defined
import '../../../src/js/procaptcha/app.js';
import Procaptcha from '../../../src/js/procaptcha/procaptcha.js';

jest.mock( '../../../src/js/procaptcha/procaptcha.js', () => {
	const mockProcaptcha = {
		getWidgetId: jest.fn(),
		reset: jest.fn(),
		bindEvents: jest.fn(),
		submit: jest.fn(),
	};
	return jest.fn( () => mockProcaptcha );
} );

describe( 'app.js', () => {
	let procap_;

	beforeEach( () => {
		procap_ = new Procaptcha();
		global.procap_ = procap_;
	} );

	test( 'procap_GetWidgetId should call getWidgetId with the given element', () => {
		const mockEl = {};
		window.procap_GetWidgetId( mockEl );
		expect( procap_.getWidgetId ).toHaveBeenCalledWith( mockEl );
	} );

	test( 'procap_Reset should call reset with the given element', () => {
		const mockEl = {};
		window.procap_Reset( mockEl );
		expect( procap_.reset ).toHaveBeenCalledWith( mockEl );
	} );

	test( 'procap_BindEvents should call bindEvents', () => {
		window.procap_BindEvents();
		expect( procap_.bindEvents ).toHaveBeenCalled();
	} );

	test( 'procap_Submit should call submit', () => {
		window.procap_Submit();
		expect( procap_.submit ).toHaveBeenCalled();
	} );

	test( 'procap_OnLoad should call bindEvents', () => {
		window.procap_OnLoad();
		expect( procap_.bindEvents ).toHaveBeenCalled();
	} );
} );
