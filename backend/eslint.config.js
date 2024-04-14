const { FlatCompat } = require('@eslint/eslintrc');
const js             = require('@eslint/js');

const compat = new FlatCompat({
	baseDirectory:            __dirname,                  // optional; default: process.cwd()
	resolvePluginsRelativeTo: __dirname,       // optional
	recommendedConfig:        js.configs.recommended, // optional unless using "eslint:recommended"
});

module.exports = [
	...compat.config({


		'env': {
			'node': true,
			'es6':  true
		},
		'extends': [
			'eslint:recommended'
		],
		'globals': {
			'Atomics':           'readonly',
			'SharedArrayBuffer': 'readonly'
		},
		'parserOptions': {
			'ecmaVersion': 2018,
			'sourceType':  'module'
		},
		'plugins': [
			'align-assignments'
		],
		'rules': {
			'arrow-parens': [
				'error',
				'always'
			],
			'indent': [
				'error',
				'tab'
			],
			'linebreak-style': [
				'error',
				'unix'
			],
			'quotes': [
				'error',
				'single'
			],
			'semi': [
				'error',
				'always'
			],
			'key-spacing': [
				'error',
				{
					'align': 'value'
				}
			],
			'comma-spacing': [
				'error',
				{
					'before': false,
					'after':  true
				}
			],
			'func-call-spacing': [
				'error',
				'never'
			],
			'keyword-spacing': [
				'error',
				{
					'before': true
				}
			],
			'no-irregular-whitespace':             'error',
			'no-unused-expressions':               0,
			'align-assignments/align-assignments': [
				2,
				{
					'requiresOnly': false
				}
			]
		}

	})
];
