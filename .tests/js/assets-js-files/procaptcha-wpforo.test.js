// noinspection JSUnresolvedFunction,JSUnresolvedVariable

import $ from 'jquery';

global.jQuery = $;
global.$ = $;

// Import the script you want to test
require( '../../../assets/js/procaptcha-wpforo' );

describe( 'procaptcha WPForo', () => {
	let procaptchaBindEvents;

	beforeEach( () => {
		document.body.innerHTML = `
		<div class="wpforo-section">
			<button class="add_wpftopic">			
			</button>
		</div>
		`;

		procaptchaBindEvents = jest.fn();
		global.procaptchaBindEvents = procaptchaBindEvents;

		// Simulate jQuery.ready
		window.procaptchaWPForo( $ );
	} );

	afterEach( () => {
		global.procaptchaBindEvents.mockRestore();
	} );

	test( 'clicking on new topic button triggers procaptchaBindEvents', () => {
		const $btn = $( '.wpforo-section .add_wpftopic:not(.not_reg_user)' );

		$( $btn ).trigger( 'click' );

		expect( procaptchaBindEvents ).toHaveBeenCalledTimes( 1 );
	} );
} );
