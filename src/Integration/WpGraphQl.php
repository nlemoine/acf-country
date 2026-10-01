<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Integration;

use n5s\AcfCountry\Countries;
use n5s\AcfCountry\Flag;
use WPGraphQL\Acf\FieldConfig;

/**
 * WPGraphQL for ACF 2+: country fields resolve to AcfCountry objects.
 */
final readonly class WpGraphQl implements IntegrationInterface
{
    public const TYPE = 'AcfCountry';

    public function __construct(private Countries $countries)
    {
    }

    public function isSupported(): bool
    {
        return \function_exists('register_graphql_acf_field_type');
    }

    public function registerHooks(): void
    {
        \add_action('graphql_register_types', [$this, 'registerObjectType']);
        \add_action('wpgraphql/acf/registry_init', [$this, 'registerFieldType']);
    }

    public function registerObjectType(): void
    {
        \register_graphql_object_type(self::TYPE, [
            'description' => \__('A country selected in an ACF Country field.', 'acf-country'),
            'fields' => [
                'code' => [
                    'type' => ['non_null' => 'String'],
                    'description' => \__('ISO 3166-1 alpha-2 code.', 'acf-country'),
                ],
                'name' => [
                    'type' => 'String',
                    'description' => \__('Country name in the site language, null for an unknown code.', 'acf-country'),
                ],
                'emoji' => [
                    'type' => 'String',
                    'description' => \__('Emoji flag, empty for an unknown code.', 'acf-country'),
                ],
            ],
        ]);
    }

    public function registerFieldType(): void
    {
        \register_graphql_acf_field_type('country', [
            'graphql_type' => static fn (mixed $fieldConfig): mixed => self::isMultiple($fieldConfig)
                ? ['list_of' => self::TYPE]
                : self::TYPE,
            'resolve' => fn (mixed $root, mixed $args, mixed $context, mixed $info, mixed $fieldType, mixed $fieldConfig): mixed => $this->resolve(
                $fieldConfig instanceof FieldConfig
                    ? $fieldConfig->resolve_field($root, (array) $args, $context, $info)
                    : null,
                self::isMultiple($fieldConfig)
            ),
        ]);
    }

    /**
     * @return array{code: string, name: ?string, emoji: string}|list<array{code: string, name: ?string, emoji: string}>|null
     */
    public function resolve(mixed $value, bool $multiple): ?array
    {
        $countries = [];
        foreach ((array) $value as $code) {
            if (\is_string($code) && $code !== '') {
                $code = \strtoupper($code);
                $name = $this->countries->name($code, \get_locale());
                $countries[] = ['code' => $code, 'name' => $name, 'emoji' => $name === null ? '' : Flag::fromCode($code)];
            }
        }

        return $multiple ? $countries : ($countries[0] ?? null);
    }

    private static function isMultiple(mixed $fieldConfig): bool
    {
        $acfField = $fieldConfig instanceof FieldConfig ? $fieldConfig->get_acf_field() : [];

        return !empty($acfField['multiple']);
    }
}
