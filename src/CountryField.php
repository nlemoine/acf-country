<?php

declare(strict_types=1);

namespace HelloNico\AcfCountry;

use acf_field;
use acf_field_select;

/**
 * @phpstan-type Field array<string, mixed>
 */
class CountryField extends acf_field
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

    /**
     * @var acf_field_select
     */
    protected acf_field $select;

    /**
     * Isn't needed because we're extending acf_field, but it's here for clarity.
     *
     * @var bool
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint -- acf_field declares it untyped.
    public $show_in_rest = true;

    /**
     * Create a new field instance.
     */
    public function __construct(protected string $uri, protected string $path)
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function initialize()
    {
        $this->name = 'country';
        $this->label = \__('Country', 'acf-country');
        $this->category = 'choice';
        $this->defaults = [
            'multiple' => 0,
            'allow_null' => 0,
            'choices' => [],
            'default_value' => '',
            'layout' => 'vertical',
            'ui' => 0,
            'ajax' => 0,
            'placeholder' => '',
            'return_format' => self::FORMAT_ARRAY,
        ];
        $this->select = \acf_get_field_type('select');
    }

    /**
     * The rendered field type.
     *
     * @param Field $field
     *
     * @return void
     */
    public function render_field($field)
    {
        // Admin screens follow the language of the logged-in user.
        $countries = $this->get_countries_for_locale(\determine_locale());

        \array_walk($countries, function (&$name, string $code): void {
            $name = $this->country_flag_emoji($code) . '  ' . $name;
        });

        $field['choices'] = $countries;

        $field['ajax'] = 0;
        $field['value'] = $this->normalize_codes($field['value']);
        $this->select->render_field($field);
    }

    /**
     * The rendered field type settings.
     *
     * @param Field $field
     *
     * @return void
     */
    public function render_field_settings($field)
    {
        $field['choices'] = \acf_encode_choices($this->get_countries_for_locale(\determine_locale()));

        $field['default_value'] = \acf_encode_choices($field['default_value'], false);

        // choices
        \acf_render_field_setting($field, [
            'label' => \__('Choices', 'acf'),
            'name' => 'choices',
            'type' => 'textarea',
            'wrapper' => [
                'class' => 'hidden',
            ],
        ]);

        // default_value
        \acf_render_field_setting(
            $field,
            [
                'label' => \__('Default Value', 'acf'),
                'instructions' => \__('Enter each default value on a new line', 'acf'),
                'name' => 'default_value',
                'type' => 'textarea',
            ]
        );

        // return_format
        \acf_render_field_setting(
            $field,
            [
                'label' => \__('Return Format', 'acf'),
                'instructions' => \__('Specify the value returned', 'acf'),
                'type' => 'radio',
                'name' => 'return_format',
                'layout' => 'horizontal',
                'choices' => [
                    self::FORMAT_ARRAY => \__('Country code and name', 'acf-country'),
                    self::FORMAT_VALUE => \__('Country code', 'acf-country'),
                    self::FORMAT_NAME => \__('Country name', 'acf-country'),
                    self::FORMAT_EMOJI => \__('Country emoji flag', 'acf-country'),
                ],
            ]
        );

        \acf_render_field_setting(
            $field,
            [
                'label' => \__('Select multiple values?', 'acf'),
                'instructions' => '',
                'name' => 'multiple',
                'type' => 'true_false',
                'ui' => 1,
            ]
        );
    }

    /**
     * Validation settings.
     *
     * @param Field $field
     *
     * @return void
     */
    public function render_field_validation_settings($field)
    {
        // allow_null
        $this->select->render_field_validation_settings($field);
    }

    /**
     * Presentation settings.
     *
     * @param Field $field
     *
     * @return void
     */
    public function render_field_presentation_settings($field)
    {
        \acf_render_field_setting(
            $field,
            [
                'label' => \__('Stylized UI', 'acf'),
                'instructions' => \__('Use a stylized checkbox using select2', 'acf'),
                'name' => 'ui',
                'type' => 'true_false',
                'ui' => 1,
            ]
        );
    }

    /**
     * The formatted field value.
     *
     * @param mixed $value
     * @param int   $post_id
     * @param Field $field
     *
     * @return mixed
     */
    public function format_value($value, $post_id, $field)
    {
        $field['choices'] = $this->get_countries();

        // Map our formats to the ones the select field understands.
        $original_format = $field['return_format'];
        $field['return_format'] = [
            self::FORMAT_EMOJI => self::FORMAT_VALUE,
            self::FORMAT_NAME => 'label',
        ][$original_format] ?? $original_format;
        $value = $this->select->format_value($value, $post_id, $field);
        $field['return_format'] = $original_format;

        // Then convert to emoji
        if ($field['return_format'] === self::FORMAT_EMOJI && !empty($value)) {
            if (\is_array($value)) {
                $value = \array_map($this->country_flag_emoji(...), $value);
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
     * @param bool|string $valid
     * @param mixed       $value
     * @param Field       $field
     * @param string      $input
     *
     * @return bool|string
     */
    public function validate_value($valid, $value, $field, $input)
    {
        if (empty($value)) {
            return $valid;
        }

        $countries = $this->get_countries();
        $invalid = [];
        foreach ((array) $value as $code) {
            if (!\is_string($code)) {
                $invalid[] = \gettype($code);
                continue;
            }
            if (!isset($countries[\strtoupper($code)])) {
                $invalid[] = $code;
            }
        }

        if ($invalid === []) {
            return $valid;
        }

        return \sprintf(
            /* translators: placeholder indicates the invalid country codes */
            \_n('%s is not a valid country code', '%s are not valid country codes', \count($invalid), 'acf-country'),
            \esc_html(\implode(', ', $invalid))
        );
    }

    /**
     * The REST API schema, listing country codes as allowed values.
     *
     * @param Field $field
     *
     * @return array<string, mixed>
     */
    public function get_rest_schema(array $field)
    {
        $field['choices'] = $this->get_countries();
        $schema = $this->select->get_rest_schema($field);

        // Codes are strings; the select field also allows "int", which is not a JSON schema type.
        $schema['type'] = ['string', 'array', 'null'];
        $schema['items']['type'] = 'string';

        return $schema;
    }

    /**
     * Validates values sent through the REST API.
     *
     * @param bool|\WP_Error $valid
     * @param mixed $value
     * @param Field $field
     *
     * @return bool|\WP_Error
     */
    public function validate_rest_value($valid, $value, $field)
    {
        if ($valid instanceof \WP_Error) {
            return $valid;
        }

        $field['choices'] = $this->get_countries();

        return $this->select->validate_rest_value($valid, $this->normalize_codes($value), $field);
    }

    /**
     * The field value after loading from the database.
     *
     * @param mixed $value
     * @param int   $post_id
     * @param Field $field
     *
     * @return mixed
     */
    public function load_value($value, $post_id, $field)
    {
        return $this->normalize_codes($this->select->load_value($value, $post_id, $field));
    }

    /**
     * The field value before saving to the database.
     *
     * @param mixed $value
     * @param int   $post_id
     * @param Field $field
     *
     * @return mixed
     */
    public function update_value($value, $post_id, $field)
    {
        return $this->select->update_value($this->normalize_codes($value), $post_id, $field);
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
     * @param Field $field
     *
     * @return Field
     */
    public function load_field($field)
    {
        return $field;
    }

    /**
     * The field before saving to the database.
     *
     * @param Field $field
     *
     * @return Field
     */
    public function update_field($field)
    {
        return $this->select->update_field($field);
    }

    /**
     * The action fired when deleting a field from the database.
     *
     * @param Field $field
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
        $this->select->input_admin_enqueue_scripts();
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
        \wp_enqueue_style($this->name, $this->get_asset_url('field.css'), [], null);
    }

    public function country_flag_emoji(string $country_iso_alpha2): string
    {
        if (!\preg_match('/^[A-Za-z]{2}$/', $country_iso_alpha2)) {
            return '';
        }

        // A flag is the pair of regional indicator symbols (U+1F1E6 for "A") matching the letters.
        $offset = 0x1F1E6 - \ord('A');
        $country_iso_alpha2 = \strtoupper($country_iso_alpha2);

        return \html_entity_decode(
            \sprintf('&#%d;&#%d;', $offset + \ord($country_iso_alpha2[0]), $offset + \ord($country_iso_alpha2[1])),
            \ENT_QUOTES,
            'UTF-8'
        );
    }

    /**
     * Uppercase country codes, the format of data/ keys.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function normalize_codes($value)
    {
        if (\is_string($value)) {
            return \strtoupper($value);
        }

        if (!\is_array($value)) {
            return $value;
        }

        return \array_map(
            static fn ($code): mixed => \is_string($code) ? \strtoupper($code) : $code,
            $value
        );
    }

    /**
     * Get countries, in the site language.
     *
     * @return array<string, string> Names indexed by country code.
     */
    public function get_countries()
    {
        return $this->get_countries_for_locale(\get_locale());
    }

    /**
     * Get countries in a given language.
     *
     * @return array<string, string> Names indexed by country code.
     */
    public function get_countries_for_locale(string $locale): array
    {
        $wp_locale = $locale;

        // Try the locale, then less specific ones: pt_PT_ao90, pt_PT, pt, then en.
        $locales = [];
        for ($parts = \explode('_', $wp_locale); $parts !== []; \array_pop($parts)) {
            $locales[] = \implode('_', $parts);
        }
        $locales[] = 'en';

        foreach ($locales as $locale) {
            $file = \sprintf('%s/data/%s/country.php', $this->path, $locale);
            if (\is_file($file)) {
                break;
            }
        }

        $countries = require $file;

        return \apply_filters('acf/country/countries', $countries, $wp_locale);
    }

    /**
     * Get asset url.
     */
    protected function get_asset_url(string $asset): string
    {
        $manifest_path = $this->path . '/assets/dist/manifest.json';
        $json = \is_readable($manifest_path) ? \file_get_contents($manifest_path) : false;
        if ($json !== false) {
            $manifest = \json_decode($json, true);
            $asset = $manifest[$asset] ?? $asset;
        }

        return $this->uri . '/assets/dist/' . $asset;
    }
}
