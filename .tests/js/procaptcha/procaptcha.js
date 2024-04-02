// noinspection JSUnresolvedFunction,JSUnresolvedVariable

import ProcaptchaWP from '../../../src/js/procaptcha/procaptchaWP.js';

// Helper function to create a DOM element with optional attributes
function createElement( tagName, attributes = {} ) {
	const element = document.createElement( tagName );
	for ( const key in attributes ) {
		element.setAttribute( key, attributes[ key ] );
	}
	return element;
}

describe( 'Procaptcha', () => {
	let procaptcha;

	beforeEach( () => {
		procaptcha = new ProcaptchaWP();
	} );

	test( 'GenerateID', () => {
		expect( procaptcha.generateID() ).toMatch( /^(?:[0-9|a-f]{4}-){3}[0-9|a-f]{4}$/ );
	} );

	test( 'getFoundFormById', () => {
		const testForm = {
			procaptchaId: 'test-id',
			submitButtonSelector: 'test-selector',
		};

		procaptcha.foundForms.push( testForm );

		expect( procaptcha.getFoundFormById( 'test-id' ) ).toEqual( testForm );
		expect( procaptcha.getFoundFormById( 'non-existent-id' ) ).toBeUndefined();
	} );

	test( 'isSameOrDescendant', () => {
		const parent = document.createElement( 'div' );
		const child = document.createElement( 'div' );
		const grandChild = document.createElement( 'div' );
		const unrelatedElement = document.createElement( 'div' );

		parent.appendChild( child );
		child.appendChild( grandChild );

		expect( procaptcha.isSameOrDescendant( parent, parent ) ).toBeTruthy();
		expect( procaptcha.isSameOrDescendant( parent, child ) ).toBeTruthy();
		expect( procaptcha.isSameOrDescendant( parent, grandChild ) ).toBeTruthy();
		expect( procaptcha.isSameOrDescendant( parent, unrelatedElement ) ).toBeFalsy();
	} );

	test( 'getParams and setParams', () => {
		const testParams = { test: 'value' };

		expect( procaptcha.getParams() ).not.toEqual( testParams );
		procaptcha.setParams( testParams );
		expect( procaptcha.getParams() ).toEqual( testParams );
	} );

	test( 'bindEvents and reset', () => {
		// Mock procaptcha object
		global.procaptcha = {
			render: jest.fn( ( procaptchaElement ) => {
				// Mock the rendering of the procaptcha widget by adding a dataset attribute
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

		procaptcha.bindEvents();

		// Check that procaptcha.render was called twice (for form1 and form2)
		expect( global.procaptcha.render ).toHaveBeenCalledTimes( 2 );

		// Check that an event listener was added to form1 for invisible procaptcha
		expect( submit1ClickHandler ).toHaveBeenCalledWith( 'click', expect.any( Function ), true );

		// Simulate click event on form1
		const clickEvent = new Event( 'click', { bubbles: true } );
		submit1.dispatchEvent( clickEvent );

		// Check that procaptcha.execute was called
		expect( global.procaptcha.execute ).toHaveBeenCalled();

		// Mock requestSubmit on the form element
		form1.requestSubmit = jest.fn();

		// Call submit method
		procaptcha.submit();

		// Check if requestSubmit was called on the form element
		expect( form1.requestSubmit ).toHaveBeenCalled();

		// Call reset method
		procaptcha.reset( form1 );

		// Check if procaptcha.reset was called with the correct widget id
		expect( global.procaptcha.reset ).toHaveBeenCalledWith( 'mock-widget-id' );

		// Clean up DOM elements
		document.body.removeChild( form1 );
		document.body.removeChild( form2 );
		document.body.removeChild( form3 );
	} );
} );
