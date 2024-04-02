/* global Marionette, Backbone, ProcaptchaAdminNFObject */

/**
 * @param ProcaptchaAdminNFObject.onlyOneProcaptchaAllowed
 */

document.addEventListener( 'DOMContentLoaded', function() {
	const nfRadio = Backbone.Radio;
	const fieldClass = 'procaptcha-for-ninja-forms';
	const dataId = fieldClass;
	const fieldSelector = '.' + fieldClass;
	let hasObserver = false;

	const ProcaptchaAdminFieldController = Marionette.Object.extend( {
		initialize() {
			document.getElementById( 'nf-builder' ).addEventListener( 'mousedown', this.checkAddingProcaptcha, true );

			const appChannel = nfRadio.channel( 'app' );

			this.listenTo( appChannel, 'click:edit', this.editField );
			this.listenTo( appChannel, 'click:closeDrawer', this.closeDrawer );

			const fieldsChannel = nfRadio.channel( 'fields' );

			this.listenTo( fieldsChannel, 'add:field', this.addField );
		},

		/**
		 * Check adding procap_ and prevent from having multiple procap_ fields.
		 *
		 * @param {Object} e Click event.
		 */
		checkAddingProcaptcha( e ) {
			const buttonClicked = e.target.dataset.id === dataId;
			const classList = e.target.classList;
			const duplicateClicked = classList !== undefined && classList.contains( 'nf-duplicate' );

			if ( ! ( buttonClicked || duplicateClicked ) ) {
				return;
			}

			const field = document.querySelector( fieldSelector );

			if ( field ) {
				e.stopImmediatePropagation();

				// eslint-disable-next-line no-alert
				alert( ProcaptchaAdminNFObject.onlyOneProcaptchaAllowed );
			}
		},

		/**
		 * On edit field event, update procap_.
		 * Do it if the drawer was opened to edit procap_.
		 *
		 * @param {Object} e Event.
		 */
		editField( e ) {
			const field = e.target.parentNode;

			if ( field.classList === undefined || ! field.classList.contains( fieldClass ) ) {
				return;
			}

			this.observeField();
		},

		/**
		 * On closing the drawer, update procap_ field in the form.
		 * Do it if the drawer was opened to edit procap_.
		 */
		closeDrawer() {
			const field = document.querySelector( fieldSelector + '.active' );

			if ( ! field ) {
				return;
			}

			this.observeField();
		},

		/**
		 * Check adding field and update procap_.
		 */
		addField() {
			const field = document.querySelector( fieldSelector );

			if ( ! field ) {
				return;
			}

			this.observeField();
		},

		/**
		 * Observe adding of a field to the form and bind procap_ events.
		 */
		observeField() {
			if ( hasObserver ) {
				return;
			}

			hasObserver = true;

			const callback = ( mutationList ) => {
				for ( const mutation of mutationList ) {
					[ ...mutation.addedNodes ].map( ( node ) => {
						if (
							document.querySelector( '.procaptcha' ) &&
							! document.querySelector( '.procaptcha iframe' )
						) {
							window.procap_BindEvents();
						}

						return node;
					} );
				}
			};

			const config = {
				childList: true,
				subtree: true,
			};
			const observer = new MutationObserver( callback );

			observer.observe( document.getElementById( 'nf-main-body' ), config );
		},
	} );

	// Instantiate our custom field's controller, defined above.
	window.ProcaptchaAdminFieldController = new ProcaptchaAdminFieldController();
} );
