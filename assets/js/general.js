/* global jQuery, procap_, ProcaptchaGeneralObject, kaggDialog */

/**
 * @param ProcaptchaGeneralObject.ajaxUrl
 * @param ProcaptchaGeneralObject.checkConfigAction
 * @param ProcaptchaGeneralObject.checkConfigNonce
 * @param ProcaptchaGeneralObject.toggleSectionAction
 * @param ProcaptchaGeneralObject.toggleSectionNonce
 * @param ProcaptchaGeneralObject.modeLive
 * @param ProcaptchaGeneralObject.modeTestPublisher
 * @param ProcaptchaGeneralObject.modeTestEnterpriseSafeEndUser
 * @param ProcaptchaGeneralObject.modeTestEnterpriseBotDetected
 * @param ProcaptchaGeneralObject.siteKey
 * @param ProcaptchaGeneralObject.modeTestPublisherSiteKey
 * @param ProcaptchaGeneralObject.modeTestEnterpriseSafeEndUserSiteKey
 * @param ProcaptchaGeneralObject.modeTestEnterpriseBotDetectedSiteKey
 * @param ProcaptchaGeneralObject.checkConfigNotice
 * @param ProcaptchaGeneralObject.checkingConfigMsg
 * @param ProcaptchaGeneralObject.completeProcaptchaTitle
 * @param ProcaptchaGeneralObject.completeProcaptchaContent
 * @param ProcaptchaMainObject.params
 */

/* eslint-disable no-console */

/**
 * General settings page logic.
 *
 * @param {Object} $ jQuery instance.
 */
const general = function( $ ) {
	const msgSelector = '#procaptcha-message';
	let $message = $( msgSelector );
	const $form = $( 'form.procaptcha-general' );
	const $siteKey = $( '[name="procaptcha_settings[site_key]"]' );
	const $secretKey = $( '[name="procaptcha_settings[secret_key]"]' );
	const $theme = $( '[name="procaptcha_settings[theme]"]' );
	const $size = $( '[name="procaptcha_settings[size]"]' );
	const $language = $( '[name="procaptcha_settings[language]"]' );
	const $mode = $( '[name="procaptcha_settings[mode]"]' );
	const $customThemes = $( '[name="procaptcha_settings[custom_themes][]"]' );
	const $configParams = $( '[name="procaptcha_settings[config_params]"]' );
	const $enterpriseInputs = $( '.procaptcha-section-enterprise + table input' );
	const $recaptchaCompatOff = $( '[name="procaptcha_settings[recaptcha_compat_off][]"]' );
	const $submit = $form.find( '#submit' );
	const modes = {};
	let siteKeyInitVal = $siteKey.val();
	let secretKeyInitVal = $secretKey.val();
	let enterpriseInitValues = getEnterpriseValues();

	modes[ ProcaptchaGeneralObject.modeLive ] = ProcaptchaGeneralObject.siteKey;
	modes[ ProcaptchaGeneralObject.modeTestPublisher ] = ProcaptchaGeneralObject.modeTestPublisherSiteKey;
	modes[ ProcaptchaGeneralObject.modeTestEnterpriseSafeEndUser ] = ProcaptchaGeneralObject.modeTestEnterpriseSafeEndUserSiteKey;
	modes[ ProcaptchaGeneralObject.modeTestEnterpriseBotDetected ] = ProcaptchaGeneralObject.modeTestEnterpriseBotDetectedSiteKey;

	let credentialsChanged = false;
	let enterpriseSettingsChanged = false;

	let consoleLogs = [];

	interceptConsoleLogs();

	function interceptConsoleLogs() {
		consoleLogs = [];

		const systemLog = console.log;
		const systemWarn = console.warn;
		const systemInfo = console.info;
		const systemError = console.error;
		const systemClear = console.clear;

		// eslint-disable-next-line no-unused-vars
		console.log = function( message ) {
			consoleLogs.push( [ 'Console log:', arguments ] );
			systemLog.apply( console, arguments );
		};

		// eslint-disable-next-line no-unused-vars
		console.warn = function( message ) {
			consoleLogs.push( [ 'Console warn:', arguments ] );
			systemWarn.apply( console, arguments );
		};

		// eslint-disable-next-line no-unused-vars
		console.info = function( message ) {
			consoleLogs.push( [ 'Console info:', arguments ] );
			systemInfo.apply( console, arguments );
		};

		// eslint-disable-next-line no-unused-vars
		console.error = function( message ) {
			consoleLogs.push( [ 'Console error:', arguments ] );
			systemError.apply( console, arguments );
		};

		console.clear = function() {
			consoleLogs = [];
			systemClear();
		};
	}

	function getCleanConsoleLogs() {
		const logs = [];

		for ( let i = 0; i < consoleLogs.length; i++ ) {
			// Extract strings only (some JS functions push objects to console).
			const consoleLog = consoleLogs[ i ];
			const type = consoleLog[ 0 ];
			const args = consoleLog[ 1 ];
			const keys = Object.keys( args );
			const lines = [];

			for ( let a = 0; a < keys.length; a++ ) {
				if ( typeof ( args[ a ] ) === 'string' ) {
					lines.push( [ type, args[ a ] ].join( ' ' ) );
				}
			}

			logs.push( lines.join( '\n' ) );
		}

		consoleLogs = [];

		return logs.join( '\n' );
	}

	function getValues( $inputs ) {
		const values = {};

		$inputs.each( function() {
			const $input = $( this );
			const name = $input.attr( 'name' ).replace( /procaptcha_settings\[(.+)]/, '$1' );
			values[ name ] = $input.val();
		} );

		return values;
	}

	function getEnterpriseValues() {
		return getValues( $enterpriseInputs );
	}

	function clearMessage() {
		$message.remove();
		$( '<div id="procaptcha-message"></div>' ).insertAfter( '#procaptcha-options h2' );
		$message = $( msgSelector );
	}

	function showMessage( message = '', msgClass = '' ) {
		message = message === undefined ? '' : String( message );

		const logs = getCleanConsoleLogs();

		message += '\n' + logs;
		message = message.trim();

		if ( ! message ) {
			return;
		}

		$message.removeClass();
		$message.addClass( msgClass + ' notice is-dismissible' );

		const messageLines = message.split( '\n' ).map( function( line ) {
			return `<p>${ line }</p>`;
		} );
		$message.html( messageLines.join( '' ) );

		$( document ).trigger( 'wp-updates-notice-added' );

		const $wpwrap = $( '#wpwrap' );
		const top = $wpwrap.position().top;

		$( 'html, body' ).animate(
			{
				scrollTop: $message.offset().top - top - parseInt( $message.css( 'margin-bottom' ) ),
			},
			1000
		);
	}

	function showSuccessMessage( message = '' ) {
		showMessage( message, 'notice-success' );
	}

	function showErrorMessage( message = '' ) {
		showMessage( message, 'notice-error' );
	}

	function procap_Update( params = {} ) {
		const updatedParams = Object.assign( procap_.getParams(), params );
		procap_.setParams( updatedParams );

		const sampleProcaptcha = document.querySelector( '#procaptcha-options .procaptcha' );
		sampleProcaptcha.innerHTML = '';

		for ( const key in params ) {
			sampleProcaptcha.setAttribute( `data-${ key }`, `${ params[ key ] }` );
		}

		procap_.bindEvents();
	}

	function applyCustomThemes() {
		let configParamsJson = $configParams.val().trim();
		let configParams;

		configParamsJson = configParamsJson ? configParamsJson : null;

		try {
			configParams = JSON.parse( configParamsJson );
		} catch ( e ) {
			$configParams.css( 'background-color', '#ffabaf' );
			$submit.attr( 'disabled', true );
			showErrorMessage( 'Bad JSON!' );

			return;
		}

		if ( ! $customThemes.prop( 'checked' ) ) {
			configParams = {
				sitekey: $siteKey.val(),
				theme: $theme.val(),
				size: $size.val(),
				hl: $language.val(),
			};
		}

		procap_Update( configParams );
	}

	function checkConfig() {
		clearMessage();
		$submit.attr( 'disabled', true );

		const data = {
			action: ProcaptchaGeneralObject.checkConfigAction,
			nonce: ProcaptchaGeneralObject.checkConfigNonce,
			mode: $mode.val(),
			siteKey: $siteKey.val(),
			secretKey: $secretKey.val(),
			'procaptcha-response': $( 'textarea[name="procaptcha-response"]' ).val(),
		};

		// noinspection JSVoidFunctionReturnValueUsed,JSCheckFunctionSignatures
		return $.post( {
			url: ProcaptchaGeneralObject.ajaxUrl,
			data,
			beforeSend: () => showSuccessMessage( ProcaptchaGeneralObject.checkingConfigMsg ),
		} )
			.done( function( response ) {
				if ( ! response.success ) {
					showErrorMessage( response.data );
					return;
				}

				siteKeyInitVal = $siteKey.val();
				secretKeyInitVal = $secretKey.val();
				enterpriseInitValues = getValues( $enterpriseInputs );
				enterpriseSettingsChanged = false;

				showSuccessMessage( response.data );
				$submit.attr( 'disabled', false );
			} )
			.fail( function( response ) {
				showErrorMessage( response.statusText );
			} )
			.always( function() {
				procap_Update();
			} );
	}

	function checkChangeCredentials() {
		if ( $siteKey.val() === siteKeyInitVal && $secretKey.val() === secretKeyInitVal ) {
			credentialsChanged = false;
			clearMessage();
			$submit.attr( 'disabled', false );
		} else if ( ! credentialsChanged ) {
			credentialsChanged = true;
			showErrorMessage( ProcaptchaGeneralObject.checkConfigNotice );
			$submit.attr( 'disabled', true );
		}
	}

	function checkChangeEnterpriseSettings() {
		if ( JSON.stringify( getEnterpriseValues() ) === JSON.stringify( enterpriseInitValues ) ) {
			enterpriseSettingsChanged = false;
			clearMessage();
			$submit.attr( 'disabled', false );
		} else if ( ! enterpriseSettingsChanged ) {
			enterpriseSettingsChanged = true;
			showErrorMessage( ProcaptchaGeneralObject.checkConfigNotice );
			$submit.attr( 'disabled', true );
		}
	}

	document.addEventListener( 'procap_Loaded', function() {
		showErrorMessage();
	} );

	$( '#check_config' ).on( 'click', function( event ) {
		event.preventDefault();

		// Check if procap_ is solved.
		if ( $( '.procaptcha-general-sample-procaptcha iframe' ).attr( 'data-procaptcha-response' ) === '' ) {
			kaggDialog.confirm( {
				title: ProcaptchaGeneralObject.completeProcaptchaTitle,
				content: ProcaptchaGeneralObject.completeProcaptchaContent,
				type: 'info',
				buttons: {
					ok: {
						text: ProcaptchaGeneralObject.OKBtnText,
					},
				},
				onAction: () => window.procap_Reset( document.querySelector( '.procaptcha-general-sample-procaptcha' ) ),
			} );

			return;
		}

		checkConfig();
	} );

	$siteKey.on( 'change', function( e ) {
		const sitekey = $( e.target ).val();

		procap_Update( { sitekey } );
		checkChangeCredentials();
	} );

	$secretKey.on( 'change', function() {
		checkChangeCredentials();
	} );

	$theme.on( 'change', function( e ) {
		const theme = $( e.target ).val();
		procap_Update( { theme } );
	} );

	$size.on( 'change', function( e ) {
		const $invisibleNotice = $( '#procaptcha-invisible-notice' );
		const size = $( e.target ).val();

		if ( 'invisible' === size ) {
			$invisibleNotice.show();
		} else {
			$invisibleNotice.hide();
		}

		procap_Update( { size } );
	} );

	$language.on( 'change', function( e ) {
		const hl = $( e.target ).val();
		procap_Update( { hl } );
	} );

	$mode.on( 'change', function( e ) {
		const mode = $( e.target ).val();

		if ( ! modes.hasOwnProperty( mode ) ) {
			return;
		}

		if ( mode === ProcaptchaGeneralObject.modeLive ) {
			$siteKey.attr( 'disabled', false );
			$secretKey.attr( 'disabled', false );
		} else {
			$siteKey.attr( 'disabled', true );
			$secretKey.attr( 'disabled', true );
		}

		const sitekey = modes[ mode ];
		procap_Update( { sitekey } );
	} );

	$customThemes.on( 'change', function() {
		applyCustomThemes();
	} );

	$configParams.on( 'blur', function() {
		applyCustomThemes();
	} );

	$configParams.on( 'focus', function() {
		$configParams.css( 'background-color', 'unset' );
		$submit.attr( 'disabled', false );
	} );

	function forceHttps( host ) {
		host = host.replace( /(http|https):\/\//, '' );

		const url = new URL( 'https://' + host );

		return 'https://' + url.host;
	}

	function scriptUpdate() {
		const params = {
			onload: 'procap_OnLoad',
			render: 'explicit',
		};

		if ( $recaptchaCompatOff.prop( 'checked' ) ) {
			params.recaptchacompat = 'off';
		}

		if ( $customThemes.prop( 'checked' ) ) {
			params.custom = 'true';
		}

		const enterpriseParams = {
			asset_host: 'assethost',
			endpoint: 'endpoint',
			host: 'host',
			image_host: 'imghost',
			report_api: 'reportapi',
			sentry: 'sentry',
		};

		const enterpriseValues = getEnterpriseValues();

		for ( const enterpriseParam in enterpriseParams ) {
			const value = enterpriseValues[ enterpriseParam ].trim();

			if ( value ) {
				params[ enterpriseParams[ enterpriseParam ] ] = encodeURIComponent( forceHttps( value ) );
			}
		}

		/**
		 * @param enterpriseValues.api_host
		 */
		let apiHost = enterpriseValues.api_host.trim();
		apiHost = apiHost ? apiHost : 'js.procaptcha.io';
		apiHost = forceHttps( apiHost ) + '/1/api.js';

		const url = new URL( apiHost );

		for ( const name in params ) {
			url.searchParams.append( name, params[ name ] );
		}

		// Remove the existing API script.
		document.getElementById( 'procaptcha-api' ).remove();
		delete global.procaptcha;

		// Remove sample procap_.
		const sampleProcaptcha = document.querySelector( '#procaptcha-options .procaptcha' );
		sampleProcaptcha.innerHTML = '';

		// Re-create the API script.
		const t = document.getElementsByTagName( 'head' )[ 0 ];
		const s = document.createElement( 'script' );

		s.type = 'text/javascript';
		s.id = 'procaptcha-api';
		s.src = url.href;

		t.appendChild( s );
	}

	$enterpriseInputs.on( 'change', function() {
		scriptUpdate();
		checkChangeEnterpriseSettings();
	} );

	// Toggle a section.
	$( '.procaptcha-general h3' ).on( 'click', function( event ) {
		const $h3 = $( event.currentTarget );

		$h3.toggleClass( 'closed' );

		const data = {
			action: ProcaptchaGeneralObject.toggleSectionAction,
			nonce: ProcaptchaGeneralObject.toggleSectionNonce,
			section: $h3.attr( 'class' ).replaceAll( /(procaptcha-section-|closed)/g, '' ).trim(),
			status: ! $h3.hasClass( 'closed' ),
		};

		$.post( {
			url: ProcaptchaGeneralObject.ajaxUrl,
			data,
		} )
			.done( function( response ) {
				if ( ! response.success ) {
					showErrorMessage( response.data );
				}
			} )
			.fail( function( response ) {
				showErrorMessage( response.statusText );
			} );
	} );
};

window.procap_General = general;

jQuery( document ).ready( general );
