<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Field;

use acf_field;
use acf_field_select;
use n5s\AcfCountry\Countries;
use n5s\AcfCountry\Flag;
use n5s\AcfCountry\Plugin;
use n5s\AcfCountry\ReturnFormat;

use function n5s\AcfCountry\get_countries;
use function n5s\AcfCountry\get_country_flag;

/**
 * The ACF "country" field: a select field of countries, delegating to the ACF select field.
 *
 * ACF method parameters stay untyped: a future ACF version could declare them untyped in acf_field.
 *
 * @phpstan-type Field array<string, mixed>
 */
class CountryField extends acf_field
{
    /** @deprecated 4.0.0 Use ReturnFormat::Value->value. */
    public const FORMAT_VALUE = 'value';

    /** @deprecated 4.0.0 Use ReturnFormat::Array->value. */
    public const FORMAT_ARRAY = 'array';

    /** @deprecated 4.0.0 Use ReturnFormat::Name->value. */
    public const FORMAT_NAME = 'name';

    /** @deprecated 4.0.0 Use ReturnFormat::Emoji->value. */
    public const FORMAT_EMOJI = 'emoji';

    /** @deprecated 4.0.0 Use ReturnFormat::cases(). */
    public const FORMAT_FORMATS = ['value', 'array', 'name', 'emoji'];

    /**
     * @var bool
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint -- acf_field declares it untyped.
    public $show_in_rest = true;

    protected acf_field_select $select;

    private readonly Countries $countries;

    public function __construct(private readonly string $uri, private readonly string $path, ?Countries $countries = null)
    {
        $this->countries = $countries ?? Plugin::getInstance()->countries();
        parent::__construct();
    }

    public function initialize(): void
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
            'return_format' => ReturnFormat::Array->value,
        ];
        $this->select = \acf_get_field_type('select');
    }

    /**
     * @param Field $field
     */
    public function render_field($field): void
    {
        // Admin screens follow the language of the logged-in user.
        $choices = [];
        foreach ($this->countries->all(\determine_locale()) as $code => $name) {
            $choices[$code] = Flag::fromCode($code) . "\u{00A0}\u{00A0}" . $name;
        }

        $field['choices'] = $choices;
        $field['ajax'] = 0;
        $field['value'] = $this->normalizeCodes($field['value']);
        $this->select->render_field($field);
    }

    /**
     * @param Field $field
     */
    public function render_field_settings($field): void
    {
        $field['choices'] = \acf_encode_choices($this->countries->all(\determine_locale()));
        $field['default_value'] = \acf_encode_choices($field['default_value'], false);

        // Read by ACF's conditional logic UI ("selectEqualTo" conditions).
        \acf_render_field_setting($field, [
            'label' => \__('Choices', 'acf'),
            'name' => 'choices',
            'type' => 'textarea',
            'wrapper' => ['class' => 'hidden'],
        ]);

        \acf_render_field_setting($field, [
            'label' => \__('Default Value', 'acf'),
            'instructions' => \__('Enter each default value on a new line', 'acf'),
            'name' => 'default_value',
            'type' => 'textarea',
        ]);

        $formats = [];
        foreach ([ReturnFormat::Array, ReturnFormat::Value, ReturnFormat::Name, ReturnFormat::Emoji] as $format) {
            $formats[$format->value] = $format->label();
        }

        \acf_render_field_setting($field, [
            'label' => \__('Return Format', 'acf'),
            'instructions' => \__('Specify the value returned', 'acf'),
            'type' => 'radio',
            'name' => 'return_format',
            'layout' => 'horizontal',
            'choices' => $formats,
        ]);

        \acf_render_field_setting($field, [
            'label' => \__('Select multiple values?', 'acf'),
            'instructions' => '',
            'name' => 'multiple',
            'type' => 'true_false',
            'ui' => 1,
        ]);
    }

    /**
     * @param Field $field
     */
    public function render_field_validation_settings($field): void
    {
        // allow_null
        $this->select->render_field_validation_settings($field);
    }

    /**
     * @param Field $field
     */
    public function render_field_presentation_settings($field): void
    {
        \acf_render_field_setting($field, [
            'label' => \__('Stylized UI', 'acf'),
            'instructions' => \__('Use a stylized checkbox using select2', 'acf'),
            'name' => 'ui',
            'type' => 'true_false',
            'ui' => 1,
        ]);
    }

    /**
     * @param mixed     $value
     * @param int|string $post_id
     * @param Field     $field
     */
    public function format_value($value, $post_id, $field): mixed
    {
        if (\acf_is_empty($value)) {
            return $value;
        }

        $format = ReturnFormat::fromField($field);
        $locale = \get_locale();

        if (!\is_array($value)) {
            return \is_string($value) ? $format->format($value, $this->countries, $locale) : $value;
        }

        $formatted = [];
        foreach ($value as $key => $code) {
            if (\is_string($code) && $code !== '') {
                $formatted[$key] = $format->format($code, $this->countries, $locale);
            }
        }

        return \array_values($formatted);
    }

    /**
     * @param bool|string $valid
     * @param mixed       $value
     * @param Field       $field
     * @param string      $input
     */
    public function validate_value($valid, $value, $field, $input): bool|string
    {
        if (empty($value)) {
            return $valid;
        }

        $invalid = [];
        foreach ((array) $value as $code) {
            if (!\is_string($code)) {
                $invalid[] = \gettype($code);
                continue;
            }
            if ($this->countries->name($code, \get_locale()) === null) {
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
     * @param Field $field
     *
     * @return array<string, mixed>
     */
    public function get_rest_schema(array $field): array
    {
        $field['choices'] = $this->countries->all(\get_locale());
        $schema = $this->select->get_rest_schema($field);

        // Codes are strings; the select field also allows "int", which is not a JSON schema type.
        $schema['type'] = ['string', 'array', 'null'];
        $schema['items']['type'] = 'string';

        return $schema;
    }

    /**
     * @param bool|\WP_Error $valid
     * @param mixed          $value
     * @param Field          $field
     */
    public function validate_rest_value($valid, $value, $field): bool|\WP_Error
    {
        if ($valid instanceof \WP_Error) {
            return $valid;
        }

        $field['choices'] = $this->countries->all(\get_locale());

        return $this->select->validate_rest_value($valid, $this->normalizeCodes($value), $field);
    }

    /**
     * @param mixed      $value
     * @param int|string $post_id
     * @param Field      $field
     */
    public function load_value($value, $post_id, $field): mixed
    {
        return $this->normalizeCodes($this->select->load_value($value, $post_id, $field));
    }

    /**
     * @param mixed      $value
     * @param int|string $post_id
     * @param Field      $field
     */
    public function update_value($value, $post_id, $field): mixed
    {
        return $this->select->update_value($this->normalizeCodes($value), $post_id, $field);
    }

    /**
     * @param Field $field
     *
     * @return Field
     */
    public function update_field($field): array
    {
        return $this->select->update_field($field);
    }

    public function input_admin_enqueue_scripts(): void
    {
        $this->select->input_admin_enqueue_scripts();
        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- the file name is hashed by the build.
        \wp_enqueue_script($this->name, $this->assetUrl('field.js'), ['jquery'], null, true);
    }

    public function field_group_admin_enqueue_scripts(): void
    {
        $this->input_admin_enqueue_scripts();
        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- the file name is hashed by the build.
        \wp_enqueue_style($this->name, $this->assetUrl('field.css'), [], null);
    }

    /**
     * @deprecated 4.0.0 Use n5s\AcfCountry\get_countries().
     *
     * @return array<string, string>
     */
    public function get_countries(): array
    {
        \_deprecated_function(__METHOD__, '4.0.0', 'n5s\AcfCountry\get_countries()');

        return get_countries();
    }

    /**
     * @deprecated 4.0.0 Use n5s\AcfCountry\get_countries($locale).
     *
     * @return array<string, string>
     */
    public function get_countries_for_locale(string $locale): array
    {
        \_deprecated_function(__METHOD__, '4.0.0', 'n5s\AcfCountry\get_countries()');

        return get_countries($locale);
    }

    /**
     * @deprecated 4.0.0 Use n5s\AcfCountry\get_country_flag().
     */
    public function country_flag_emoji(string $country_iso_alpha2): string
    {
        \_deprecated_function(__METHOD__, '4.0.0', 'n5s\AcfCountry\get_country_flag()');

        return get_country_flag($country_iso_alpha2);
    }

    /**
     * Uppercase country codes, the format of data/ keys.
     */
    private function normalizeCodes(mixed $value): mixed
    {
        if (\is_string($value)) {
            return \strtoupper($value);
        }

        if (!\is_array($value)) {
            return $value;
        }

        return \array_map(static fn (mixed $code): mixed => \is_string($code) ? \strtoupper($code) : $code, $value);
    }

    private function assetUrl(string $asset): string
    {
        $manifestPath = $this->path . '/assets/dist/manifest.json';
        $json = \is_readable($manifestPath) ? \file_get_contents($manifestPath) : false;
        if ($json !== false) {
            $manifest = \json_decode($json, true);
            $asset = \is_array($manifest) && \is_string($manifest[$asset] ?? null) ? $manifest[$asset] : $asset;
        }

        return $this->uri . '/assets/dist/' . $asset;
    }
}

// Deprecated in 4.0.0, removed in 5.0.0: the 3.x class name. Declared with the class, as instanceof and type checks never autoload.
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
if (!\class_exists('HelloNico\AcfCountry\CountryField', false)) {
    \class_alias(CountryField::class, 'HelloNico\AcfCountry\CountryField');
}
// phpcs:enable
