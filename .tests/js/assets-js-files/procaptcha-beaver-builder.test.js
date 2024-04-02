// noinspection JSUnresolvedFunction,JSUnresolvedVariable

import $ from 'jquery';

global.jQuery = $;
global.$ = $;

describe( 'procap_ Beaver Builder', () => {
	let ajaxPrefilterCallback;
	const options = {
		data: '',
	};

	beforeEach( () => {
		// Mock jQuery.ajaxPrefilter
		$.ajaxPrefilter = jest.fn( ( callback ) => {
			ajaxPrefilterCallback = callback;
		} );

		document.body.innerHTML = `
	      <div data-node="123">
	        <input type="hidden" name="procaptcha-response" value="responseValue">
	        <input type="hidden" name="procaptcha_beaver_builder_nonce" value="nonceValue">
	        <input type="hidden" name="procaptcha_login_nonce" value="loginNonceValue">
	      </div>
	    `;

		require( '../../../assets/js/procaptcha-beaver-builder.js' );
	} );

	test( 'appends procaptcha-response and procaptcha_beaver_builder_nonce when data starts with action=fl_builder_email', () => {
		options.data = 'action=fl_builder_email&node_id=123';
		ajaxPrefilterCallback( options );
		expect( options.data ).toContain( 'procaptcha-response=responseValue' );
		expect( options.data ).toContain( 'procaptcha_beaver_builder_nonce=nonceValue' );
	} );

	test( 'appends procaptcha-response and procaptcha_login_nonce when data starts with action=fl_builder_login_form_submit', () => {
		options.data = 'action=fl_builder_login_form_submit&node_id=123';
		ajaxPrefilterCallback( options );
		expect( options.data ).toContain( 'procaptcha-response=responseValue' );
		expect( options.data ).toContain( 'procaptcha_login_nonce=loginNonceValue' );
	} );

	test( 'does not append anything when data does not start with any expected action', () => {
		options.data = 'action=other_action&node_id=123';
		ajaxPrefilterCallback( options );
		expect( options.data ).not.toContain( 'procaptcha-response' );
		expect( options.data ).not.toContain( 'procaptcha_beaver_builder_nonce' );
		expect( options.data ).not.toContain( 'procaptcha_login_nonce' );
	} );
} );
