# ACF Country field

[![GitHub issues](https://img.shields.io/github/issues/nlemoine/acf-country.svg?style=flat-square)](https://github.com/nlemoine/acf-country/issues)
[![Packagist](https://img.shields.io/packagist/dt/hellonico/acf-country.svg?style=flat-square)](https://packagist.org/packages/hellonico/acf-country)
[![Beerpay](https://beerpay.io/nlemoine/acf-country/badge.svg?style=flat-square)](https://beerpay.io/nlemoine/acf-country)

Adds a 'Country' field type for the [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/) WordPress plugin.

## ⚠️ Warning

From version 2.0, ACF Country will add some breaking changes. Keep an eye on the repository if you included an autoupdater.

### Overview

Display a select list of all countries in your language.

Country names are available in every language ([see available list](https://github.com/umpirsky/country-list/tree/master/data)). By default, country names are localized in your current WordPress language.

Select a single value:

![ACF Country field](https://cloud.githubusercontent.com/assets/2526939/24555414/5c045c7c-1631-11e7-815a-35b6b6903e36.png)

Or multiple ones:

![ACF Country field](https://cloud.githubusercontent.com/assets/2526939/24555413/5bf05402-1631-11e7-8d7e-74d425a3eae4.png)

### Compatibility

* ACF version 4
* ACF version 5/pro
* PHP 5.3 or higher

*Note: I [tried my best](https://github.com/nlemoine/acf-country/tree/master/assets/js) to support every ACF version since version 4. However, before 5.7, ACF didn't provide any convenient way to work with conditional logic. This feature may be broken in some ACF versions. Update ACF to the latest if you absolutely need it.*

### Field options

* Default value: set a default value for the country field (country code)
* Allow null: enable/disable null value (disabled by default, only apply when "allow multiple" is set to `false`)
* Allow multiple: enable/disable multiple countries selection (disabled by default)
* Stylised UI: enable/disable enhanced select field thanks to [Select2](https://select2.github.io/) (enabled by default)
* Return format:
	* country code and country name (default):
		* single: `array('FR' => 'France')`
		* multiple: `array('FR' => 'France', 'DE' => 'Germany')`
	* country code:
		* single: `FR`
		* multiple: `array('FR', 'DE', 'ES')`
	* country name:
		* single: `France`
		* multiple: `array('France', 'Germany', 'Spain')`

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
