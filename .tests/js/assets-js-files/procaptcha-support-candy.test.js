// noinspection JSUnresolvedFunction,JSUnresolvedVariable

import $ from 'jquery';

global.jQuery = $;
global.$ = $;

describe( 'procaptcha ajaxStop binding', () => {
	let procaptchaBindEvents;

	beforeEach( () => {
		procaptchaBindEvents = jest.fn();
		global.procaptchaBindEvents = procaptchaBindEvents;

		require( '../../../assets/js/procaptcha-support-candy.js' );
	} );

	afterEach( () => {
		global.procaptchaBindEvents.mockRestore();
	} );

	test( 'procaptchaBindEvents is called when ajaxStop event is triggered', () => {
		const xhr = {};
		const settings = {};

		settings.data = '?some_data&action=wpsc_get_ticket_form';
		$( document ).trigger( 'ajaxSuccess', [ xhr, settings ] );
		expect( procaptchaBindEvents ).toHaveBeenCalledTimes( 1 );
	} );
} );
