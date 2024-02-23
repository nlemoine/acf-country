<?php

declare(strict_types=1);

/**
 * Plugin Name:       Advanced Custom Fields: ACF Country
 * Plugin URI:        https://github.com/nlemoine/acf-country
 * Description:       A country field for ACF. Display a select field of all countries, in any language.
 * Version:           3.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Nicolas Lemoine
 * Author URI:        https://github.com/nlemoine
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       acf-country
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/nlemoine/acf-country.
 */

add_filter('after_setup_theme', new class() {
    /**
     * Invoke the plugin.
     */
    public function __invoke()
    {
        if (!class_exists('acf_field')) {
            return;
        }

        require_once __DIR__ . '/src/CountryField.php';

        add_filter('acf/include_field_types', [$this, 'register_field']);

        if (defined('ACP_FILE')) {
            add_filter('ac/column/value', [$this, 'admin_column'], 10, 3);
        }
        load_plugin_textdomain('acf-country', false, plugin_basename(dirname(__FILE__)) . '/lang');
        add_filter('wpgraphql_acf_register_graphql_field', [$this, 'register_graphql_field'], 10, 4);
    }

    public function register_field($acfMajorVersion)
    {
        $field = new HelloNico\AcfCountry\CountryField(
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
     * @param array  $field_config
     * @param string $type_name
     * @param string $field_name
     * @param array  $config
     *
     * @return mixed
     */
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
                    'type'    => empty($acf_field['multiple']) ? [
                        'list_of' => 'String',
                    ] : [
                        'list_of' => [
                            'list_of' => 'String',
                        ],
                    ],
                    'resolve' => function ($root, $args, $context, $info) use ($resolve, $acf_field) {
                        $value = $resolve($root, $args, $context, $info);

                        if (!empty($value)) {
                            if (is_array($value)) {
                                $values = [];

                                foreach ($value as $single_value) {
                                    array_push($values, [
                                        'value' => $single_value,
                                        'label' => $acf_field['choices'][$single_value],
                                    ]);
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
                    'type'    => empty($acf_field['multiple']) ? 'String' : [
                        'list_of' => 'String',
                    ],
                    'resolve' => function ($root, $args, $context, $info) use ($resolve) {
                        $value = $resolve($root, $args, $context, $info);

                        return !empty($value) ? $value : null;
                    },
                ];
                break;
            case 'label':
                $field_config = [
                    'type'    => empty($acf_field['multiple']) ? 'String' : [
                        'list_of' => 'String',
                    ],
                    'resolve' => function ($root, $args, $context, $info) use ($resolve, $acf_field) {
                        $value = $resolve($root, $args, $context, $info);

                        if (!empty($value)) {
                            if (is_array($value)) {
                                $values = [];

                                foreach ($value as $single_value) {
                                    array_push($values, $acf_field['choices'][$single_value]);
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
     * @param array $supported_fields
     *
     * @return array
     */
    public function add_graphql_field_support($supported_fields)
    {
        array_push($supported_fields, 'country');

        return $supported_fields;
    }

    /**
     * Hook the Admin Columns Pro plugin to provide basic field support
     * if detected on the current WordPress installation.
     */
    protected function admin_column($value, $id, $column)
    {
        if (
            !is_a($column, '\ACA\ACF\Column')
            || $column->get_acf_field_option('type') !== 'country'
        ) {
            return $value;
        }

        return get_field($column->get_meta_key()) ?? $value;
    }
});
