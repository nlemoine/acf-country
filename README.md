# ACF Country field

[![Latest Version](https://img.shields.io/packagist/v/hellonico/acf-country.svg?style=flat-square)](https://github.com/nlemoine/acf-country/releases)
[![Packagist](https://img.shields.io/packagist/dt/hellonico/acf-country.svg?style=flat-square)](https://packagist.org/packages/hellonico/acf-country)
[![Donate](https://img.shields.io/badge/Donate-PayPal-blue.svg?style=flat-square)](https://paypal.me/hellonico)
[![Try in WordPress Playground](https://img.shields.io/badge/Try%20in-WordPress%20Playground-3858e9?style=flat-square&logo=wordpress)](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/nlemoine/acf-country/4.x/blueprint.json)

Adds a 'Country' field type for the [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/) WordPress plugin (free or PRO).

### Overview

Display a select list of all countries in your language.

Country names are available in every language ([see available list](https://github.com/nlemoine/acf-country/tree/4.x/data), generated from [Unicode CLDR](https://cldr.unicode.org/) via [symfony/intl](https://github.com/symfony/intl)). By default, country names are localized in your current WordPress language.

Select a single value:

![ACF Country field](https://cloud.githubusercontent.com/assets/2526939/24555414/5c045c7c-1631-11e7-815a-35b6b6903e36.png)

Or multiple ones:

![ACF Country field](https://cloud.githubusercontent.com/assets/2526939/24555413/5bf05402-1631-11e7-8d7e-74d425a3eae4.png)

### Requirements

- WordPress 5.0+
- PHP 7.4+
- ACF 6.0+ (free or PRO)

### Field options

| Option | Default | Description |
| ------------- | ------------- | ------------- |
| Default value | empty | Default country, as a country code (e.g. `FR`) |
| Allow null | `false` | Allow an empty value |
| Select multiple values | `false` | Allow selecting several countries |
| Stylized UI | `false` | Use an enhanced select field ([Select2](https://select2.org/)) |
| Return format | `array` | See below |

### Return formats

Values are stored as [ISO 3166-1 alpha-2](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2) codes. `get_field()` returns, for France:

| Return format | Value |
| ------------- | ------------- |
| Country code and name (`array`) | `['value' => 'FR', 'label' => 'France']` |
| Country code (`value`) | `'FR'` |
| Country name (`name`) | `'France'` |
| Country emoji flag (`emoji`) | `'🇫🇷'` |

With multiple values, you get an array of those. Country names use the site language.

Since codes are stored, you can query posts by country:

```php
$events = new WP_Query([
    'post_type' => 'event',
    'meta_query' => [['key' => 'country', 'value' => 'FR']],
]);
```

For a multiple field, values are serialized: compare with `'LIKE'` and `'"FR"'`.

### Filters

You can remove (or add) some countries with the `acf/country/countries` filter, example:

```php
add_filter('acf/country/countries', static function (array $countries): array {
    unset($countries['IC'], $countries['EA']);

    return $countries;
});
```

The filter also receives the locale of the list as a second argument. Admin screens use the language of the logged-in user, while values returned by `get_field()` use the site language.

### Integrations

- **REST API**: the field schema lists the country codes, and invalid codes are rejected.
- **[Admin Columns](https://wordpress.org/plugins/codepress-admin-columns/) 7+** (free or Pro): custom field columns show flags and country names. Admin Columns Pro 6 columns show the formatted value.
- **[WPGraphQL for ACF](https://github.com/wp-graphql/wp-graphql-acf) 0.x** (the original, now archived plugin): country fields are exposed with the `value`, `array` and `name` return formats. WPGraphQL for ACF 2.x is not supported yet.

### Installation

#### Zip

[Download the plugin](https://github.com/nlemoine/acf-country/releases/latest) and extract the archive to your plugins folder.

#### Composer

```bash
composer require hellonico/acf-country
```

### Contributing

See [CONTRIBUTING](CONTRIBUTING.md).

### Support

This ACF field was originally developed for a personal project I don't use anymore. I still decided to maintain it anyway. If you use it in a commercial project, please consider [supporting it on Open Collective](https://opencollective.com/n5s).
