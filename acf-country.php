<?php

/**
 * Plugin Name:       Advanced Custom Fields: ACF Country
 * Plugin URI:        https://github.com/nlemoine/acf-country
 * Description:       A country field for ACF. Display a select field of all countries, in any language.
 * x-release-please-start-version
 * Version:           3.1.0
 * x-release-please-end
 * Requires at least: 5.0
 * Requires PHP:      8.1
 * Author:            Nicolas Lemoine
 * Author URI:        https://github.com/nlemoine
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       acf-country
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/nlemoine/acf-country
 * Primary Branch:    4.x
 * Release Asset:     true
 */

declare(strict_types=1);

namespace n5s\AcfCountry;

if (!\defined('ABSPATH')) {
    exit;
}

// @bundle-autoload

\add_action('init', static function (): void {
    \load_plugin_textdomain('acf-country', false, \basename(__DIR__) . '/languages');
});

\add_action('plugins_loaded', static function (): void {
    Plugin::getInstance()->init();
}, 0);
