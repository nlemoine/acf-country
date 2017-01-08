=== Advanced Custom Fields: ACF Country Field ===
Contributors: Nicolas Lemoine
Tags: acf, advanced custom fields, country, world
Requires at least: 4.5
Tested up to: 4.7
Stable tag: 1.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add a country field to ACF (Advanced Custom Fields)

== Description ==

ACF country is a select field for Advanced Custom Fields, it displays a list of all countries in your langage.

= Development =

Plugin is hosted on [Github](https://github.com/nlemoine/acf-country).

= Compatibility =

This ACF field type is compatible with:
* ACF 5 (pro)
* ACF 4

== Installation ==

1. Copy the `acf-country` folder into your `wp-content/plugins` folder
2. Activate the ACF Country plugin via the plugins admin page
3. Create a new field via ACF and select the `country` type
4. Please refer to the description for more info regarding the field type settings

== Changelog ==

= 1.1.0 =
* Switched from symfony/intl to umpirsky/country-list to avoid ICU dependency
* Allow multiple values
* Support for ACF v5/Pro
* Added flags
* Added options
* Improved UI

= 1.0.0 =
* Initial Release.
