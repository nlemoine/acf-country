# ACF { Country Field

Adds a 'Country' field type for the [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/) WordPress plugin.

-----------------------

### Overview

Display a localized list of all countries.

### Compatibility

This add-on will work with:

* ACF version 4 or higher
* PHP 5.3 or higher
* ICU library 4.4 or higher

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

### More Information

Please read the readme.txt file for more information
