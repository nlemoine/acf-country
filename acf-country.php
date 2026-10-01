<?php

/**
 * Plugin Name:       Advanced Custom Fields: ACF Country
 * Plugin URI:        https://github.com/nlemoine/acf-country
 * Description:       A country field for ACF. Display a select field of all countries, in any language.
 * x-release-please-start-version
 * Version:           3.1.0
 * x-release-please-end
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Nicolas Lemoine
 * Author URI:        https://github.com/nlemoine
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       acf-country
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/nlemoine/acf-country
 */

declare(strict_types=1);

use AC\Column\CustomFieldContext;
use ACA\ACF\Column;
use HelloNico\AcfCountry\CountryField;

add_action('after_setup_theme', new class () {
    /**
     * Invoke the plugin.
     */
    public function __invoke(): void
    {
        if (!class_exists('acf_field')) {
            return;
        }

        require_once __DIR__ . '/src/CountryField.php';

        add_action('acf/include_field_types', [$this, 'register_field']);

        add_filter('ac/column/render', [$this, 'admin_column'], 10, 3);
        add_filter('ac/column/value', [$this, 'admin_column_pro_6'], 10, 3);
        load_plugin_textdomain('acf-country', false, plugin_basename(__DIR__) . '/languages');
        add_filter('wpgraphql_acf_register_graphql_field', [$this, 'register_graphql_field'], 10, 4);
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

        switch ($acf_field['return_format']) {
            case 'array':
                $field_config = [
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
                ];
                break;
            case 'value':
                $field_config = [
                    'type' => empty($acf_field['multiple']) ? 'String' : [
                        'list_of' => 'String',
                    ],
                    'resolve' => static function ($root, $args, $context, $info) use ($resolve) {
                        $value = $resolve($root, $args, $context, $info);

                        return !empty($value) ? $value : null;
                    },
                ];
                break;
            case 'name':
            case 'label':
                $field_config = [
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
                ];
                break;
        }

        return $field_config;
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

    /**
     * Admin Columns Pro before 7: show the formatted value of country field columns.
     *
     * @param mixed      $value
     * @param int|string $id
     * @param mixed      $column
     *
     * @return mixed
     */
    public function admin_column_pro_6($value, $id, $column)
    {
        if (
            !$column instanceof Column
            || $column->get_field()->get_settings()['type'] !== 'country'
        ) {
            return $value;
        }

        return get_field($column->get_meta_key()) ?? $value;
    }

    /**
     * Show country flags and names in Admin Columns (7+) custom field columns.
     *
     * @param mixed $value
     * @param mixed $context
     * @param mixed $id
     *
     * @return mixed
     */
    public function admin_column($value, $context, $id)
    {
        if (!$context instanceof CustomFieldContext || !is_numeric($id)) {
            return $value;
        }

        // ACF post ID format for each Admin Columns meta type.
        $post_id = [
            'post' => (int) $id,
            'user' => 'user_' . $id,
            'term' => 'term_' . $id,
            'comment' => 'comment_' . $id,
        ][$context->get_meta_type()] ?? null;
        if ($post_id === null) {
            return $value;
        }

        $field = get_field_object($context->get_meta_key(), $post_id, false);
        $field_type = acf_get_field_type('country');
        if (!is_array($field) || $field['type'] !== 'country' || !$field_type instanceof CountryField) {
            return $value;
        }

        // Admin screens follow the language of the logged-in user.
        $countries = $field_type->get_countries_for_locale(determine_locale());
        $names = [];
        foreach ((array) $field['value'] as $code) {
            if (is_string($code) && isset($countries[$code])) {
                $names[] = trim($field_type->country_flag_emoji($code) . ' ' . $countries[$code]);
            }
        }

        return $names === [] ? $value : esc_html(implode(', ', $names));
    }
});
