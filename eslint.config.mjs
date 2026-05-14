import js from '@eslint/js';
import globals from 'globals';
import eslintConfigPrettier from 'eslint-config-prettier';

export default [
    js.configs.recommended,

    {
        files: ['assets/**/*.js'],

        languageOptions: {
            sourceType: 'module',
            globals: {
                ...globals.browser,
            },
        },

        rules: {
            'no-var': 'error',
            'no-undef': 'error',

            'no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
            'prefer-const': 'warn',
            eqeqeq: 'warn',

            'no-console': 'off',
        },
    },

    eslintConfigPrettier,
];
