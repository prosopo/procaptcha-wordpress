<?php
/**
 * CF7Test class file.
 *
 * @package Procaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace Procaptcha\Tests\Integration\CF7;

use Procaptcha\CF7\CF7;
use Procaptcha\Tests\Integration\ProcaptchaPluginWPTestCase;
use Mockery;
use tad\FunctionMocker\FunctionMocker;
use WPCF7_FormTag;
use WPCF7_Submission;
use WPCF7_TagGenerator;
use WPCF7_Validation;

/**
 * Test CF7 class.
 *
 * @requires PHP >= 7.4
 *
 * @group    cf7
 */
class CF7Test extends ProcaptchaPluginWPTestCase {

	/**
	 * Plugin relative path.
	 *
	 * @var string
	 */
	protected static $plugin = 'contact-form-7/wp-contact-form-7.php';

	/**
	 * Tear down the test.
	 */
	public function tearDown(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		procaptcha()->form_shown = false;

		wp_deregister_script( 'procaptcha-script' );
		wp_dequeue_script( 'procaptcha-script' );

		parent::tearDown();
	}

	/**
	 * Test init_hooks().
	 */
	public function test_init_hooks() {
		$subject = new CF7();

		self::assertSame( 20, has_filter( 'do_shortcode_tag', [ $subject, 'wpcf7_shortcode' ] ) );
		self::assertTrue( shortcode_exists( 'cf7-procaptcha' ) );
		self::assertSame( 20, has_filter( 'wpcf7_validate', [ $subject, 'verify_procaptcha' ] ) );
		self::assertSame( 9, has_action( 'wp_print_footer_scripts', [ $subject, 'enqueue_scripts' ] ) );
	}

	/**
	 * Test wpcf7_shortcode().
	 *
	 * @param string $procaptcha_size Widget size/visibility.
	 *
	 * @dataProvider dp_test_wpcf7_shortcode
	 */
	public function test_wpcf7_shortcode( string $procaptcha_size ) {
		$output            =
			'<form>' .
			'<input type="submit" value="Send">' .
			'</form>';
		$tag               = 'contact-form-7';
		$form_id           = 177;
		$attr              = [ 'id' => $form_id ];
		$m                 = [
			'[contact-form-7 id="177" title="Contact form 1"]',
			'',
			'contact-form-7',
			'id="177" title="Contact form 1"',
			'',
			'',
			'',
		];
		$uniqid            = 'procaptchacf7-6004092a854114.24546665';
		$nonce             = wp_nonce_field( 'wp_rest', '_wpnonce', true, false );
		$procaptcha_site_key = 'some site key';
		$procaptcha_theme    = 'some theme';
		$id                = [
			'source'  => [ 'contact-form-7/wp-contact-form-7.php' ],
			'form_id' => $form_id,
		];

		update_option(
			'procaptcha_settings',
			[
				'site_key' => $procaptcha_site_key,
				'theme'    => $procaptcha_theme,
				'size'     => $procaptcha_size,
			]
		);

		procaptcha()->init_hooks();

		FunctionMocker::replace(
			'uniqid',
			static function ( $prefix, $more_entropy ) use ( $uniqid ) {
				if ( 'procaptchacf7-' === $prefix && $more_entropy ) {
					return $uniqid;
				}

				return null;
			}
		);

		$expected =
			'<form>' .
			'<span class="wpcf7-form-control-wrap" data-name="procap-cf7">' .
			$this->get_procaptchawidget( $id ) . '
				<span id="' . $uniqid . '" class="wpcf7-form-control procaptcha "
			data-sitekey="' . $procaptcha_site_key . '"
			data-theme="' . $procaptcha_theme . '"
			data-size="' . $procaptcha_size . '"
			data-auto="false"
			data-force="false">' . '
		</span>
		' . $nonce .
		'</span><input type="submit" value="Send">' .
		'</form>';

		$subject = new CF7();

		self::assertSame( $expected, $subject->wpcf7_shortcode( $output, $tag, $attr, $m ) );

		$output = str_replace( '<input', '[cf7-procaptcha]<input', $output );

		self::assertSame( $expected, $subject->wpcf7_shortcode( $output, $tag, $attr, $m ) );

		$output   = str_replace( '[cf7-procaptcha]', '[cf7-procaptcha form_id=' . $form_id . ' class:some-class]', $output );
		$expected = str_replace( 'procaptcha ', 'procaptcha some-class', $expected );

		self::assertSame( $expected, $subject->wpcf7_shortcode( $output, $tag, $attr, $m ) );
	}

	/**
	 * Data provide for test_wpcf7_shortcode().
	 *
	 * @return array
	 */
	public function dp_test_wpcf7_shortcode(): array {
		return [
			'visible'   => [ 'normal' ],
			'invisible' => [ 'invisible' ],
		];
	}

	/**
	 * Test wpcf7_shortcode() when NOT active.
	 */
	public function test_wpcf7_shortcode_when_NOT_active() {
		$output            =
			'<form>' .
			'<input type="submit" value="Send">' .
			'</form>';
		$form_id           = 177;
		$tag               = 'contact-form-7';
		$attr              = [ 'id' => $form_id ];
		$m                 = [
			'[contact-form-7 id="' . $form_id . '" title="Contact form 1"]',
			'',
			'contact-form-7',
			'id="177" title="Contact form 1"',
			'',
			'',
			'',
		];
		$uniqid            = 'procaptchacf7-6004092a854114.24546665';
		$procaptcha_site_key = 'some site key';
		$procaptcha_theme    = 'some theme';
		$procaptcha_size     = 'normal';

		update_option(
			'procaptcha_settings',
			[
				'site_key' => $procaptcha_site_key,
				'theme'    => $procaptcha_theme,
				'size'     => $procaptcha_size,
			]
		);

		procaptcha()->init_hooks();

		add_filter(
			'procaptchaprotect_form',
			static function ( $value, $source, $id ) use ( $form_id ) {
				if ( (int) $id === $form_id && in_array( 'contact-form-7/wp-contact-form-7.php', $source, true ) ) {
					return false;
				}

				return $value;
			},
			10,
			3
		);

		FunctionMocker::replace(
			'uniqid',
			static function ( $prefix, $more_entropy ) use ( $uniqid ) {
				if ( 'procaptchacf7-' === $prefix && $more_entropy ) {
					return $uniqid;
				}

				return null;
			}
		);

		$id       = [
			'source'  => [ 'contact-form-7/wp-contact-form-7.php' ],
			'form_id' => $form_id,
		];
		$expected =
			'<form><span class="wpcf7-form-control-wrap" data-name="procap-cf7">' .
			$this->get_procaptchawidget( $id ) . '
		</span><input type="submit" value="Send"></form>';

		$subject = new CF7();

		self::assertSame( $expected, $subject->wpcf7_shortcode( $output, $tag, $attr, $m ) );
	}

	/**
	 * Test procaptchacf7_verify_recaptcha().
	 *
	 * @noinspection PhpVariableIsUsedOnlyInClosureInspection
	 */
	public function test_procaptchacf7_verify_recaptcha() {
		$data              = [ 'procaptcha-response' => 'some response' ];
		$wpcf7_id          = 23;
		$procaptcha_site_key = 'some site key';
		$cf7_text          =
			'<form>' .
			'<input type="submit" value="Send">' .
			$procaptcha_site_key .
			'</form>';

		$submission = Mockery::mock( WPCF7_Submission::class );
		$submission->shouldReceive( 'get_posted_data' )->andReturn( $data );
		FunctionMocker::replace( 'WPCF7_Submission::get_instance', $submission );

		add_shortcode(
			'contact-form-7',
			static function ( $content ) use ( $wpcf7_id, $cf7_text ) {
				if ( $wpcf7_id === (int) $content['id'] ) {
					return $cf7_text;
				}

				return '';
			}
		);

		update_option( 'procaptcha_settings', [ 'site_key' => $procaptcha_site_key ] );

		procaptcha()->init_hooks();

		$this->prepare_procaptcha_request_verify( $data['procaptcha-response'] );

		$result = Mockery::mock( WPCF7_Validation::class );
		$tag    = Mockery::mock( WPCF7_FormTag::class );

		$subject = new CF7();

		self::assertSame( $result, $subject->verify_procaptcha( $result, $tag ) );
	}

	/**
	 * Test procaptchacf7_verify_recaptcha() without submission.
	 */
	public function test_procaptchacf7_verify_recaptcha_without_submission() {
		$result = Mockery::mock( WPCF7_Validation::class );
		$result->shouldReceive( 'invalidate' )->with(
			[
				'type' => 'procaptcha',
				'name' => 'procap-cf7',
			],
			'Please complete the procaptcha.'
		);

		$tag = Mockery::mock( WPCF7_FormTag::class );

		$subject = new CF7();

		self::assertSame( $result, $subject->verify_procaptcha( $result, $tag ) );
	}

	/**
	 * Test procaptchacf7_verify_recaptcha() without posted data.
	 */
	public function test_procaptchacf7_verify_recaptcha_without_posted_data() {
		$data       = [];
		$submission = Mockery::mock( WPCF7_Submission::class );
		$submission->shouldReceive( 'get_posted_data' )->andReturn( $data );
		FunctionMocker::replace( 'WPCF7_Submission::get_instance', $submission );

		$result = Mockery::mock( WPCF7_Validation::class );
		$result->shouldReceive( 'invalidate' )->with(
			[
				'type' => 'procaptcha',
				'name' => 'procap-cf7',
			],
			'Please complete the procaptcha.'
		);

		$tag = Mockery::mock( WPCF7_FormTag::class );

		$subject = new CF7();

		self::assertSame( $result, $subject->verify_procaptcha( $result, $tag ) );
	}

	/**
	 * Test procaptchacf7_verify_recaptcha() without site key.
	 */
	public function test_procaptchacf7_verify_recaptcha_without_site_key() {
		$data = [];

		$submission = Mockery::mock( WPCF7_Submission::class );
		$submission->shouldReceive( 'get_posted_data' )->andReturn( $data );
		FunctionMocker::replace( 'WPCF7_Submission::get_instance', $submission );

		$result = Mockery::mock( WPCF7_Validation::class );
		$result->shouldReceive( 'invalidate' )->with(
			[
				'type' => 'procaptcha',
				'name' => 'procap-cf7',
			],
			'Please complete the procaptcha.'
		);

		$tag = Mockery::mock( WPCF7_FormTag::class );

		$subject = new CF7();

		self::assertSame( $result, $subject->verify_procaptcha( $result, $tag ) );
	}

	/**
	 * Test procaptchacf7_verify_recaptcha() without response.
	 *
	 * @noinspection PhpVariableIsUsedOnlyInClosureInspection
	 */
	public function test_procaptchacf7_verify_recaptcha_without_response() {
		$data              = [];
		$wpcf7_id          = 23;
		$procaptcha_site_key = 'some site key';
		$cf7_text          =
			'<form>' .
			'<input type="submit" value="Send">' .
			$procaptcha_site_key .
			'</form>';

		$submission = Mockery::mock( WPCF7_Submission::class );
		$submission->shouldReceive( 'get_posted_data' )->andReturn( $data );
		FunctionMocker::replace( 'WPCF7_Submission::get_instance', $submission );

		add_shortcode(
			'contact-form-7',
			static function ( $content ) use ( $wpcf7_id, $cf7_text ) {
				if ( $wpcf7_id === (int) $content['id'] ) {
					return $cf7_text;
				}

				return '';
			}
		);

		update_option( 'procaptcha_settings', [ 'site_key' => $procaptcha_site_key ] );

		procaptcha()->init_hooks();

		$result = Mockery::mock( WPCF7_Validation::class );
		$tag    = Mockery::mock( WPCF7_FormTag::class );

		$result
			->shouldReceive( 'invalidate' )
			->with(
				[
					'type' => 'procaptcha',
					'name' => 'procap-cf7',
				],
				'Please complete the procaptcha.'
			)
			->once();

		$subject = new CF7();

		self::assertSame( $result, $subject->verify_procaptcha( $result, $tag ) );
	}

	/**
	 * Test procaptchacf7_verify_recaptcha() not verified.
	 *
	 * @noinspection PhpVariableIsUsedOnlyInClosureInspection
	 */
	public function test_procaptchacf7_verify_recaptcha_not_verified() {
		$data              = [ 'procaptcha-response' => 'some response' ];
		$wpcf7_id          = 23;
		$procaptcha_site_key = 'some site key';
		$cf7_text          =
			'<form>' .
			'<input type="submit" value="Send">' .
			$procaptcha_site_key .
			'</form>';

		$submission = Mockery::mock( WPCF7_Submission::class );
		$submission->shouldReceive( 'get_posted_data' )->andReturn( $data );
		FunctionMocker::replace( 'WPCF7_Submission::get_instance', $submission );

		add_shortcode(
			'contact-form-7',
			static function ( $content ) use ( $wpcf7_id, $cf7_text ) {
				if ( $wpcf7_id === (int) $content['id'] ) {
					return $cf7_text;
				}

				return '';
			}
		);

		update_option( 'procaptcha_settings', [ 'site_key' => $procaptcha_site_key ] );

		procaptcha()->init_hooks();

		$this->prepare_procaptcha_request_verify( $data['procaptcha-response'], false );

		$result = Mockery::mock( WPCF7_Validation::class );
		$tag    = Mockery::mock( WPCF7_FormTag::class );

		$result
			->shouldReceive( 'invalidate' )
			->with(
				[
					'type' => 'procaptcha',
					'name' => 'procap-cf7',
				],
				'The procaptcha is invalid.'
			)
			->once();

		$subject = new CF7();

		self::assertSame( $result, $subject->verify_procaptcha( $result, $tag ) );
	}

	/**
	 * Test procaptchacf7_enqueue_scripts().
	 */
	public function test_procaptchacf7_enqueue_scripts() {
		$procaptcha_size = 'normal';

		$subject = new CF7();

		$subject->enqueue_scripts();

		self::assertFalse( wp_script_is( CF7::HANDLE ) );

		ob_start();
		do_action( 'wp_print_footer_scripts' );
		ob_end_clean();

		self::assertFalse( wp_script_is( CF7::HANDLE ) );

		update_option( 'procaptcha_settings', [ 'size' => $procaptcha_size ] );

		procaptcha()->init_hooks();

		do_shortcode( '[cf7-procaptcha]' );

		ob_start();
		do_action( 'wp_print_footer_scripts' );
		ob_end_clean();

		self::assertTrue( wp_script_is( CF7::HANDLE ) );
	}

	/**
	 * Test print_inline_styles().
	 *
	 * @return void
	 */
	public function test_print_inline_styles() {
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

		$expected = <<<CSS
	span[data-name="procap-cf7"] .procaptcha {
		margin-bottom: 0;
	}

	span[data-name="procap-cf7"] ~ input[type="submit"],
	span[data-name="procap-cf7"] ~ button[type="submit"] {
		margin-top: 2rem;
	}
CSS;
		$expected = "<style>\n$expected\n</style>\n";

		$subject = new CF7();

		ob_start();

		$subject->print_inline_styles();

		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Test add_tag_generator_procaptcha().
	 *
	 * @return void
	 */
	public function test_add_tag_generator_procaptcha() {
		$subject = new CF7();

		require_once WPCF7_PLUGIN_DIR . '/admin/includes/tag-generator.php';

		$tag_generator = WPCF7_TagGenerator::get_instance();

		ob_start();
		$tag_generator->print_buttons();
		$buttons = ob_get_clean();

		self::assertFalse( strpos( $buttons, 'procaptcha' ) );

		$subject->add_tag_generator_procaptcha();

		ob_start();
		$tag_generator->print_buttons();
		$buttons = ob_get_clean();

		self::assertNotFalse( strpos( $buttons, 'procaptcha' ) );
	}

	/**
	 * Test tag_generator_procaptcha().
	 *
	 * @return void
	 */
	public function test_tag_generator_procaptcha() {
		$args     = [
			'id'      => 'cf7-procaptcha',
			'title'   => 'procaptcha',
			'content' => 'tag-generator-panel-cf7-procaptcha',
		];
		$expected = '		<div class="control-box">
			<fieldset>
				<legend>Generate a form-tag for a procaptcha field.</legend>

				<table class="form-table">
					<tbody>

					<tr>
						<th scope="row">
							<label for="tag-generator-panel-cf7-procaptcha-id">
								Id attribute							</label>
						</th>
						<td>
							<input
									type="text" name="id" class="idvalue oneline option"
									id="tag-generator-panel-cf7-procaptcha-id"/>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="tag-generator-panel-cf7-procaptcha-class">
								Class attribute							</label>
						</th>
						<td>
							<input
									type="text" name="class" class="classvalue oneline option"
									id="tag-generator-panel-cf7-procaptcha-class"/>
						</td>
					</tr>

					</tbody>
				</table>
			</fieldset>
		</div>

		<div class="insert-box">
			<label>
				<input
						type="text" name="cf7-procaptcha" class="tag code" readonly="readonly"
						onfocus="this.select()"/>
			</label>

			<div class="submitbox">
				<input
						type="button" class="button button-primary insert-tag"
						value="Insert Tag"/>
			</div>
		</div>
		';

		$subject = new CF7();

		ob_start();
		$subject->tag_generator_procaptcha( [], $args );
		self::assertSame( $expected, ob_get_clean() );
	}
}
