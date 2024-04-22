/**
 * HCaptcha Application.
 *
 * @file HCaptcha Application.
 */

import HCaptcha from './hcaptcha';

const hCaptcha = new HCaptcha();

window.hCaptcha = hCaptcha;

window.hCaptchaGetWidgetId = ( el ) => {
	hCaptcha.getWidgetId( el );
};

window.hCaptchaReset = ( el ) => {
	hCaptcha.reset( el );
};

window.hCaptchaBindEvents = () => {
	hCaptcha.bindEvents();
};

window.hCaptchaSubmit = () => {
	hCaptcha.submit();
};

window.hCaptchaOnLoad = () => {
	function hCaptchaOnLoad() {
		console.log("running onLoad")
		// Just allow one procaptcha element for now
		const procaptchaElement = document.querySelector(".procaptcha")
		// put the callback on the window object
		window.onCaptchaVerified = (payload) => hCaptcha.callback(payload, procaptchaElement)
		window.hCaptchaBindEvents();
		document.dispatchEvent(new CustomEvent("hCaptchaLoaded"));
	}

	// Sync with DOMContentLoaded event.
	if ( document.readyState === 'loading' ) {
		window.addEventListener( 'DOMContentLoaded', hCaptchaOnLoad );
	} else {
		hCaptchaOnLoad();
	}
};
