// noinspection JSUnresolvedFunction,JSUnresolvedVariable

import $ from 'jquery';

global.jQuery = $;
global.$ = $;

// Import the script you want to test
require( '../../../assets/js/procaptcha-wc-checkout' );

// Simulate jQuery.ready
window.procaptchaWC( $ );

describe( 'procaptcha WooCommerce', () => {
	let procaptchaBindEvents;

	beforeEach( () => {
		procaptchaBindEvents = jest.fn();
		window.procaptchaBindEvents = procaptchaBindEvents;
	} );

	afterEach( () => {
		window.procaptchaBindEvents.mockRestore();
	} );

	test( 'checkout_error event triggers procaptchaBindEvents', () => {
		const event = new CustomEvent( 'checkout_error' );
		document.body.dispatchEvent( event );

		expect( procaptchaBindEvents ).toHaveBeenCalledTimes( 1 );
	} );

	test( 'updated_checkout event triggers procaptchaBindEvents', () => {
		const event = new CustomEvent( 'updated_checkout' );
		document.body.dispatchEvent( event );

		expect( procaptchaBindEvents ).toHaveBeenCalledTimes( 1 );
	} );
} );
