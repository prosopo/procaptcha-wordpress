/* global _, elementor, elementorPro, elementorModules */

/**
 * @param config.setup_message
 * @param config.site_key
 * @param config.procaptcha_theme
 * @param config.procaptcha_size
 * @param item.field_type
 * @param item.custom_id
 * @param item.css_classes
 */

class ProcaptchaAdminElementorPro extends elementorModules.editor.utils.Module {
	/**
	 * Get procaptcha form.
	 *
	 * @param {Object} item
	 *
	 * @return {string} procaptcha form.
	 */
	static getProcaptchaForm( item ) {
		const config = elementorPro.config.forms[ item.field_type ];

		if ( ! config.enabled ) {
			return (
				'<div class="elementor-alert elementor-alert-info">' +
				config.setup_message +
				'</div>'
			);
		}

		let procaptchaData = 'data-sitekey="' + config.site_key + '"';
		procaptchaData += ' data-theme="' + config.procaptcha_theme + '"';
		procaptchaData += ' data-size="' + config.procaptcha_size + '"';
		procaptchaData += ' data-auto="false"';

		return '<div class="procaptcha" ' + procaptchaData + '></div>';
	}

	renderField( inputField, item ) {
		inputField +=
			'<div class="elementor-field" id="form-field-' +
			item.custom_id +
			'">';
		inputField +=
			'<div class="elementor-procaptcha' +
			_.escape( item.css_classes ) +
			'">';
		inputField += ProcaptchaAdminElementorPro.getProcaptchaForm( item );
		inputField += '</div>';
		inputField += '</div>';
		return inputField;
	}

	filterItem( item ) {
		if ( 'procaptcha' === item.field_type ) {
			item.field_label = false;
		}

		return item;
	}

	onInit() {
		elementor.hooks.addFilter(
			'elementor_pro/forms/content_template/item',
			this.filterItem
		);
		elementor.hooks.addFilter(
			'elementor_pro/forms/content_template/field/procaptcha',
			this.renderField,
			10,
			2
		);
	}
}

window.procaptchaAdminElementorPro = new ProcaptchaAdminElementorPro();
