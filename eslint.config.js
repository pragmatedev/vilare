import neostandard from 'neostandard';

export default [
  ...neostandard(),

  {
    rules: {
      '@stylistic/comma-dangle': ['error', 'always-multiline'],
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
