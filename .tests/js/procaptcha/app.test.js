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
	let procaptcha;

	beforeEach( () => {
		procaptcha = new Procaptcha();
		global.procaptcha = procaptcha;
	} );

	test( 'procaptchaGetWidgetId should call getWidgetId with the given element', () => {
		const mockEl = {};
		window.procaptchaGetWidgetId( mockEl );
		expect( procaptcha.getWidgetId ).toHaveBeenCalledWith( mockEl );
	} );

	test( 'procaptchaReset should call reset with the given element', () => {
		const mockEl = {};
		window.procaptchaReset( mockEl );
		expect( procaptcha.reset ).toHaveBeenCalledWith( mockEl );
	} );

	test( 'procaptchaBindEvents should call bindEvents', () => {
		window.procaptchaBindEvents();
		expect( procaptcha.bindEvents ).toHaveBeenCalled();
	} );

	test( 'procaptchaSubmit should call submit', () => {
		window.procaptchaSubmit();
		expect( procaptcha.submit ).toHaveBeenCalled();
	} );

	test( 'procaptchaOnLoad should call bindEvents', () => {
		window.procaptchaOnLoad();
		expect( procaptcha.bindEvents ).toHaveBeenCalled();
	} );
} );
