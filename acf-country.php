<?php

/**
 * Plugin Name:       Advanced Custom Fields: ACF Country
 * Plugin URI:        https://github.com/nlemoine/acf-country
 * Description:       A country field for ACF. Display a select field of all countries, in any language.
 * x-release-please-start-version
 * Version:           3.1.0
 * x-release-please-end
 * Requires at least: 6.0
 * Requires PHP:      8.2
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

// The plugin file registers hooks next to their named callbacks.
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

namespace n5s\AcfCountry;

if (!\defined('ABSPATH')) {
    exit;
}

// @bundle-autoload

// Named functions rather than closures, so other plugins can remove these hooks.

// Before ACF registers field types on init 5: the field label is translated then.
\add_action('init', __NAMESPACE__ . '\\load_textdomain', 4);
\add_action('plugins_loaded', __NAMESPACE__ . '\\boot');

function load_textdomain(): void
{
    \load_plugin_textdomain('acf-country', false, \basename(__DIR__) . '/languages');
}

function boot(): void
{
    // The GitHub source archive and Composer sites that never load vendor/autoload.php have no autoloader.
    if (!\class_exists(Plugin::class)) {
        \add_action('admin_notices', __NAMESPACE__ . '\\missing_autoloader_notice');

        return;
    }

    Plugin::getInstance()->init();
}

function missing_autoloader_notice(): void
{
    if (!\current_user_can('activate_plugins')) {
        return;
    }

    \printf(
        '<div class="notice notice-error"><p>%s</p></div>',
        \esc_html__('ACF Country could not load its classes. Install the plugin from the acf-country.zip release asset, or load Composer\'s vendor/autoload.php.', 'acf-country')
    );
}
