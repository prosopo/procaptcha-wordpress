/**
 * HCaptcha Application.
 *
 * @file HCaptcha Application.
 */

import Procaptcha from './procaptcha';

const hCaptcha = new Procaptcha();

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
	window.hCaptchaBindEvents();

	document.dispatchEvent( new CustomEvent( 'hCaptchaLoaded' ) );
};
