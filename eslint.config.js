import neostandard from 'neostandard';

export default [
  ...neostandard(),

  {
    rules: {
      curly: ['error', 'all'],
      'no-empty-function': ['error'],
      '@stylistic/comma-dangle': ['error', 'always-multiline'],
      '@stylistic/curly-newline': ['error', 'always'],
      '@stylistic/semi': ['error', 'always'],
      '@stylistic/space-before-function-paren': ['error', 'never'],
      '@stylistic/padding-line-between-statements': ['error', {
        blankLine: 'always',
        prev: '*',
        next: 'return',
      }],
    },
  },

  {
    ignores: ['dist', 'vendor', 'node_modules', '.output'],
  },
];
