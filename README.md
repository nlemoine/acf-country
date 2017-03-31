# ACF Country field

Adds a 'Country' field type for the [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/) WordPress plugin.

-----------------------

### Overview

Display a select list of all countries in your language.

Country names are available in every language ([see available list](https://github.com/umpirsky/country-list/tree/master/data)). By default, country names are localized in your current WordPress language.

Select a single value:

![ACF Country field](https://cloud.githubusercontent.com/assets/2526939/24555414/5c045c7c-1631-11e7-815a-35b6b6903e36.png)

Or multiple ones:

![ACF Country field](https://cloud.githubusercontent.com/assets/2526939/24555413/5bf05402-1631-11e7-8d7e-74d425a3eae4.png)

### Compatibility

This add-on will work with:

* ACF version 4
* ACF version 5 (pro)
* PHP 5.3 or higher

### Field options

* Allow null: enable/disable null value (disabled by default)
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

[Download](https://github.com/nlemoine/acf-country/archive/master.zip) the plugin and extract the plugin to your plugins folder.

#### Composer

```bash
composer require hellonico/acf-country
```
