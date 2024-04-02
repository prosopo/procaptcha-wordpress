// noinspection JSUnresolvedFunction,JSUnresolvedVariable

import Procaptcha from '../../../src/js/procaptcha/procaptcha.js';

// Helper function to create a DOM element with optional attributes
function createElement( tagName, attributes = {} ) {
	const element = document.createElement( tagName );
	for ( const key in attributes ) {
		element.setAttribute( key, attributes[ key ] );
	}
	return element;
}

describe( 'Procaptcha', () => {
	let procap_;

	beforeEach( () => {
		procap_ = new Procaptcha();
	} );

	test( 'GenerateID', () => {
		expect( procap_.generateID() ).toMatch( /^(?:[0-9|a-f]{4}-){3}[0-9|a-f]{4}$/ );
	} );

	test( 'getFoundFormById', () => {
		const testForm = {
			procap_Id: 'test-id',
			submitButtonSelector: 'test-selector',
		};

		procap_.foundForms.push( testForm );

		expect( procap_.getFoundFormById( 'test-id' ) ).toEqual( testForm );
		expect( procap_.getFoundFormById( 'non-existent-id' ) ).toBeUndefined();
	} );

	test( 'isSameOrDescendant', () => {
		const parent = document.createElement( 'div' );
		const child = document.createElement( 'div' );
		const grandChild = document.createElement( 'div' );
		const unrelatedElement = document.createElement( 'div' );

		parent.appendChild( child );
		child.appendChild( grandChild );

		expect( procap_.isSameOrDescendant( parent, parent ) ).toBeTruthy();
		expect( procap_.isSameOrDescendant( parent, child ) ).toBeTruthy();
		expect( procap_.isSameOrDescendant( parent, grandChild ) ).toBeTruthy();
		expect( procap_.isSameOrDescendant( parent, unrelatedElement ) ).toBeFalsy();
	} );

	test( 'getParams and setParams', () => {
		const testParams = { test: 'value' };

		expect( procap_.getParams() ).not.toEqual( testParams );
		procap_.setParams( testParams );
		expect( procap_.getParams() ).toEqual( testParams );
	} );

	test( 'bindEvents and reset', () => {
		// Mock procaptcha object
		global.procaptcha = {
			render: jest.fn( ( procaptchaElement ) => {
				// Mock the rendering of the procap_ widget by adding a dataset attribute
				const iframe = document.createElement( 'iframe' );
				iframe.dataset.procaptchaWidgetId = 'mock-widget-id';
				iframe.dataset.procaptchaResponse = '';
				procaptchaElement.appendChild( iframe );
			} ),
			execute: jest.fn(),
			reset: jest.fn(),
		};

		// Mock ProcaptchaMainObject
		global.ProcaptchaMainObject = {
			params: JSON.stringify( { test: 'value' } ),
		};

		// Create DOM elements
		const form1 = createElement( 'form' );
		const form2 = createElement( 'form' );
		const form3 = createElement( 'form' );
		const widget1 = createElement( 'div', { class: 'procaptcha', 'data-size': 'invisible' } );
		const widget2 = createElement( 'div', { class: 'procaptcha', 'data-size': 'normal' } );
		const submit1 = createElement( 'input', { type: 'submit' } );
		const submit2 = createElement( 'input', { type: 'submit' } );

		form1.appendChild( widget1 );
		form1.appendChild( submit1 );
		form2.appendChild( widget2 );
		form2.appendChild( submit2 );

		document.body.appendChild( form1 );
		document.body.appendChild( form2 );
		document.body.appendChild( form3 );

		// Spy on addEventListener before calling bindEvents
		const submit1ClickHandler = jest.spyOn( submit1, 'addEventListener' );

		procap_.bindEvents();

		// Check that procaptcha.render was called twice (for form1 and form2)
		expect( global.procaptcha.render ).toHaveBeenCalledTimes( 2 );

		// Check that an event listener was added to form1 for invisible procap_
		expect( submit1ClickHandler ).toHaveBeenCalledWith( 'click', expect.any( Function ), true );

		// Simulate click event on form1
		const clickEvent = new Event( 'click', { bubbles: true } );
		submit1.dispatchEvent( clickEvent );

		// Check that procaptcha.execute was called
		expect( global.procaptcha.execute ).toHaveBeenCalled();

		// Mock requestSubmit on the form element
		form1.requestSubmit = jest.fn();

		// Call submit method
		procap_.submit();

		// Check if requestSubmit was called on the form element
		expect( form1.requestSubmit ).toHaveBeenCalled();

		// Call reset method
		procap_.reset( form1 );

		// Check if procaptcha.reset was called with the correct widget id
		expect( global.procaptcha.reset ).toHaveBeenCalledWith( 'mock-widget-id' );

		// Clean up DOM elements
		document.body.removeChild( form1 );
		document.body.removeChild( form2 );
		document.body.removeChild( form3 );
	} );
} );
