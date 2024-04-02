// noinspection JSUnresolvedReference

const elementorPro = {
	config: {
		forms: {
			procaptcha: {
				enabled: true,
				setup_message: 'Setup message',
				site_key: 'test_site_key',
				procaptcha_theme: 'light',
				procaptcha_size: 'normal',
			},
		},
	},
};

global.elementorPro = elementorPro;

// noinspection JSUnusedGlobalSymbols
export default elementorPro;
