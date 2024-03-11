/* global jQuery, procaptcha, PROCAPTCHAGeneralObject */

/**
 * @param PROCAPTCHAGeneralObject.ajaxUrl
 * @param PROCAPTCHAGeneralObject.checkConfigAction
 * @param PROCAPTCHAGeneralObject.nonce
 * @param PROCAPTCHAGeneralObject.modeLive
 * @param PROCAPTCHAGeneralObject.modeTestPublisher
 * @param PROCAPTCHAGeneralObject.modeTestEnterpriseSafeEndUser
 * @param PROCAPTCHAGeneralObject.modeTestEnterpriseBotDetected
 * @param PROCAPTCHAGeneralObject.siteKey
 * @param PROCAPTCHAGeneralObject.modeTestPublisherSiteKey
 * @param PROCAPTCHAGeneralObject.modeTestEnterpriseSafeEndUserSiteKey
 * @param PROCAPTCHAGeneralObject.modeTestEnterpriseBotDetectedSiteKey
 * @param PROCAPTCHAGeneralObject.checkConfigNotice
 * @param PROCAPTCHAMainObject.params
 */

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
	const $submit = $form.find( '#submit' );
	const modes = {};
	const siteKeyInitVal = $siteKey.val();
	const secretKeyInitVal = $secretKey.val();

	modes[ PROCAPTCHAGeneralObject.modeLive ] = PROCAPTCHAGeneralObject.siteKey;
	modes[ PROCAPTCHAGeneralObject.modeTestPublisher ] = PROCAPTCHAGeneralObject.modeTestPublisherSiteKey;
	modes[ PROCAPTCHAGeneralObject.modeTestEnterpriseSafeEndUser ] = PROCAPTCHAGeneralObject.modeTestEnterpriseSafeEndUserSiteKey;
	modes[ PROCAPTCHAGeneralObject.modeTestEnterpriseBotDetected ] = PROCAPTCHAGeneralObject.modeTestEnterpriseBotDetectedSiteKey;

	function clearMessage() {
		$message.remove();
		$( '<div id="procaptcha-message"></div>' ).insertAfter( '#procaptcha-options h2' );
		$message = $( msgSelector );
	}

	function showMessage( message, msgClass ) {
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

	function showSuccessMessage( response ) {
		showMessage( response, 'notice-success' );
	}

	function showErrorMessage( response ) {
		showMessage( response, 'notice-error' );
	}

	function pCAPTCHAUpdate( params ) {
		const updatedParams = Object.assign( procaptcha.getParams(), params );
		procaptcha.setParams( updatedParams );

		const samplePROCAPTCHA = document.querySelector( '#procaptcha-options .pro-captcha' );
		samplePROCAPTCHA.innerHTML = '';

		for ( const key in params ) {
			samplePROCAPTCHA.setAttribute( `data-${ key }`, `${ params[ key ] }` );
		}

		procaptcha.bindEvents();
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

		pCAPTCHAUpdate( configParams );
	}

	function checkConfig() {
		clearMessage();
		$submit.attr( 'disabled', true );

		const data = {
			action: PROCAPTCHAGeneralObject.checkConfigAction,
			nonce: PROCAPTCHAGeneralObject.nonce,
			mode: $mode.val(),
			siteKey: $siteKey.val(),
			secretKey: $secretKey.val(),
			'pro-captcha-response': $( 'textarea[name="pro-captcha-response"]' ).val(),
		};

		// noinspection JSVoidFunctionReturnValueUsed,JSCheckFunctionSignatures
		return $.post( {
			url: PROCAPTCHAGeneralObject.ajaxUrl,
			data,
		} )
			.done( function( response ) {
				if ( ! response.success ) {
					showErrorMessage( response.data );
					return;
				}

				showSuccessMessage( response.data );
				$submit.attr( 'disabled', false );
			} )
			.fail( function( response ) {
				showErrorMessage( response.statusText );
			} )
			.always( function() {
				pCAPTCHAUpdate( {} );
			} );
	}

	function checkCredentialsChange() {
		if ( $siteKey.val() === siteKeyInitVal && $secretKey.val() === secretKeyInitVal ) {
			clearMessage();
			$submit.attr( 'disabled', false );
		} else {
			showErrorMessage( PROCAPTCHAGeneralObject.checkConfigNotice );
			$submit.attr( 'disabled', true );
		}
	}

	$( '#check_config' ).on( 'click', function( event ) {
		event.preventDefault();

		checkConfig();
	} );

	$siteKey.on( 'change', function( e ) {
		const sitekey = $( e.target ).val();
		pCAPTCHAUpdate( { sitekey } );
		checkCredentialsChange();
	} );

	$secretKey.on( 'change', function() {
		checkCredentialsChange();
	} );

	$theme.on( 'change', function( e ) {
		const theme = $( e.target ).val();
		pCAPTCHAUpdate( { theme } );
	} );

	$size.on( 'change', function( e ) {
		const $invisibleNotice = $( '#procaptcha-invisible-notice' );
		const size = $( e.target ).val();

		if ( 'invisible' === size ) {
			$invisibleNotice.show();
		} else {
			$invisibleNotice.hide();
		}

		pCAPTCHAUpdate( { size } );
	} );

	$language.on( 'change', function( e ) {
		const hl = $( e.target ).val();
		pCAPTCHAUpdate( { hl } );
	} );

	$mode.on( 'change', function( e ) {
		const mode = $( e.target ).val();

		if ( ! modes.hasOwnProperty( mode ) ) {
			return;
		}

		if ( mode === PROCAPTCHAGeneralObject.modeLive ) {
			$siteKey.attr( 'disabled', false );
			$secretKey.attr( 'disabled', false );
		} else {
			$siteKey.attr( 'disabled', true );
			$secretKey.attr( 'disabled', true );
		}

		const sitekey = modes[ mode ];
		pCAPTCHAUpdate( { sitekey } );
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
};

window.pCAPTCHAGeneral = general;

jQuery( document ).ready( general );
