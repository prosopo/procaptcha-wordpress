<?php
/**
 * DelayedScriptTest class file.
 *
 * @package Procaptcha\Tests
 */

namespace Procaptcha\Tests\Integration\DelayedScript;

use Procaptcha\DelayedScript\DelayedScript;
use Procaptcha\Tests\Integration\ProcaptchaWPTestCase;
use tad\FunctionMocker\FunctionMocker;

/**
 * Test DelayedScriptTest class.
 *
 * @group delayed-script
 */
class DelayedScriptTest extends ProcaptchaWPTestCase {

	/**
	 * Test create().
	 *
	 * @noinspection BadExpressionStatementJS
	 * @noinspection JSUnresolvedReference
	 * @noinspection JSUnusedLocalSymbols
	 */
	public function test_create() {
		FunctionMocker::replace(
			'defined',
			static function ( $constant_name ) {
				return 'SCRIPT_DEBUG' === $constant_name;
			}
		);

		FunctionMocker::replace(
			'constant',
			static function ( $name ) {
				return 'SCRIPT_DEBUG' === $name;
			}
		);

		$js = "\t\t\tconst some = 1;";

		$expected = <<<JS
	( () => {
		'use strict';

		let loaded = false,
			scrolled = false,
			timerId;

		function load() {
			if ( loaded ) {
				return;
			}

			loaded = true;
			clearTimeout( timerId );

			window.removeEventListener( 'touchstart', load );
			document.removeEventListener( 'mouseenter', load );
			document.removeEventListener( 'click', load );
			window.removeEventListener( 'load', delayedLoad );

			const some = 1;
		}

		function scrollHandler() {
			if ( ! scrolled ) {
				// Ignore first scroll event, which can be on page load.
				scrolled = true;
				return;
			}

			window.removeEventListener( 'scroll', scrollHandler );
			load();
		}

		function delayedLoad() {
			window.addEventListener( 'scroll', scrollHandler );
			// noinspection JSAnnotator
			const delay = 3000;

			if ( delay >= 0 ) {
				setTimeout( load, delay );
			}
		}

		window.addEventListener( 'touchstart', load );
		document.addEventListener( 'mouseenter', load );
		document.addEventListener( 'click', load );
		window.addEventListener( 'load', delayedLoad );
	} )();
JS;

		$expected = "<script>\n$expected\n</script>\n";

		self::assertSame( $expected, DelayedScript::create( $js ) );

		$expected = str_replace( '3000', '-1', $expected );

		self::assertSame( $expected, DelayedScript::create( $js, - 1 ) );
	}

	/**
	 * Test launch().
	 *
	 * @noinspection BadExpressionStatementJS
	 */
	public function test_launch() {
		FunctionMocker::replace(
			'defined',
			static function ( $constant_name ) {
				return 'SCRIPT_DEBUG' === $constant_name;
			}
		);

		FunctionMocker::replace(
			'constant',
			static function ( $name ) {
				return 'SCRIPT_DEBUG' === $name;
			}
		);

		$expected = <<<JS
	( () => {
		'use strict';

		let loaded = false,
			scrolled = false,
			timerId;

		function load() {
			if ( loaded ) {
				return;
			}

			loaded = true;
			clearTimeout( timerId );

			window.removeEventListener( 'touchstart', load );
			document.removeEventListener( 'mouseenter', load );
			document.removeEventListener( 'click', load );
			window.removeEventListener( 'load', delayedLoad );

			const t = document.getElementsByTagName( 'script' )[0];
			const s = document.createElement('script');
			s.type  = 'text/javascript';
			s.id = 'procaptcha-api';
			s['src'] = 'https://js.prosopo.io/js/procaptcha.bundle.js';
			s.async = true;
			t.parentNode.insertBefore( s, t );
		}

		function scrollHandler() {
			if ( ! scrolled ) {
				// Ignore first scroll event, which can be on page load.
				scrolled = true;
				return;
			}

			window.removeEventListener( 'scroll', scrollHandler );
			load();
		}

		function delayedLoad() {
			window.addEventListener( 'scroll', scrollHandler );
			// noinspection JSAnnotator
			const delay = 3000;

			if ( delay >= 0 ) {
				setTimeout( load, delay );
			}
		}

		window.addEventListener( 'touchstart', load );
		document.addEventListener( 'mouseenter', load );
		document.addEventListener( 'click', load );
		window.addEventListener( 'load', delayedLoad );
	} )();
JS;

		$expected = "<script>\n$expected\n</script>\n";

		$src  = 'https://js.prosopo.io/js/procaptcha.bundle.js';
		$args = [ 'src' => $src ];

		ob_start();
		DelayedScript::launch( $args );
		self::assertSame( $expected, ob_get_clean() );

		$expected = str_replace( '3000', '-1', $expected );

		ob_start();
		DelayedScript::launch( $args, - 1 );
		self::assertSame( $expected, ob_get_clean() );
	}
}
