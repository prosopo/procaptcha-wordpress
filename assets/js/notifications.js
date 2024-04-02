/* global jQuery, ProcaptchaNotificationsObject */

/**
 * @param ProcaptchaNotificationsObject.ajaxUrl
 * @param ProcaptchaNotificationsObject.dismissNotificationAction
 * @param ProcaptchaNotificationsObject.dismissNotificationNonce
 * @param ProcaptchaNotificationsObject.resetNotificationAction
 * @param ProcaptchaNotificationsObject.resetNotificationNonce
 */

/**
 * Notifications logic.
 *
 * @param {Object} $ jQuery instance.
 */
const notifications = ( $ ) => {
	const optionsSelector = 'form#procaptcha-options';
	const sectionKeysSelector = 'h3.procaptcha-section-keys';
	const notificationsSelector = 'div#procaptcha-notifications';
	const notificationSelector = 'div.procaptcha-notification';
	const dismissSelector = notificationsSelector + ' button.notice-dismiss';
	const navPrevSelector = '#procaptcha-navigation .prev';
	const navNextSelector = '#procaptcha-navigation .next';
	const navSelectors = navPrevSelector + ', ' + navNextSelector;
	const buttonsSelector = '.procaptcha-notification-buttons';
	const resetBtnSelector = 'button#reset_notifications';
	const footerSelector = '#procaptcha-notifications-footer';
	let $notifications;

	const getVisibleNotificationIndex = function() {
		$notifications = $( notificationSelector );

		if ( ! $notifications.length ) {
			return false;
		}

		let index = 0;

		$notifications.each( function( i ) {
			if ( $( this ).is( ':visible' ) ) {
				index = i;
				return false;
			}
		} );

		return index;
	};

	const setNavStatus = function() {
		const index = getVisibleNotificationIndex();

		if ( index >= 0 ) {
			$( navSelectors ).removeClass( 'disabled' );
		} else {
			$( navSelectors ).addClass( 'disabled' );
			return;
		}

		if ( index === 0 ) {
			$( navPrevSelector ).addClass( 'disabled' );
		}

		if ( index === $notifications.length - 1 ) {
			$( navNextSelector ).addClass( 'disabled' );
		}
	};

	const setButtons = function() {
		const index = getVisibleNotificationIndex();

		$( footerSelector ).find( buttonsSelector ).remove();

		if ( index < 0 ) {
			return;
		}

		$( $notifications[ index ] ).find( buttonsSelector ).clone().removeClass( 'hidden' ).prependTo( footerSelector );
	};

	$( optionsSelector ).on( 'click', dismissSelector, function( event ) {
		const $notification = $( event.target ).closest( notificationSelector );

		const data = {
			action: ProcaptchaNotificationsObject.dismissNotificationAction,
			nonce: ProcaptchaNotificationsObject.dismissNotificationNonce,
			id: $notification.data( 'id' ),
		};

		let next = $( notificationSelector ).index( $notification ) + 1;
		next = next < $( notificationSelector ).length ? next : 0;
		const $next = $( notificationSelector ).eq( next );

		$notification.remove();
		$next.show();

		setNavStatus();
		setButtons();

		if ( $( notificationSelector ).length === 0 ) {
			$( notificationsSelector ).remove();
		}

		// noinspection JSVoidFunctionReturnValueUsed,JSCheckFunctionSignatures
		$.post( {
			url: ProcaptchaNotificationsObject.ajaxUrl,
			data,
		} );

		return false;
	} );

	$( optionsSelector ).on( 'click', navSelectors, function( event ) {
		let direction = 1;

		if ( $( event.target ).hasClass( 'prev' ) ) {
			direction = -1;
		}

		const index = getVisibleNotificationIndex();

		const newIndex = index + direction;

		if ( index >= 0 && newIndex !== index && newIndex >= 0 && newIndex < $notifications.length ) {
			$( $notifications[ index ] ).hide();
			$( $notifications[ newIndex ] ).show();
			setNavStatus();
			setButtons();
		}
	} );

	$( resetBtnSelector ).on( 'click', function() {
		const data = {
			action: ProcaptchaNotificationsObject.resetNotificationAction,
			nonce: ProcaptchaNotificationsObject.resetNotificationNonce,
		};

		// noinspection JSVoidFunctionReturnValueUsed,JSCheckFunctionSignatures
		$.post( {
			url: ProcaptchaNotificationsObject.ajaxUrl,
			data,
		} ).success( function( response ) {
			if ( ! response.success ) {
				return;
			}

			$( notificationsSelector ).remove();
			$( response.data ).insertBefore( sectionKeysSelector );

			setButtons();
			$( document ).trigger( 'wp-updates-notice-added' );
		} );
	} );

	setButtons();
};

jQuery( document ).ready( notifications );
