# Upgrading from 3.x to 4.0

Stored values, the `country` field type, return formats and the `acf/country/countries` filter are unchanged: sites using `get_field()` and field groups built in the ACF UI need no change.

## PHP 8.2 and WordPress 6.0

4.0 requires PHP 8.2 and WordPress 6.0. Sites on older versions can stay on 3.x.

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

The functions are available once plugins are loaded. With the bundled ZIP, call them from a hook such as `init`, not when a must-use plugin file loads: must-use plugins load before regular plugins.

## Return values

- `get_field()` with the `emoji` return format now returns an empty string for codes that are not in the country list. 3.x returned a flag for any two letters.
- Multiple values now skip empty and non-string items, and the returned list is re-indexed from 0.

## Classes extending CountryField

The `HelloNico\AcfCountry\CountryField` alias works for `instanceof` checks, type declarations, `new` and static calls, but subclasses written for 3.x must update their signatures. Overriding methods must add the new return types, and the 3.x protected `normalize_codes()`, `get_asset_url()`, `$uri` and `$path` members are no longer accessible to subclasses: they are now private, and the two helper methods were renamed. Match the signatures of `n5s\AcfCountry\Field\CountryField`.

The protected `$select` property type changed from `acf_field` to `acf_field_select`: a subclass redeclaring it must use the new type.

The constructor stays compatible: `new CountryField($uri, $path)` with the 3.x arguments still works, and 4.0 adds an optional third `?Countries $countries` parameter.

## WPGraphQL

The integration with the archived WPGraphQL for ACF 0.x was replaced by support for WPGraphQL for ACF 2+. Country fields are now exposed as an `AcfCountry` object (`code`, `name`, `emoji`), or a list of them for multiple fields, whatever the field's return format. Update your queries to select these subfields.

## Admin Columns Pro 6

The Admin Columns integration now supports Admin Columns 7+ (free or Pro) only. Stay on ACF Country 3.x for Admin Columns Pro 6.
