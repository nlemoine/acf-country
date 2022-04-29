<?php

namespace HelloNico\AcfCountry;

class CountryField extends \acf_field
{
    public const FORMAT_VALUE = 'value';
    public const FORMAT_ARRAY = 'array';
    public const FORMAT_NAME = 'name';
    public const FORMAT_EMOJI = 'emoji';
    public const FORMAT_FORMATS = [
        self::FORMAT_VALUE,
        self::FORMAT_ARRAY,
        self::FORMAT_NAME,
        self::FORMAT_EMOJI,
    ];

    protected string $path;
    protected string $uri;

    /**
     * Create a new field instance.
     *
     * @return void
     */
    public function __construct(string $uri, string $path)
    {
        $this->uri = $uri;
        $this->path = $path;
        parent::__construct();
    }

    public function initialize()
    {
        $this->name = 'country';
        $this->label = \__('Country', 'acf-country');
        $this->category = 'choice';
        $this->defaults = [
            'multiple'      => 0,
            'allow_null'    => 0,
            'choices'       => [],
            'default_value' => '',
            'layout'        => 'vertical',
            'ui'            => 0,
            'ajax'          => 0,
            'placeholder'   => '',
            'return_format' => self::FORMAT_ARRAY,
        ];
    }

    /**
     * The rendered field type.
     *
     * @param array $field
     *
     * @return void
     */
    public function render_field($field)
    {
        $countries = $this->get_countries();

        \array_walk($countries, function (&$name, $code) {
            $name = $this->country_flag_emoji($code).'  '.$name;
        });

        $field['choices'] = $countries;

        $field['ajax'] = 0;
        if ($field['value'] && \is_array($field['value'])) {
            $field['value'] = \array_map('strtoupper', $field['value']);
        }
        \acf_get_field_type('select')->render_field($field);
    }

    /**
     * The rendered field type settings.
     *
     * @param array $field
     *
     * @return void
     */
    public function render_field_settings($field)
    {
        $field['choices'] = \acf_encode_choices($this->get_countries());

        $field['default_value'] = \acf_encode_choices($field['default_value'], false);

        // choices
        \acf_render_field_setting($field, [
            'label'   => \__('Choices', 'acf'),
            'name'    => 'choices',
            'type'    => 'textarea',
            'wrapper' => [
                'class' => 'hidden',
            ],
        ]);

        // Placeholder
        \acf_render_field_setting($field, [
            'label'        => \__('Placeholder Text', 'acf-country'),
            'instructions' => \__('Appears within the input', 'acf-country'),
            'type'         => 'text',
            'name'         => 'placeholder',
        ]);

        // default_value
        \acf_render_field_setting($field, [
            'label'        => \__('Default Value', 'acf'),
            'instructions' => \__('Enter each default value on a new line', 'acf'),
            'name'         => 'default_value',
            'type'         => 'textarea',
        ]);

        // allow_null
        \acf_render_field_setting($field, [
            'label'        => \__('Allow Null?', 'acf'),
            'instructions' => '',
            'name'         => 'allow_null',
            'type'         => 'true_false',
            'ui'           => 1,
        ]);

        // multiple
        \acf_render_field_setting($field, [
                'label'        => \__('Select multiple values?', 'acf'),
                'instructions' => '',
                'name'         => 'multiple',
                'type'         => 'true_false',
                'ui'           => 1,
        ]);

        // ui
        \acf_render_field_setting($field, [
            'label'        => \__('Stylised UI', 'acf'),
            'instructions' => '',
            'name'         => 'ui',
            'type'         => 'true_false',
            'ui'           => 1,
        ]);

        // return_format
        \acf_render_field_setting($field, [
            'label'        => \__('Return Format', 'acf'),
            'instructions' => \__('Specify the value returned', 'acf'),
            'type'         => 'select',
            'name'         => 'return_format',
            'choices'      => [
                self::FORMAT_ARRAY => \__('Country code and name', 'acf-country'),
                self::FORMAT_VALUE => \__('Country code', 'acf-country'),
                self::FORMAT_NAME  => \__('Country name', 'acf-country'),
                self::FORMAT_EMOJI => \__('Country emoji flag', 'acf-country'),
            ],
        ]);
    }

    /**
     * The formatted field value.
     *
     * @param mixed $value
     * @param int   $post_id
     * @param array $field
     *
     * @return mixed
     */
    public function format_value($value, $post_id, $field)
    {
        $field['choices'] = $this->get_countries();

        // Set format to 'value' if 'emoji'
        $original_format = $field['return_format'];
        $field['return_format'] = self::FORMAT_EMOJI === $field['return_format'] ? self::FORMAT_VALUE : $field['return_format'];
        $value = \acf_get_field_type('select')->format_value($value, $post_id, $field);
        $field['return_format'] = $original_format;

        // Then convert to emoji
        if (self::FORMAT_EMOJI === $field['return_format'] && !empty($value)) {
            if (\is_array($value)) {
                $value = \array_map([$this, 'country_flag_emoji'], $value);
            } else {
                $value = $this->country_flag_emoji($value);
            }
        }

        return $value;
    }

    /**
     * The condition the field value must meet before
     * it is valid and can be saved.
     *
     * @param bool  $valid
     * @param mixed $value
     * @param array $field
     * @param array $input
     *
     * @return bool
     */
    public function validate_value($valid, $value, $field, $input)
    {
        if (empty($value)) {
            return $valid;
        }

        $countries = \array_keys($this->get_countries());
        if (\is_array($value)) {
            if (0 !== \count(\array_diff($value, $countries))) {
                /* translators: placeholder indicates the invalid country codes */
                $valid = \sprintf(\_n('%s is not valid a country code', '%s are not valid country codes', \count($value), 'acf-country'), \implode(', ', $value));
            }
        } elseif (\is_string($value)) {
            if (!\in_array($value, $countries, true)) {
                /* translators: placeholder indicates the invalid country code */
                $valid = \sprintf(\__('%s is not a valid country code', 'acf-country'), $value);
            }
        }

        return $valid;
    }

    /**
     * The field value after loading from the database.
     *
     * @param mixed $value
     * @param int   $post_id
     * @param array $field
     *
     * @return mixed
     */
    public function load_value($value, $post_id, $field)
    {
        return \acf_get_field_type('select')->load_value($value, $post_id, $field);
    }

    /**
     * The field value before saving to the database.
     *
     * @param mixed $value
     * @param int   $post_id
     * @param array $field
     *
     * @return mixed
     */
    public function update_value($value, $post_id, $field)
    {
        return \acf_get_field_type('select')->update_value($value, $post_id, $field);
    }

    /**
     * The action fired when deleting a field value from the database.
     *
     * @param int    $post_id
     * @param string $key
     *
     * @return void
     */
    public function delete_value($post_id, $key)
    {
        // delete_value($post_id, $key);
    }

    /**
     * The field after loading from the database.
     *
     * @param array $field
     *
     * @return array
     */
    public function load_field($field)
    {
        return $field;
    }

    /**
     * The field before saving to the database.
     *
     * @param array $field
     *
     * @return array
     */
    public function update_field($field)
    {
        return \acf_get_field_type('select')->update_field($field);
    }

    /**
     * The action fired when deleting a field from the database.
     *
     * @param array $field
     *
     * @return void
     */
    public function delete_field($field)
    {
        // parent::delete_field($field);
    }

    /**
     * The assets enqueued when rendering the field.
     *
     * @return void
     */
    public function input_admin_enqueue_scripts()
    {
        \acf_get_field_type('select')->input_admin_enqueue_scripts();
        \wp_enqueue_script($this->name, $this->get_asset_url('field.js'), ['jquery'], null, true);
    }

    /**
     * The assets enqueued when creating a field group.
     *
     * @return void
     */
    public function field_group_admin_enqueue_scripts()
    {
        $this->input_admin_enqueue_scripts();
    }

    /**
     * Get countries.
     *
     * @return array
     */
    protected function get_countries()
    {
        $wp_locale = \get_locale();

        // Try locales in that order
        $locales = [
            $wp_locale, // e.g. 'en_US'
            \substr($wp_locale, 0, 2), // e.g. 'en'
            'en',
        ];

        foreach ($locales as $locale) {
            $file = \sprintf('%s/data/%s/country.php', $this->path, $locale);
            if (\is_file($file)) {
                break;
            }
        }

        $countries = require $file;

        return \apply_filters('acf/country/countries', $countries);
    }

    public function country_flag_emoji(string $country_iso_alpha2): string
    {
        if (2 !== \strlen($country_iso_alpha2)) {
            return '';
        }

        $unicode_prefix = "\xF0\x9F\x87";
        $unicode_addition_for_upper_case = 0x65;
        $country_iso_alpha2 = \strtoupper($country_iso_alpha2);

        $emoji = $unicode_prefix.\chr(\ord($country_iso_alpha2[0]) + $unicode_addition_for_upper_case).$unicode_prefix.\chr(\ord($country_iso_alpha2[1]) + $unicode_addition_for_upper_case);

        return \strlen($emoji) ? $emoji : '';
    }

    /**
     * Get asset url.
     */
    protected function get_asset_url(string $asset): string
    {
        $manifest_path = $this->path.'/assets/dist/manifest.json';
        if (\is_file($manifest_path) && \is_readable($manifest_path)) {
            $manifest = \json_decode(\file_get_contents($manifest_path), true);
            $asset = $manifest[$asset] ?? $asset;
        }

        return $this->uri.'/assets/dist/'.$asset;
    }
}
