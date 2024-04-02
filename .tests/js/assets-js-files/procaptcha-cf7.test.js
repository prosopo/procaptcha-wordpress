// noinspection JSUnresolvedFunction,JSUnresolvedVariable

describe( 'procaptcha Contact Form 7', () => {
	let procaptchaReset;

	beforeEach( () => {
		document.body.innerHTML = `
	      <form class="wpcf7">
	        <div class="procaptcha-widget"></div>
	      </form>
	      <form class="wpcf7">
	        <div class="procaptcha-widget"></div>
	      </form>
	    `;

		procaptchaReset = jest.fn();
		global.procaptchaReset = procaptchaReset;

		require( '../../../assets/js/procaptcha-cf7.js' );
		document.dispatchEvent( new Event( 'DOMContentLoaded' ) );
	} );

	afterEach( () => {
		global.procaptchaReset.mockRestore();
	} );

	const eventTypes = [
		'wpcf7invalid',
		'wpcf7spam',
		'wpcf7mailsent',
		'wpcf7mailfailed',
		'wpcf7submit',
	];

	eventTypes.forEach( ( eventType ) => {
		test( `procaptchaReset is called when the ${ eventType } event is triggered`, () => {
			const forms = document.querySelectorAll( '.wpcf7' );
			forms.forEach( ( form ) => {
				const event = new CustomEvent( eventType );
				form.dispatchEvent( event );
			} );

			expect( procaptchaReset ).toHaveBeenCalledTimes( forms.length );
		} );
	} );
} );
