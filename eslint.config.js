import js from '@eslint/js';
import prettier from 'eslint-config-prettier';
import react from 'eslint-plugin-react';
import reactHooks from 'eslint-plugin-react-hooks';
import globals from 'globals';
import tseslint from 'typescript-eslint';
import literalBaseline from './eslint.literal-baseline.json' with { type: 'json' };

export default tseslint.config(
    {
        ignores: [
            '.claude/**',
            '.pranata/**',
            'bootstrap/**',
            'docs/**',
            'node_modules/**',
            'public/**',
            'reference-inputs/**',
            'resources/js/actions/**',
            'resources/js/components/ui/**',
            'resources/js/routes/**',
            'resources/js/wayfinder/**',
            'storage/**',
            'vendor/**',
        ],
    },
    js.configs.recommended,
    ...tseslint.configs.recommended,
    {
        files: ['resources/js/**/*.{ts,tsx}'],
        plugins: { react, 'react-hooks': reactHooks },
        languageOptions: {
            globals: { ...globals.browser },
            parserOptions: { ecmaFeatures: { jsx: true } },
        },
        settings: { react: { version: 'detect' } },
        rules: {
            ...react.configs.recommended.rules,
            ...react.configs['jsx-runtime'].rules,
            'react/prop-types': 'off',
            'react-hooks/rules-of-hooks': 'error',
            'react-hooks/exhaustive-deps': 'error',
            // Text comes from the translation catalog (LOCALIZATION, TEST_STRATEGY section 1).
            'react/jsx-no-literals': [
                'error',
                { noStrings: false, ignoreProps: true },
            ],
        },
    },
    {
        // Pages as the starter kit ships them, with English text. TASK-006 moves their text
        // to the catalog and empties this list, so a new file can never join it.
        files: literalBaseline,
        rules: { 'react/jsx-no-literals': 'off' },
    },
    {
        files: ['scripts/**/*.mjs', '*.config.{js,ts}'],
        languageOptions: { globals: { ...globals.node } },
    },
    prettier,
);
