# Changelog

## [4.0.0](https://github.com/nlemoine/acf-country/compare/v3.1.0...v4.0.0) (2026-10-01)


### ⚠ BREAKING CHANGES

* ACF Country 4.0 requires WordPress 6.0.
* ACF Country 4.0 requires PHP 8.2.
* **graphql:** the WPGraphQL for ACF 0.x integration is removed; query the code, name and emoji subfields instead.
* classes moved to the n5s\AcfCountry namespace, CountryField method signatures changed, and the plugin needs Composer autoloading or the bundled release ZIP.
* **admin-columns:** Admin Columns Pro versions before 7 are no longer supported.
* ACF Country now requires PHP 8.1.

### Features

* add get_countries(), get_country_name() and get_country_flag() ([3d1f401](https://github.com/nlemoine/acf-country/commit/3d1f401e9dc3da22354e92f2822302e3a97cad89))
* **graphql:** support WPGraphQL for ACF 2+ ([981bd94](https://github.com/nlemoine/acf-country/commit/981bd94cc35a6007a79997d7478087cda99f441b))


### Bug Fixes

* **admin-columns:** do nothing when ACF is inactive ([ed7fb7a](https://github.com/nlemoine/acf-country/commit/ed7fb7abee8ddba9a35cce8e3d4ccd56c1c1112f))
* declare the deprecated class alias eagerly ([401d04d](https://github.com/nlemoine/acf-country/commit/401d04d072dde9bf2800751d2bbb3f76c34e8e94))
* **graphql:** move the integration to Integration\WpGraphQl ([f08b031](https://github.com/nlemoine/acf-country/commit/f08b031ad3c7ad7840a0b682b6e1c9f118ec8968))
* **i18n:** load translations before ACF registers the field ([c963988](https://github.com/nlemoine/acf-country/commit/c963988b8bad670afe133c5657c02b91afae3c70))
* reject flag codes with a trailing newline ([a32ff38](https://github.com/nlemoine/acf-country/commit/a32ff383d1380b6173bce7e053335d8f217cf161))
* remove the trailing period from the GitHub Plugin URI header ([8eeb6c3](https://github.com/nlemoine/acf-country/commit/8eeb6c304be12a138da943ac822bb7c51ddf9789))
* **security:** anchor the locale check at the end of the string ([d1447d6](https://github.com/nlemoine/acf-country/commit/d1447d6446c2ff9a15ad6b78bdf8a18b03d758b7))
* **security:** reject malformed locales before loading country data ([01750b1](https://github.com/nlemoine/acf-country/commit/01750b136dc948b9ec48585ae7ddabebda4ac3d1))
* show an admin notice instead of a fatal error without an autoloader ([3b85443](https://github.com/nlemoine/acf-country/commit/3b8544357c364aa09bfc05b27b3184614f1e1727))
* use a prefixed handle for the field assets ([c17e099](https://github.com/nlemoine/acf-country/commit/c17e099b6b1b34b65c14929ce6ed00145cdd5669))


### Performance Improvements

* read the asset manifest once ([f941b8f](https://github.com/nlemoine/acf-country/commit/f941b8f647c4a7239d918274c2756a2af7e72f59))
* resolve each country data locale once ([8d9f70b](https://github.com/nlemoine/acf-country/commit/8d9f70b47f9cd1e454670235c5c83c190f1286e5))


### Code Refactoring

* **admin-columns:** move the integration to Integration\AdminColumns ([6168db0](https://github.com/nlemoine/acf-country/commit/6168db0470d6acc3ce12af21d872c12624757c70))
* move the field to n5s\AcfCountry\Field\CountryField ([2f84ded](https://github.com/nlemoine/acf-country/commit/2f84deda9ffafc347719f99dba7c3799f7e70320))


### Build System

* require PHP 8.1 ([5ad2506](https://github.com/nlemoine/acf-country/commit/5ad2506fe83dff1badbb942edb8cd7d6ed5a9a44))
* require PHP 8.2 ([0d30470](https://github.com/nlemoine/acf-country/commit/0d30470c97c262607970e7047dba6307c22c3f59))
* require WordPress 6.0 ([48c7875](https://github.com/nlemoine/acf-country/commit/48c7875784e6d5df52417353f349e9f6e103c022))

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
