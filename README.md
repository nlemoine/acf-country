# ACF Country field

Adds a 'Country' field type for the [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/) WordPress plugin.

-----------------------

### Overview

Display a select list of all countries.

Country names are available in every language ([see available list](https://github.com/umpirsky/country-list/tree/master/data)). By default, country names are localized with current WordPress locale.

Select a single value:

![ACF Country field](https://dl.dropboxusercontent.com/u/54390968/dev/acf-country_single.png)

Or multiple ones:

![ACF Country field](https://dl.dropboxusercontent.com/u/54390968/dev/acf-country_multiple.png)

### Compatibility

This add-on will work with:

* ACF version 4
* ACF version 5 (pro)
* PHP 5.3 or higher

### Field options

* Allow null: enable/disable null value
* Allow multiple: enable/disable multiple countries selection
* Stylised UI: enable/disable enhanced select field
* Return format: country code, country name or both (as an array: `array('fr' => 'France')`)

### Installation

This add-on can be treated as both a WP plugin and a theme include.

**Install as Plugin**

1. Copy the 'acf-country' folder into your plugins folder
2. Activate the plugin via the Plugins admin page

**Include within theme**

1.	Copy the 'acf-country' folder into your theme folder (can use sub folders). You can place the folder anywhere inside the 'wp-content' directory
2.	Edit your functions.php file and add the code below (Make sure the path is correct to include the acf-country.php file)

```php
include_once('acf-country/acf-country.php');
```