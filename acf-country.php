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
        add_filter('wpgraphql_acf_register_graphql_field', $this->register_graphql_field(...), 10, 4);
    }

    public function register_field(): void
    {
        $field = new CountryField(
            untrailingslashit(plugin_dir_url(__FILE__)),
            untrailingslashit(plugin_dir_path(__FILE__))
        );
        acf_register_field_type($field);
    }

    /**
     * Register WPGraphQL field.
     *
     * @see https://github.com/wp-graphql/wp-graphql/issues/214#issuecomment-653141685
     *
     * @param array<string, mixed> $field_config
     * @param string $type_name
     * @param string $field_name
     * @param array<string, mixed> $config
     *
     * @return mixed
     */
    // phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    public function register_graphql_field($field_config, $type_name, $field_name, $config)
    {
        $acf_field = $config['acf_field'] ?? null;
        $acf_type = $acf_field['type'] ?? null;

        if ($acf_type !== 'country') {
            return $field_config;
        }

        $resolve = $field_config['resolve'];

        return match ($acf_field['return_format']) {
            'array' => [
                'type' => empty($acf_field['multiple']) ? [
                    'list_of' => 'String',
                ] : [
                    'list_of' => [
                        'list_of' => 'String',
                    ],
                ],
                'resolve' => static function ($root, $args, $context, $info) use ($resolve, $acf_field): array {
                    $value = $resolve($root, $args, $context, $info);

                    if (!empty($value)) {
                        if (is_array($value)) {
                            $values = [];

                            foreach ($value as $single_value) {
                                $values[] = [
                                    'value' => $single_value,
                                    'label' => $acf_field['choices'][$single_value],
                                ];
                            }

                            return $values;
                        }
                        return [
                            'value' => $value,
                            'label' => $acf_field['choices'][$value],
                        ];
                    }

                    return [];
                },
            ],
            'value' => [
                'type' => empty($acf_field['multiple']) ? 'String' : [
                    'list_of' => 'String',
                ],
                'resolve' => static function ($root, $args, $context, $info) use ($resolve) {
                    $value = $resolve($root, $args, $context, $info);

                    return !empty($value) ? $value : null;
                },
            ],
            'name', 'label' => [
                'type' => empty($acf_field['multiple']) ? 'String' : [
                    'list_of' => 'String',
                ],
                'resolve' => static function ($root, $args, $context, $info) use ($resolve, $acf_field) {
                    $value = $resolve($root, $args, $context, $info);

                    if (!empty($value)) {
                        if (is_array($value)) {
                            $values = [];

                            foreach ($value as $single_value) {
                                $values[] = $acf_field['choices'][$single_value];
                            }

                            return $values;
                        }
                        return $acf_field['choices'][$value];
                    }

                    return null;
                },
            ],
            default => $field_config,
        };
    }

    /**
     * Add ACF Country to WPGraphQL supported fields.
     *
     * @param string[] $supported_fields
     *
     * @return string[]
     */
    public function add_graphql_field_support($supported_fields)
    {
        $supported_fields[] = 'country';

        return $supported_fields;
    }
});
