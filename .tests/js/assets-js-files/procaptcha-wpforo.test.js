// noinspection JSUnresolvedFunction,JSUnresolvedVariable

import $ from 'jquery';

global.jQuery = $;
global.$ = $;

// Import the script you want to test
require( '../../../assets/js/procaptcha-wpforo' );

describe( 'procap_ WPForo', () => {
	let procap_BindEvents;

	beforeEach( () => {
		document.body.innerHTML = `
		<div class="wpforo-section">
			<button class="add_wpftopic">			
			</button>
		</div>
		`;

		procap_BindEvents = jest.fn();
		global.procap_BindEvents = procap_BindEvents;

		// Simulate jQuery.ready
		window.procap_WPForo( $ );
	} );

	afterEach( () => {
		global.procap_BindEvents.mockRestore();
	} );

	test( 'clicking on new topic button triggers procap_BindEvents', () => {
		const $btn = $( '.wpforo-section .add_wpftopic:not(.not_reg_user)' );

		$( $btn ).trigger( 'click' );

		expect( procap_BindEvents ).toHaveBeenCalledTimes( 1 );
	} );
} );
