let originalStateChange;

function modifyResponse() {
	if ( this.readyState === XMLHttpRequest.DONE ) {
		[ ...document.getElementsByClassName( 'procaptcha' ) ].map( function( widget ) {
			window.procap_Reset( widget.closest( 'form' ) );

			return widget;
		} );
	}

	if ( originalStateChange ) {
		originalStateChange.apply( this, arguments );
	}
}

const originalSend = XMLHttpRequest.prototype.send;

XMLHttpRequest.prototype.send = function() {
	originalStateChange = this.onreadystatechange;
	this.onreadystatechange = modifyResponse;
	originalSend.apply( this, arguments );
};
