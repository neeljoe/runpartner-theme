module.exports = {
	plugins: {
		'postcss-import': {},
		'postcss-preset-env': {
			stage: 3,
			features: {
				'nesting-rules': true,
				'custom-media-queries': true,
			},
		},
		...(process.env.NODE_ENV === 'production' ? { cssnano: {} } : {}),
	},
};
