# Upgrading from 3.x to 4.0

Stored values, the `country` field type, return formats and the `acf/country/countries` filter are unchanged: sites using `get_field()` and field groups built in the ACF UI need no change.

## PHP 8.1

4.0 requires PHP 8.1. Sites on older PHP versions can stay on 3.x.

## Installation without Composer

Install `acf-country.zip` from the release assets. The "Source code" archives GitHub adds to each release no longer work as a plugin: they lack the autoloader. Git Updater picks the release asset automatically.

## Namespace

Classes moved from `HelloNico\AcfCountry` to `n5s\AcfCountry`:

| 3.x | 4.0 |
|---|---|
| `HelloNico\AcfCountry\CountryField` | `n5s\AcfCountry\Field\CountryField` |

The old name still works as an alias in 4.x and will be removed in 5.0.

## Functions instead of field methods

| 3.x | 4.0 |
|---|---|
| `acf_get_field_type('country')->get_countries()` | `n5s\AcfCountry\get_countries()` |
| `acf_get_field_type('country')->get_countries_for_locale($locale)` | `n5s\AcfCountry\get_countries($locale)` |
| `acf_get_field_type('country')->country_flag_emoji($code)` | `n5s\AcfCountry\get_country_flag($code)` |
| `CountryField::FORMAT_NAME` (and other `FORMAT_*`) | `n5s\AcfCountry\ReturnFormat::Name->value` |

The old methods still work in 4.x and trigger a deprecation notice.

## Classes extending CountryField

The `HelloNico\AcfCountry\CountryField` alias covers `instanceof` checks and `new`, but not subclasses written for 3.x. Overriding methods must add the new return types, and the protected `normalize_codes()`, `get_asset_url()`, `$uri` and `$path` members of 3.x were removed. Match the signatures of `n5s\AcfCountry\Field\CountryField`.

## Admin Columns Pro 6

The Admin Columns integration now supports Admin Columns 7+ (free or Pro) only. Stay on ACF Country 3.x for Admin Columns Pro 6.
