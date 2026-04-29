const [scriptConfig, moduleConfig] = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = () => {
	return [
		{
			...scriptConfig,
			entry: {}
		},
		{
			...moduleConfig,
			entry: {
				'js/interactivity': path.resolve(process.cwd(), 'resources/js/interactivity.js')
			}
		}
	];
};