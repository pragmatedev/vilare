import { propertyGroups } from 'stylelint-config-clean-order';

export default {
  extends: [
    'stylelint-config-standard-scss',
    'stylelint-config-clean-order',
    '@stylistic/stylelint-config',
  ],
  rules: {
    'no-empty-source': null,
    'selector-class-pattern': null,
    'at-rule-no-unknown': null,
    'no-descending-specificity': null,
    'at-rule-empty-line-before': [
      'always',
      {
        except: ['blockless-after-blockless', 'first-nested'],
        ignore: ['after-comment'],
        ignoreAtRules: ['if', 'else'],
      },
    ],
    'order/properties-order': [
      propertyGroups.map(properties => ({
        noEmptyLineBetween: true,
        emptyLineBefore: 'never',
        properties,
      })),
      {
        severity: 'warning',
        unspecified: 'bottomAlphabetical',
      },
    ],
    '@stylistic/color-hex-case': 'upper',
    '@stylistic/string-quotes': 'single',
    '@stylistic/block-closing-brace-newline-after': [
      'always',
      {
        ignoreAtRules: ['if', 'else'],
      },
    ],
  },
};
