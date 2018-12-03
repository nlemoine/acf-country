# ACF Country field

[![Latest Version](https://img.shields.io/packagist/v/hellonico/acf-country.svg?style=flat-square)](https://github.com/nlemoine/acf-country/releases)
[![Packagist](https://img.shields.io/packagist/dt/hellonico/acf-country.svg?style=flat-square)](https://packagist.org/packages/hellonico/acf-country)
[![Beerpay](https://beerpay.io/nlemoine/acf-country/badge.svg?style=flat-square)](https://beerpay.io/nlemoine/acf-country)
[![Donate](https://img.shields.io/badge/Donate-PayPal-blue.svg?style=flat-square)](https://paypal.me/hellonico)

Adds a 'Country' field type for the [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/) WordPress plugin.

## ⚠️ WARNING ⚠️

**From version 2.0.0, ACF Country introduced some important breaking changes**:

- Dropped support for older PHP & ACF versions, new requirements are:
	- ACF 5.7+ 
	- PHP 5.4+
- Return format has changed. To better stick to ACF and make use of ACF functions, ACF Country will now return values the same way select field do. `['FR' => 'France']` will now look like `['label' => 'France', 'value' => 'FR']`

Looking for a ACF pre 5.7 support? Check the [1.0 branch](https://github.com/nlemoine/acf-country/tree/1.0). 

### Overview

Display a select list of all countries in your language.

Country names are available in every language ([see available list](https://github.com/umpirsky/country-list/tree/master/data)). By default, country names are localized in your current WordPress language.

Select a single value:

![ACF Country field](https://cloud.githubusercontent.com/assets/2526939/24555414/5c045c7c-1631-11e7-815a-35b6b6903e36.png)

Or multiple ones:

![ACF Country field](https://cloud.githubusercontent.com/assets/2526939/24555413/5bf05402-1631-11e7-8d7e-74d425a3eae4.png)

### Compatibility

- ACF 5.7+ 
- PHP 5.4+


### Field options

| Option  | Default | Description |
| ------------- | ------------- | ------------- |
| Default value | emtpy | Set a default value for the country field (as country code)  |
| Allow null | `false` | Enable/disable null value  |
| Allow multiple | `false` | Enable/disable multiple countries selection  |
| Stylised UI | `true` | Enable/disable enhanced select field thanks to [Select2](https://select2.github.io/)  |
| Return format | `value` | See [ACF Select field](https://www.advancedcustomfields.com/resources/select/) |

### Filters

You can remove (or add) some countries with the `acf/country/countries` filter, example:

```php 
add_filter( 'acf/country/countries', function( $countries ) {
	return array_filter( $countries, function( $code ) {
		return !in_array( $code, ['IC', 'EA'], true );
	}, ARRAY_FILTER_USE_KEY);
} );
```
*Note: PHP5.6+ example*

### Installation

#### Zip

[Download the plugin](https://github.com/nlemoine/acf-country/releases/latest) and extract the archive to your plugins folder.

#### Composer

```bash
composer require hellonico/acf-country
```

### Contributing

See [CONTRIBUTING](CONTRIBUTING.MD).

### Support

This ACF field was originally developed for a personal project I don't use  anymore. I still decided to maintain it anyway. If you use it in a commercial project, please consider [buying me a beer](https://beerpay.io/nlemoine/acf-country).
