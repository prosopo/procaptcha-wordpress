/**
 * Procaptcha Application.
 *
 * @file Procaptcha Application.
 */

import Procaptcha from './procaptcha';

const procaptcha = new Procaptcha();

window.procaptcha = procaptcha;

window.procaptchaGetWidgetId = ( el ) => {
	procaptcha.getWidgetId( el );
};

window.procaptchaReset = ( el ) => {
	procaptcha.reset( el );
};

window.procaptchaBindEvents = () => {
	procaptcha.bindEvents();
};

window.procaptchaSubmit = () => {
	procaptcha.submit();
};

window.procaptchaOnLoad = () => {
	window.procaptchaBindEvents();

	document.dispatchEvent( new CustomEvent( 'procaptchaLoaded' ) );
};
