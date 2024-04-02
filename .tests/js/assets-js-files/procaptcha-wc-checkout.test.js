// noinspection JSUnresolvedFunction,JSUnresolvedVariable

import $ from 'jquery';

global.jQuery = $;
global.$ = $;

// Import the script you want to test
require( '../../../assets/js/procaptcha-wc-checkout' );

// Simulate jQuery.ready
window.procap_WC( $ );

describe( 'procap_ WooCommerce', () => {
	let procap_BindEvents;

	beforeEach( () => {
		procap_BindEvents = jest.fn();
		window.procap_BindEvents = procap_BindEvents;
	} );

	afterEach( () => {
		window.procap_BindEvents.mockRestore();
	} );

	test( 'checkout_error event triggers procap_BindEvents', () => {
		const event = new CustomEvent( 'checkout_error' );
		document.body.dispatchEvent( event );

		expect( procap_BindEvents ).toHaveBeenCalledTimes( 1 );
	} );

	test( 'updated_checkout event triggers procap_BindEvents', () => {
		const event = new CustomEvent( 'updated_checkout' );
		document.body.dispatchEvent( event );

		expect( procap_BindEvents ).toHaveBeenCalledTimes( 1 );
	} );
} );
