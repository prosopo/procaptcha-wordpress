/* global jQuery, ProcaptchaForminatorObject */

/**
 * @param ProcaptchaForminatorObject.noticeLabel
 * @param ProcaptchaForminatorObject.noticeDescription
 */
jQuery( document ).on( 'ajaxSuccess', function( event, xhr, settings ) {
	const params = new URLSearchParams( settings.data );

	if ( params.get( 'action' ) !== 'forminator_load_form' ) {
		return;
	}

	window.procap_BindEvents();
} );

jQuery( document ).ready( function( $ ) {
	if ( ! window.location.href.includes( 'page=forminator-settings' ) ) {
		return;
	}

	const $procaptchaTab = $( '#procaptcha-tab' );

	$procaptchaTab.find( '.sui-settings-label' ).first()
		.html( ProcaptchaForminatorObject.noticeLabel ).css( 'display', 'block' );
	$procaptchaTab.find( '.sui-description' ).first()
		.html( ProcaptchaForminatorObject.noticeDescription ).css( 'display', 'block' );
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

			const procap_Button = document.querySelectorAll( '#forminator-modal-body--captcha .sui-tabs-content .sui-tabs-menu .sui-tab-item' )[ 1 ];

			if ( procap_Button === undefined || ! procap_Button.classList.contains( 'active' ) ) {
				return;
			}

			const content = procap_Button.closest( '.sui-tab-content' );

			const rows = content.querySelectorAll( '.sui-box-settings-row' );

			[ ...rows ].map( ( row, index ) => {
				if ( index === 1 ) {
					row.querySelector( '.sui-settings-label' ).innerHTML = ProcaptchaForminatorObject.noticeLabel;
					row.querySelector( '.sui-description' ).innerHTML = ProcaptchaForminatorObject.noticeDescription;
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
