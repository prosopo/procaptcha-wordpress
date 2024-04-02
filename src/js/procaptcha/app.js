/**
 * Procaptcha Application.
 *
 * @file Procaptcha Application.
 */

import ProcaptchaWP from './procaptchaWP';

const procaptchawp = new ProcaptchaWP();

window.procaptchawp = procaptchawp;

window.procaptchaGetWidgetId = ( el ) => {
	procaptchawp.getWidgetId( el );
};

window.procaptchaReset = ( el ) => {
	procaptchawp.reset( el );
};

window.procaptchaBindEvents = () => {
	procaptchawp.bindEvents();
};

window.procaptchaSubmit = () => {
	procaptchawp.submit();
};

window.procaptchaOnLoad = () => {
	// Just allow one procaptcha element for now
	const procaptchaElement = document.querySelector(".procaptcha")
	// put the callback on the window object
	window.onCaptchaVerified = (payload) => procaptchawp.callback(payload, procaptchaElement)
	window.procaptchaBindEvents()
	document.dispatchEvent(new CustomEvent("procaptchaLoaded"));
};
