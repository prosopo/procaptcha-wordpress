/**
 * Procaptcha Application.
 *
 * @file Procaptcha Application.
 */

import Procaptcha from './procaptcha';

const procap_ = new Procaptcha();

window.procap_ = procap_;

window.procap_GetWidgetId = ( el ) => {
	procap_.getWidgetId( el );
};

window.procap_Reset = ( el ) => {
	procap_.reset( el );
};

window.procap_BindEvents = () => {
	procap_.bindEvents();
};

window.procap_Submit = () => {
	procap_.submit();
};

window.procap_OnLoad = () => {
	window.procap_BindEvents();

	document.dispatchEvent( new CustomEvent( 'procap_Loaded' ) );
};
