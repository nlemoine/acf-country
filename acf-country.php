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
 */

declare(strict_types=1);

use HelloNico\AcfCountry\CountryField;
use n5s\AcfCountry\Plugin;

add_action('after_setup_theme', new class () {
    /**
     * Invoke the plugin.
     */
    public function __invoke(): void
    {
        if (!class_exists('acf_field')) {
            return;
        }

        Plugin::getInstance()->init();

        require_once __DIR__ . '/src/CountryField.php';

        add_action('acf/include_field_types', $this->register_field(...));

        load_plugin_textdomain('acf-country', false, plugin_basename(__DIR__) . '/languages');
    }

    public function register_field(): void
    {
        $field = new CountryField(
            untrailingslashit(plugin_dir_url(__FILE__)),
            untrailingslashit(plugin_dir_path(__FILE__))
        );
        acf_register_field_type($field);
    }
});
