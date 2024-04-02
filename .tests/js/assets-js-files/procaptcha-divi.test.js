// noinspection JSUnresolvedFunction,JSUnresolvedVariable

import $ from 'jquery';

global.jQuery = $;
global.$ = $;

describe( 'procaptcha ajaxStop binding', () => {
	let procaptchaBindEvents;

	beforeEach( () => {
		procaptchaBindEvents = jest.fn();
		global.procaptchaBindEvents = procaptchaBindEvents;

		require( '../../../assets/js/procaptcha-divi.js' );
	} );

	afterEach( () => {
		global.procaptchaBindEvents.mockRestore();
	} );

	test( 'procaptchaBindEvents is called when ajaxStop event is triggered', () => {
		const xhr = {};
		const settings = {};

		settings.data = '?some_data&et_pb_contactform_submit_0=some_value';
		$( document ).trigger( 'ajaxSuccess', [ xhr, settings ] );
		expect( procaptchaBindEvents ).toHaveBeenCalledTimes( 1 );
	} );
} );
