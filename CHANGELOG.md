# Changelog

## [3.1.0](https://github.com/nlemoine/acf-country/compare/v3.0.1...v3.1.0) (2026-10-01)


### Features

* **admin-columns:** show country flags and names in Admin Columns 7 ([bd67a78](https://github.com/nlemoine/acf-country/commit/bd67a780e15b205bdeba07ee6496ca3be4d77212))
* **data:** generate country names from Unicode CLDR via symfony/intl ([340c5b0](https://github.com/nlemoine/acf-country/commit/340c5b015c5530198300c7cda3ba355fafdda57b))


### Bug Fixes

* **admin-columns:** keep supporting Admin Columns Pro 6 ([8705381](https://github.com/nlemoine/acf-country/commit/87053819a200d8a2b1d840b6bbf30e5f1f57ae53))
* **graphql:** resolve the "name" return format ([ff13fbc](https://github.com/nlemoine/acf-country/commit/ff13fbcbb9fc5fa3008894c13124ce64c369673e))
* **i18n:** fall back to less specific locales instead of the first two letters ([073901d](https://github.com/nlemoine/acf-country/commit/073901d022dc12e67203761c37f7788af6efc09f))
* **i18n:** fix typo in the invalid country code message ([30ca9f5](https://github.com/nlemoine/acf-country/commit/30ca9f571427209340eb19dd60b2eed8c3aac0ef))
* **i18n:** load translations from the languages directory ([ae01db1](https://github.com/nlemoine/acf-country/commit/ae01db1f447cdf2b640f8e1755bd6ab2e383a19a))
* **i18n:** show country names in the user language in the admin ([a2f657c](https://github.com/nlemoine/acf-country/commit/a2f657c2fc0170df644514d57d0059e4b64cd2fa))
* normalize country codes to uppercase ([4e8ffa4](https://github.com/nlemoine/acf-country/commit/4e8ffa4a583f75a6a70d2d4f27a8664b3a01442c))
* **rest:** validate values and describe them in the REST schema ([c8d09fc](https://github.com/nlemoine/acf-country/commit/c8d09fcd47e3fd87483213477047b5ca2bca2dbc))
* return no flag for codes that are not two letters ([efb961b](https://github.com/nlemoine/acf-country/commit/efb961bc2ac6a2d9a7d92eb6aa00d59507a79d83))
* return the country name for the "name" return format ([b948224](https://github.com/nlemoine/acf-country/commit/b948224cbafb21cb22d8e21e225e28ba1749809e))
* **validation:** only list invalid country codes in the error message ([840ebf2](https://github.com/nlemoine/acf-country/commit/840ebf21b0e4bcb6c87e5c96312f20433d3143c6))


### Miscellaneous Chores

* add release-please markers to the plugin version header ([bdb66ea](https://github.com/nlemoine/acf-country/commit/bdb66ea459939b25ad41879cc15e7d485944a3a8))

## [3.0.1](https://github.com/nlemoine/acf-country/compare/v3.0.0...v3.0.1) (2024-03-12)


### Bug Fixes

* ACF Column compatibility ([5f6c517](https://github.com/nlemoine/acf-country/commit/5f6c5170145103e4d0e03462251bec153f84dc13))

## [3.0.0](https://github.com/nlemoine/acf-country/compare/2.1.0...v3.0.0) (2024-02-23)


### ⚠ BREAKING CHANGES

* release 3.0

### Features

* release 3.0 ([7154518](https://github.com/nlemoine/acf-country/commit/715451866a0294f9f418c72790b9b192155c9382))


### Bug Fixes

* rename plugin file ([9067a66](https://github.com/nlemoine/acf-country/commit/9067a66859e4889e6a8fb6c749a984a899daba63))
* typed prop ([263c629](https://github.com/nlemoine/acf-country/commit/263c629f50251bb99ea624a883a2a0a6d6806f9b))


### Miscellaneous Chores

* cleanup ([8680aba](https://github.com/nlemoine/acf-country/commit/8680aba85a1ed433449bd3634d844dda39a77152))
