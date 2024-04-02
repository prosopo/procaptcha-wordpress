// noinspection JSUnresolvedFunction,JSUnresolvedVariable

describe( 'procap_ Contact Form 7', () => {
	let procap_Reset;

	beforeEach( () => {
		document.body.innerHTML = `
	      <form class="wpcf7">
	        <div class="procaptcha-widget"></div>
	      </form>
	      <form class="wpcf7">
	        <div class="procaptcha-widget"></div>
	      </form>
	    `;

		procap_Reset = jest.fn();
		global.procap_Reset = procap_Reset;

		require( '../../../assets/js/procaptcha-cf7.js' );
		document.dispatchEvent( new Event( 'DOMContentLoaded' ) );
	} );

	afterEach( () => {
		global.procap_Reset.mockRestore();
	} );

	const eventTypes = [
		'wpcf7invalid',
		'wpcf7spam',
		'wpcf7mailsent',
		'wpcf7mailfailed',
		'wpcf7submit',
	];

	eventTypes.forEach( ( eventType ) => {
		test( `procap_Reset is called when the ${ eventType } event is triggered`, () => {
			const forms = document.querySelectorAll( '.wpcf7' );
			forms.forEach( ( form ) => {
				const event = new CustomEvent( eventType );
				form.dispatchEvent( event );
			} );

			expect( procap_Reset ).toHaveBeenCalledTimes( forms.length );
		} );
	} );
} );
