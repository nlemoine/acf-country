<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Integration;

use n5s\AcfCountry\Countries;
use n5s\AcfCountry\ReturnFormat;

/**
 * WPGraphQL for ACF 0.x (wp-graphql/wp-graphql-acf): country fields with the value, array and name formats.
 */
final class WpGraphQl implements IntegrationInterface
{
    public function __construct(private readonly Countries $countries)
    {
    }

    public function isSupported(): bool
    {
        return \defined('WPGraphQL\ACF\WPGRAPHQL_ACF_VERSION');
    }

    public function registerHooks(): void
    {
        \add_filter('wpgraphql_acf_supported_fields', $this->addSupportedField(...));
        \add_filter('wpgraphql_acf_register_graphql_field', $this->registerField(...), 10, 4);
    }

    public function addSupportedField(mixed $types): mixed
    {
        if (\is_array($types)) {
            $types[] = 'country';
        }

        return $types;
    }

    public function registerField(mixed $fieldConfig, mixed $typeName, mixed $fieldName, mixed $config): mixed
    {
        $acfField = \is_array($config) ? ($config['acf_field'] ?? null) : null;
        if (
            !\is_array($fieldConfig)
            || !\is_array($acfField)
            || ($acfField['type'] ?? null) !== 'country'
            || !\is_callable($fieldConfig['resolve'] ?? null)
        ) {
            return $fieldConfig;
        }

        $format = ReturnFormat::fromField($acfField);
        if ($format === ReturnFormat::Emoji) {
            return $fieldConfig;
        }

        $multiple = !empty($acfField['multiple']);
        $resolve = $fieldConfig['resolve'];
        $countries = $this->countries;

        $formatOne = static fn (mixed $code): mixed => $format->format((string) $code, $countries, \get_locale());
        $formatValue = static function (mixed $value) use ($formatOne): mixed {
            if (\is_array($value)) {
                return \array_map(static fn (mixed $code): mixed => $formatOne($code), \array_values($value));
            }

            return $formatOne((string) $value);
        };

        return match ($format) {
            ReturnFormat::Array => [
                'type' => $multiple ? ['list_of' => ['list_of' => 'String']] : ['list_of' => 'String'],
                'resolve' => static function (mixed $root, mixed $args, mixed $context, mixed $info) use ($resolve, $formatValue): array {
                    $value = $resolve($root, $args, $context, $info);

                    return empty($value) ? [] : (array) $formatValue($value);
                },
            ],
            default => [
                'type' => $multiple ? ['list_of' => 'String'] : 'String',
                'resolve' => static function (mixed $root, mixed $args, mixed $context, mixed $info) use ($resolve, $formatValue): mixed {
                    $value = $resolve($root, $args, $context, $info);

                    return empty($value) ? null : $formatValue($value);
                },
            ],
        };
    }
}
