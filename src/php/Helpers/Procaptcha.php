<?php
/**
 * Procaptcha class file.
 *
 * @package procaptcha-wp
 */

namespace Procaptcha\Helpers;

use Procaptcha\Vendor\MatthiasMullie\Minify\CSS;
use Procaptcha\Vendor\MatthiasMullie\Minify\JS;
use WP_Error;

/**
 * Class Procaptcha.
 */
class Procaptcha {

	/**
	 * Widget id.
	 */
	const PROCAPTCHA_WIDGET_ID = 'procaptcha-widget-id';

	/**
	 * Signature prefix.
	 */
	const PROCAPTCHA_SIGNATURE = 'procaptcha-signature';

	/**
	 * Default widget id.
	 *
	 * @var array
	 */
	private static $default_id = [
		'source'  => [],
		'form_id' => 0,
	];

	/**
	 * Get procap_ form.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string
	 */
	public static function form( array $args = [] ): string {
		ob_start();
		self::form_display( $args );

		return (string) ob_get_clean();
	}

	/**
	 * Display procap_ form.
	 *
	 * @param array $args Arguments.
	 */
	public static function form_display( array $args = [] ) {
		$settings          = procaptcha()->settings();
		$procaptcha_site_key = $settings->get_site_key();
		$procaptcha_theme    = $settings->get_theme();
		$procaptcha_size     = $settings->get( 'size' );
		$allowed_sizes     = [ 'normal', 'compact', 'invisible' ];

		$args = wp_parse_args(
			$args,
			[
				'action'  => '', // Action name for wp_nonce_field.
				'name'    => '', // Nonce name for wp_nonce_field.
				'auto'    => false, // Whether a form has to be auto-verified.
				'force'   => false, // Whether to execute procap_ widget before submit (like for invisible).
				'size'    => $procaptcha_size, // The procap_ widget size.
				/**
				 * The procap_ widget id.
				 * Example of id:
				 * [
				 *   'source'  => ['gravityforms/gravityforms.php'],
				 *   'form_id' => 23
				 * ]
				 */
				'id'      => [],
				// Protection status. When true, procap_ should be added.
				'protect' => true,
			]
		);

		$args['action']  = (string) $args['action'];
		$args['name']    = (string) $args['name'];
		$args['auto']    = filter_var( $args['auto'], FILTER_VALIDATE_BOOLEAN );
		$args['force']   = filter_var( $args['force'], FILTER_VALIDATE_BOOLEAN );
		$args['size']    = in_array( $args['size'], $allowed_sizes, true ) ? $args['size'] : $procaptcha_size;
		$args['id']      = (array) $args['id'];
		$args['protect'] = filter_var( $args['protect'], FILTER_VALIDATE_BOOLEAN );

		$id = wp_parse_args(
			$args['id'],
			self::$default_id
		);

		self::display_widget( $id );

		procaptcha()->form_shown = true;

		/**
		 * Filters the protection status of a form.
		 *
		 * @param bool       $value   The protection status of a form.
		 * @param string[]   $source  The source of the form (plugin, theme, WordPress Core).
		 * @param int|string $form_id Form id.
		 */
		if (
			! $args['protect'] ||
			! apply_filters( 'procap_protect_form', true, $id['source'], $id['form_id'] )
		) {
			return;
		}

		?>
		<div
			class="procaptcha"
			data-sitekey="<?php echo esc_attr( $procaptcha_site_key ); ?>"
			data-theme="<?php echo esc_attr( $procaptcha_theme ); ?>"
			data-size="<?php echo esc_attr( $args['size'] ); ?>"
			data-auto="<?php echo $args['auto'] ? 'true' : 'false'; ?>"
			data-force="<?php echo $args['force'] ? 'true' : 'false'; ?>">
		</div>
		<?php

		if ( ! empty( $args['action'] ) && ! empty( $args['name'] ) ) {
			wp_nonce_field( $args['action'], $args['name'] );
		}
	}

	/**
	 * Display widget.
	 *
	 * @param array|mixed $id The procap_ widget id.
	 *
	 * @return void
	 */
	private static function display_widget( array $id ) {
		$id['source']  = (array) ( $id['source'] ?? [] );
		$id['form_id'] = $id['form_id'] ?? 0;

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$encoded_id = base64_encode( wp_json_encode( $id ) );
		$widget_id  = $encoded_id . '-' . wp_hash( $encoded_id );

		?>
		<input
				type="hidden"
				class="<?php echo esc_attr( self::PROCAPTCHA_WIDGET_ID ); ?>"
				name="<?php echo esc_attr( self::PROCAPTCHA_WIDGET_ID ); ?>"
				value="<?php echo esc_attr( $widget_id ); ?>">
		<?php
	}

	/**
	 * Display signature.
	 *
	 * @param string     $class_name     Class name.
	 * @param int|string $form_id        Form id.
	 * @param bool       $procaptcha_shown The procap_ was shown.
	 *
	 * @return void
	 */
	public static function display_signature( string $class_name, $form_id, bool $procaptcha_shown ) {

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$name = self::PROCAPTCHA_SIGNATURE . '-' . base64_encode( $class_name );

		?>
		<input
				type="hidden"
				class="<?php echo esc_attr( self::PROCAPTCHA_SIGNATURE ); ?>"
				name="<?php echo esc_attr( $name ); ?>"
				value="<?php echo esc_attr( self::encode_signature( $class_name, $form_id, $procaptcha_shown ) ); ?>">
		<?php
	}

	/**
	 * Check signature.
	 *
	 * @param string     $class_name Class name.
	 * @param int|string $form_id    Form id.
	 *
	 * @return bool|null True if signature is valid, false if not or does not exist. Null if valid and procap_ was shown.
	 */
	public static function check_signature( string $class_name, $form_id ) {
		$info = self::decode_id_info(
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			self::PROCAPTCHA_SIGNATURE . '-' . base64_encode( $class_name )
		);

		if (
			$form_id !== $info['id']['form_id'] ||
			self::get_class_source( $class_name ) !== $info['id']['source'] ||
			wp_hash( $info['encoded_id'] ) !== $info['hash']
		) {
			return false;
		}

		return $info['id']['procaptcha_shown'] ? null : true;
	}

	/**
	 * Whether form protection is enabled/disabled via procap_ widget id.
	 *
	 * Return false(protection disabled) in only one case:
	 * when $_POST['procaptcha-widget-id'] contains encoded id with proper hash
	 * and procap_protect_form filter confirms that form referenced in widget id is not protected.
	 *
	 * @return bool
	 */
	public static function is_protection_enabled(): bool {
		$info = self::decode_id_info();

		$id         = $info['id'];
		$encoded_id = $info['encoded_id'];
		$hash       = $info['hash'];

		return ! (
			wp_hash( $encoded_id ) === $hash &&
			! apply_filters( 'procap_protect_form', true, $id['source'], $id['form_id'] )
		);
	}

	/**
	 * Get procaptcha widget id from $_POST.
	 *
	 * @return array
	 */
	public static function get_widget_id(): array {
		return self::decode_id_info()['id'];
	}

	/**
	 * Get source which class serves.
	 *
	 * @param string $class_name Class name.
	 *
	 * @return array
	 */
	public static function get_class_source( string $class_name ): array {
		foreach ( procaptcha()->modules as $module ) {
			if ( in_array( $class_name, (array) $module[2], true ) ) {
				$source = $module[1];

				// For WP Core (empty $source string), return option value.
				return '' === $source ? [ 'WordPress' ] : (array) $source;
			}
		}

		return [];
	}

	/**
	 * Get procap_ plugin notice.
	 *
	 * @return string[]
	 * @noinspection HtmlUnknownTarget
	 */
	public static function get_procaptcha_plugin_notice(): array {
		$url                   = admin_url( 'options-general.php?page=procaptcha&tab=general' );
		$notice['label']       = esc_html__( 'procap_ plugin is active', 'procaptcha-wordpress' );
		$notice['description'] = wp_kses_post(
			sprintf(
			/* translators: 1: link to the General setting page */
				__( 'When procap_ plugin is active and integration is on, procap_ settings must be modified on the %1$s.', 'procaptcha-wordpress' ),
				sprintf(
					'<a href="%1$s" target="_blank">%2$s</a>',
					esc_url( $url ),
					__( 'General settings page', 'procaptcha-wordpress' )
				)
			)
		);

		return $notice;
	}

	/**
	 * Retrieves the number of times a filter has been applied during the current request.
	 *
	 * Introduced in WP 6.1.0.
	 *
	 * @global int[] $wp_filters Stores the number of times each filter was triggered.
	 *
	 * @param string $hook_name The name of the filter hook.
	 * @return int The number of times the filter hook has been applied.
	 */
	public static function did_filter( string $hook_name ): int {
		global $wp_filters;

		return $wp_filters[ $hook_name ] ?? 0;
	}

	/**
	 * Add procap_ error message to WP_Error object.
	 *
	 * @param WP_Error|mixed $errors        A WP_Error object containing any errors.
	 * @param string|null    $error_message Error message.
	 *
	 * @return WP_Error
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public static function add_error_message( $errors, $error_message ): WP_Error {
		if ( null === $error_message ) {
			return $errors;
		}

		$code = array_search( $error_message, procap_get_error_messages(), true ) ?: 'fail';

		$errors = is_wp_error( $errors ) ? $errors : new WP_Error();

		if ( ! isset( $errors->errors[ $code ] ) || ! in_array( $error_message, $errors->errors[ $code ], true ) ) {
			$errors->add( $code, $error_message );
		}

		return $errors;
	}

	/**
	 * Display CSS.
	 *
	 * @param string $css  CSS.
	 * @param bool   $wrap Wrap by <style>...</style> tags.
	 *
	 * @return void
	 */
	public static function css_display( string $css, bool $wrap = true ) {
		if ( $wrap ) {
			echo "<style>\n";
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::css_minify( $css ) . "\n";

		if ( $wrap ) {
			echo "</style>\n";
		}
	}

	/**
	 * Minify CSS.
	 *
	 * @param string $css CSS.
	 *
	 * @return string
	 */
	public static function css_minify( string $css ): string {
		if ( defined( 'SCRIPT_DEBUG' ) && constant( 'SCRIPT_DEBUG' ) ) {
			return $css;
		}

		$minifier = new CSS();

		$minifier->add( $css );

		return $minifier->minify();
	}

	/**
	 * Display JavaScript.
	 *
	 * @param string $js   JavaScript.
	 * @param bool   $wrap Wrap by <script>...</script> tags.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public static function js_display( string $js, bool $wrap = true ) {
		if ( $wrap ) {
			echo "<script>\n";
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::js_minify( $js ) . "\n";

		if ( $wrap ) {
			echo "</script>\n";
		}
	}

	/**
	 * Minify JavaScript.
	 *
	 * @param string $js JavaScript.
	 *
	 * @return string
	 */
	public static function js_minify( string $js ): string {
		if ( defined( 'SCRIPT_DEBUG' ) && constant( 'SCRIPT_DEBUG' ) ) {
			return $js;
		}

		$minifier = new JS();

		$minifier->add( $js );

		return $minifier->minify();
	}

	/**
	 * Convert WP locale to procap_ locale.
	 *
	 * @return string
	 */
	public static function get_procap_locale(): string {

		// To get all WP locales, use the following statement on the https://translate.wordpress.org/ page
		// and remove all double quotes.
		// phpcs:disable Squiz.Commenting.InlineComment.InvalidEndChar
		// [...document.querySelectorAll( '.locale')].map( (l) => { return "'" + l.querySelector('.english a').text + "' => '" + l.querySelector('.code a').text + "'" } )
		// phpcs:enable Squiz.Commenting.InlineComment.InvalidEndChar
		$wp_locales =
			[
				'Afrikaans'                        => 'af',
				'Albanian'                         => 'sq',
				'Algerian Arabic'                  => 'arq',
				'Amharic'                          => 'am',
				'Arabic'                           => 'ar',
				'Aragonese'                        => 'arg',
				'Armenian'                         => 'hy',
				'Arpitan'                          => 'frp',
				'Assamese'                         => 'as',
				'Asturian'                         => 'ast',
				'Azerbaijani'                      => 'az',
				'Azerbaijani (Turkey)'             => 'az_TR',
				'Balochi Southern'                 => 'bcc',
				'Bashkir'                          => 'ba',
				'Basque'                           => 'eu',
				'Belarusian'                       => 'bel',
				'Bengali (Bangladesh)'             => 'bn_BD',
				'Bengali (India)'                  => 'bn_IN',
				'Bhojpuri'                         => 'bho',
				'Bodo'                             => 'brx',
				'Borana-Arsi-Guji Oromo'           => 'gax',
				'Bosnian'                          => 'bs_BA',
				'Breton'                           => 'bre',
				'Bulgarian'                        => 'bg_BG',
				'Catalan'                          => 'ca',
				'Catalan (Balear)'                 => 'bal',
				'Catalan (Valencian)'              => 'ca_valencia',
				'Cebuano'                          => 'ceb',
				'Chinese (China)'                  => 'zh_CN',
				'Chinese (Hong Kong)'              => 'zh_HK',
				'Chinese (Singapore)'              => 'zh_SG',
				'Chinese (Taiwan)'                 => 'zh_TW',
				'Cornish'                          => 'cor',
				'Corsican'                         => 'co',
				'Croatian'                         => 'hr',
				'Czech'                            => 'cs_CZ',
				'Danish'                           => 'da_DK',
				'Dhivehi'                          => 'dv',
				'Dutch'                            => 'nl_NL',
				'Dutch (Belgium)'                  => 'nl_BE',
				'Dzongkha'                         => 'dzo',
				'Emoji'                            => 'art_xemoji',
				'English (Australia)'              => 'en_AU',
				'English (Canada)'                 => 'en_CA',
				'English (New Zealand)'            => 'en_NZ',
				'English (Pirate)'                 => 'art_xpirate',
				'English (South Africa)'           => 'en_ZA',
				'English (UK)'                     => 'en_GB',
				'Esperanto'                        => 'eo',
				'Estonian'                         => 'et',
				'Ewe'                              => 'ewe',
				'Faroese'                          => 'fo',
				'Finnish'                          => 'fi',
				'Fon'                              => 'fon',
				'French (Belgium)'                 => 'fr_BE',
				'French (Canada)'                  => 'fr_CA',
				'French (France)'                  => 'fr_FR',
				'Frisian'                          => 'fy',
				'Friulian'                         => 'fur',
				'Fulah'                            => 'fuc',
				'Galician'                         => 'gl_ES',
				'Georgian'                         => 'ka_GE',
				'German'                           => 'de_DE',
				'German (Austria)'                 => 'de_AT',
				'German (Switzerland)'             => 'de_CH',
				'Greek'                            => 'el',
				'Greenlandic'                      => 'kal',
				'Gujarati'                         => 'gu',
				'Haitian Creole'                   => 'hat',
				'Hausa'                            => 'hau',
				'Hawaiian'                         => 'haw_US',
				'Hazaragi'                         => 'haz',
				'Hebrew'                           => 'he_IL',
				'Hindi'                            => 'hi_IN',
				'Hungarian'                        => 'hu_HU',
				'Icelandic'                        => 'is_IS',
				'Ido'                              => 'ido',
				'Igbo'                             => 'ibo',
				'Indonesian'                       => 'id_ID',
				'Irish'                            => 'ga',
				'Italian'                          => 'it_IT',
				'Japanese'                         => 'ja',
				'Javanese'                         => 'jv_ID',
				'Kabyle'                           => 'kab',
				'Kannada'                          => 'kn',
				'Karakalpak'                       => 'kaa',
				'Kazakh'                           => 'kk',
				'Khmer'                            => 'km',
				'Kinyarwanda'                      => 'kin',
				'Korean'                           => 'ko_KR',
				'Kurdish (Kurmanji)'               => 'kmr',
				'Kurdish (Sorani)'                 => 'ckb',
				'Kyrgyz'                           => 'kir',
				'Lao'                              => 'lo',
				'Latin'                            => 'la',
				'Latvian'                          => 'lv',
				'Ligurian'                         => 'lij',
				'Limburgish'                       => 'li',
				'Lingala'                          => 'lin',
				'Lithuanian'                       => 'lt_LT',
				'Lombard'                          => 'lmo',
				'Lower Sorbian'                    => 'dsb',
				'Luganda'                          => 'lug',
				'Luxembourgish'                    => 'lb_LU',
				'Macedonian'                       => 'mk_MK',
				'Maithili'                         => 'mai',
				'Malagasy'                         => 'mg_MG',
				'Malay'                            => 'ms_MY',
				'Malayalam'                        => 'ml_IN',
				'Maltese'                          => 'mlt',
				'Maori'                            => 'mri',
				'Marathi'                          => 'mr',
				'Mauritian Creole'                 => 'mfe',
				'Mongolian'                        => 'mn',
				'Montenegrin'                      => 'me_ME',
				'Moroccan Arabic'                  => 'ary',
				'Myanmar (Burmese)'                => 'my_MM',
				'Nepali'                           => 'ne_NP',
				'Nigerian Pidgin'                  => 'pcm',
				'Norwegian (Bokmål)'               => 'nb_NO',
				'Norwegian (Nynorsk)'              => 'nn_NO',
				'N’ko'                             => 'nqo',
				'Occitan'                          => 'oci',
				'Oriya'                            => 'ory',
				'Ossetic'                          => 'os',
				'Panjabi (India)'                  => 'pa_IN',
				'Papiamento (Aruba)'               => 'pap_AW',
				'Papiamento (Curaçao and Bonaire)' => 'pap_CW',
				'Pashto'                           => 'ps',
				'Persian'                          => 'fa_IR',
				'Persian (Afghanistan)'            => 'fa_AF',
				'Picard'                           => 'pcd',
				'Polish'                           => 'pl_PL',
				'Portuguese (Angola)'              => 'pt_AO',
				'Portuguese (Brazil)'              => 'pt_BR',
				'Portuguese (Portugal)'            => 'pt_PT',
				'Rohingya'                         => 'rhg',
				'Romanian'                         => 'ro_RO',
				'Romansh'                          => 'roh',
				'Russian'                          => 'ru_RU',
				'Sakha'                            => 'sah',
				'Sanskrit'                         => 'sa_IN',
				'Saraiki'                          => 'skr',
				'Sardinian'                        => 'srd',
				'Scottish Gaelic'                  => 'gd',
				'Serbian'                          => 'sr_RS',
				'Shona'                            => 'sna',
				'Shqip (Kosovo)'                   => 'sq_XK',
				'Sicilian'                         => 'scn',
				'Silesian'                         => 'szl',
				'Sindhi'                           => 'snd',
				'Sinhala'                          => 'si_LK',
				'Slovak'                           => 'sk_SK',
				'Slovenian'                        => 'sl_SI',
				'Somali'                           => 'so_SO',
				'South Azerbaijani'                => 'azb',
				'Spanish (Argentina)'              => 'es_AR',
				'Spanish (Chile)'                  => 'es_CL',
				'Spanish (Colombia)'               => 'es_CO',
				'Spanish (Costa Rica)'             => 'es_CR',
				'Spanish (Dominican Republic)'     => 'es_DO',
				'Spanish (Ecuador)'                => 'es_EC',
				'Spanish (Guatemala)'              => 'es_GT',
				'Spanish (Honduras)'               => 'es_HN',
				'Spanish (Mexico)'                 => 'es_MX',
				'Spanish (Peru)'                   => 'es_PE',
				'Spanish (Puerto Rico)'            => 'es_PR',
				'Spanish (Spain)'                  => 'es_ES',
				'Spanish (Uruguay)'                => 'es_UY',
				'Spanish (Venezuela)'              => 'es_VE',
				'Sundanese'                        => 'su_ID',
				'Swahili'                          => 'sw',
				'Swati'                            => 'ssw',
				'Swedish'                          => 'sv_SE',
				'Syriac'                           => 'syr',
				'Tagalog'                          => 'tl',
				'Tahitian'                         => 'tah',
				'Tajik'                            => 'tg',
				'Tamazight'                        => 'zgh',
				'Tamazight (Central Atlas)'        => 'tzm',
				'Tamil'                            => 'ta_IN',
				'Tamil (Sri Lanka)'                => 'ta_LK',
				'Tatar'                            => 'tt_RU',
				'Telugu'                           => 'te',
				'Thai'                             => 'th',
				'Tibetan'                          => 'bo',
				'Tigrinya'                         => 'tir',
				'Turkish'                          => 'tr_TR',
				'Turkmen'                          => 'tuk',
				'Tweants'                          => 'twd',
				'Uighur'                           => 'ug_CN',
				'Ukrainian'                        => 'uk',
				'Upper Sorbian'                    => 'hsb',
				'Urdu'                             => 'ur',
				'Uzbek'                            => 'uz_UZ',
				'Venetian'                         => 'vec',
				'Vietnamese'                       => 'vi',
				'Welsh'                            => 'cy',
				'Wolof'                            => 'wol',
				'Xhosa'                            => 'xho',
				'Yoruba'                           => 'yor',
				'Zulu'                             => 'zul',
			];

		// To get all procap_ locales, use the following statement on the https://docs.procaptcha.io/languages page
		// and remove all double quotes.
		// phpcs:disable Squiz.Commenting.InlineComment.InvalidEndChar
		// [...document.querySelectorAll('table tbody tr')].map( r => { return ' + r.querySelector('td:nth-of-type(1)').innerText + ' => ' + r.querySelector('td:nth-of-type(2)').innerText + ' })
		// phpcs:enable Squiz.Commenting.InlineComment.InvalidEndChar
		$procaptcha_locales = [
			'Afrikaans'           => 'af',
			'Albanian'            => 'sq',
			'Amharic'             => 'am',
			'Arabic'              => 'ar',
			'Armenian'            => 'hy',
			'Azerbaijani'         => 'az',
			'Basque'              => 'eu',
			'Belarusian'          => 'be',
			'Bengali'             => 'bn',
			'Bulgarian'           => 'bg',
			'Bosnian'             => 'bs',
			'Burmese'             => 'my',
			'Catalan'             => 'ca',
			'Cebuano'             => 'ceb',
			'Chinese'             => 'zh',
			'Chinese Simplified'  => 'zh-CN',
			'Chinese Traditional' => 'zh-TW',
			'Corsican'            => 'co',
			'Croatian'            => 'hr',
			'Czech'               => 'cs',
			'Danish'              => 'da',
			'Dutch'               => 'nl',
			'English'             => 'en',
			'Esperanto'           => 'eo',
			'Estonian'            => 'et',
			'Farsi'               => 'fa',
			'Finnish'             => 'fi',
			'French'              => 'fr',
			'Frisian'             => 'fy',
			'Gaelic'              => 'gd',
			'Galician'            => 'gl',
			'Georgian'            => 'ka',
			'German'              => 'de',
			'Greek'               => 'el',
			'Gujarati'            => 'gu',
			'Haitian'             => 'ht',
			'Hausa'               => 'ha',
			'Hawaiian'            => 'haw',
			'Hebrew'              => 'he',
			'Hindi'               => 'hi',
			'Hmong'               => 'hmn',
			'Hungarian'           => 'hu',
			'Icelandic'           => 'is',
			'Igbo'                => 'ig',
			'Indonesian'          => 'id',
			'Irish'               => 'ga',
			'Italian'             => 'it',
			'Japanese'            => 'ja',
			'Javanese'            => 'jw',
			'Kannada'             => 'kn',
			'Kazakh'              => 'kk',
			'Khmer'               => 'km',
			'Kinyarwanda'         => 'rw',
			'Kirghiz'             => 'ky',
			'Korean'              => 'ko',
			'Kurdish'             => 'ku',
			'Lao'                 => 'lo',
			'Latin'               => 'la',
			'Latvian'             => 'lv',
			'Lithuanian'          => 'lt',
			'Luxembourgish'       => 'lb',
			'Macedonian'          => 'mk',
			'Malagasy'            => 'mg',
			'Malay'               => 'ms',
			'Malayalam'           => 'ml',
			'Maltese'             => 'mt',
			'Maori'               => 'mi',
			'Marathi'             => 'mr',
			'Mongolian'           => 'mn',
			'Nepali'              => 'ne',
			'Norwegian'           => 'no',
			'Nyanja'              => 'ny',
			'Oriya'               => 'or',
			'Persian'             => 'fa',
			'Polish'              => 'pl',
			'Portuguese'          => 'pt',
			'Pashto'              => 'ps',
			'Punjabi'             => 'pa',
			'Romanian'            => 'ro',
			'Russian'             => 'ru',
			'Samoan'              => 'sm',
			'Shona'               => 'sn',
			'Sindhi'              => 'sd',
			'Sinhala'             => 'si',
			'Serbian'             => 'sr',
			'Slovak'              => 'sk',
			'Slovenian'           => 'sl',
			'Somali'              => 'so',
			'Southern Sotho'      => 'st',
			'Spanish'             => 'es',
			'Sundanese'           => 'su',
			'Swahili'             => 'sw',
			'Swedish'             => 'sv',
			'Tagalog'             => 'tl',
			'Tajik'               => 'tg',
			'Tamil'               => 'ta',
			'Tatar'               => 'tt',
			'Telugu'              => 'te',
			'Thai'                => 'th',
			'Turkish'             => 'tr',
			'Turkmen'             => 'tk',
			'Uyghur'              => 'ug',
			'Ukrainian'           => 'uk',
			'Urdu'                => 'ur',
			'Uzbek'               => 'uz',
			'Vietnamese'          => 'vi',
			'Welsh'               => 'cy',
			'Xhosa'               => 'xh',
			'Yiddish'             => 'yi',
			'Yoruba'              => 'yo',
			'Zulu'                => 'zu',
		];

		$wp_locale = get_locale();

		$locale = str_replace( '_', '-', $wp_locale );

		if ( in_array( $locale, $procaptcha_locales, true ) ) {
			return $locale;
		}

		$locale_arr = explode( '-', $locale );

		if ( ( count( $locale_arr ) > 1 ) && in_array( $locale_arr[0], $procaptcha_locales, true ) ) {
			return $locale_arr[0];
		}

		$lang_name = array_search( $wp_locale, $wp_locales, true );

		if ( false === $lang_name ) {
			return '';
		}

		if ( array_key_exists( $lang_name, $procaptcha_locales ) ) {
			return $procaptcha_locales[ $lang_name ];
		}

		$lang_name = explode( ' (', $lang_name, 2 )[0];

		return $procaptcha_locales[ $lang_name ] ?? '';
	}

	/**
	 * Get procap_ hashed id info from $_POST.
	 *
	 * @param string $hashed_id_field Hashed id field name in $_POST array.
	 *
	 * @return array
	 */
	public static function decode_id_info( string $hashed_id_field = '' ): array {
		$hashed_id_field = $hashed_id_field ?: self::PROCAPTCHA_WIDGET_ID;

		// Nonce is checked in procaptcha_verify_post().
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$hashed_id = isset( $_POST[ $hashed_id_field ] ) ?
			filter_var( wp_unslash( $_POST[ $hashed_id_field ] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ) :
			'';
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( ! $hashed_id ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			$encoded_id = base64_encode( wp_json_encode( self::$default_id ) );
			$hash       = wp_hash( $encoded_id );

			return [
				'id'         => self::$default_id,
				'encoded_id' => $encoded_id,
				'hash'       => $hash,
			];
		}

		list( $encoded_id, $hash ) = explode( '-', $hashed_id );

		$id = wp_parse_args(
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			(array) json_decode( base64_decode( $encoded_id ), true ),
			self::$default_id
		);

		return [
			'id'         => $id,
			'encoded_id' => $encoded_id,
			'hash'       => $hash,
		];
	}

	/**
	 * Encode signature.
	 *
	 * @param string     $class_name     Class name.
	 * @param int|string $form_id        Form id.
	 * @param bool       $procaptcha_shown The procap_ was shown.
	 *
	 * @return string
	 */
	private static function encode_signature( string $class_name, $form_id, bool $procaptcha_shown ): string {
		$id = [
			'source'         => self::get_class_source( $class_name ),
			'form_id'        => $form_id,
			'procaptcha_shown' => $procaptcha_shown,
		];

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$encoded_id = base64_encode( wp_json_encode( $id ) );

		return $encoded_id . '-' . wp_hash( $encoded_id );
	}
}
