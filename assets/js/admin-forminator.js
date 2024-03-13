/* global jQuery, PROCAPTCHAForminatorObject */

/**
 * @param PROCAPTCHAForminatorObject.noticeLabel
 * @param PROCAPTCHAForminatorObject.noticeDescription
 */
jQuery( document ).on( 'ajaxSuccess', function( event, xhr, settings ) {
	const params = new URLSearchParams( settings.data );

	if ( params.get( 'action' ) !== 'forminator_load_form' ) {
		return;
	}

	window.ProCaptchaBindEvents();
} );

jQuery( document ).ready( function( $ ) {
	if ( ! window.location.href.includes( 'page=forminator-settings' ) ) {
		return;
	}

	const $procaptchaTab = $( '#procaptcha-tab' );

	$procaptchaTab.find( '.sui-settings-label' ).first()
		.html( PROCAPTCHAForminatorObject.noticeLabel ).css( 'display', 'block' );
	$procaptchaTab.find( '.sui-description' ).first()
		.html( PROCAPTCHAForminatorObject.noticeDescription ).css( 'display', 'block' );
} );

document.addEventListener( 'DOMContentLoaded', function() {
	if ( ! window.location.href.includes( 'page=forminator-cform' ) ) {
		return;
	}

	const config = {
		attributes: true,
		subtree: true,
	};

	const callback = ( mutationList ) => {
		for ( const mutation of mutationList ) {
			if (
				! (
					mutation.type === 'attributes' &&
					mutation.target.id === 'forminator-field-procaptcha_size'
				)
			) {
				continue;
			}

			const ProCaptchaButton = document.querySelectorAll( '#forminator-modal-body--captcha .sui-tabs-content .sui-tabs-menu .sui-tab-item' )[ 1 ];

			if ( ProCaptchaButton === undefined || ! ProCaptchaButton.classList.contains( 'active' ) ) {
				return;
			}

			const content = ProCaptchaButton.closest( '.sui-tab-content' );

			const rows = content.querySelectorAll( '.sui-box-settings-row' );

			[ ...rows ].map( ( row, index ) => {
				if ( index === 1 ) {
					row.querySelector( '.sui-settings-label' ).innerHTML = PROCAPTCHAForminatorObject.noticeLabel;
					row.querySelector( '.sui-description' ).innerHTML = PROCAPTCHAForminatorObject.noticeDescription;
					row.querySelector( '.sui-form-field' ).style.display = 'none';
				}

				if ( index > 1 ) {
					row.style.display = 'none';
				}

				return row;
			} );

			return;
		}
	};

	const observer = new MutationObserver( callback );
	observer.observe( document.body, config );
} );
