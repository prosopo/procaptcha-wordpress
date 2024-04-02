// noinspection JSUnresolvedFunction,JSUnresolvedVariable

import $ from 'jquery';

global.jQuery = $;
global.$ = $;

describe( 'procap_ ajaxStop binding', () => {
	let procap_BindEvents;

	beforeEach( () => {
		procap_BindEvents = jest.fn();
		global.procap_BindEvents = procap_BindEvents;

		require( '../../../assets/js/procaptcha-divi.js' );
	} );

	afterEach( () => {
		global.procap_BindEvents.mockRestore();
	} );

	test( 'procap_BindEvents is called when ajaxStop event is triggered', () => {
		const xhr = {};
		const settings = {};

		settings.data = '?some_data&et_pb_contactform_submit_0=some_value';
		$( document ).trigger( 'ajaxSuccess', [ xhr, settings ] );
		expect( procap_BindEvents ).toHaveBeenCalledTimes( 1 );
	} );
} );
