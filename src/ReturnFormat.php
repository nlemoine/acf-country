<?php

declare(strict_types=1);

namespace n5s\AcfCountry;

/**
 * What get_field() returns for a country field.
 */
enum ReturnFormat: string
{
    case Array = 'array';
    case Value = 'value';
    case Name = 'name';
    case Emoji = 'emoji';

    /**
     * @param array<string, mixed> $field
     */
    public static function fromField(array $field): self
    {
        $format = $field['return_format'] ?? null;
        if ($format === 'label') {
            // The ACF select field name for "name".
            return self::Name;
        }

        return \is_string($format) ? (self::tryFrom($format) ?? self::Array) : self::Array;
    }

    /**
     * @return string|array{value: string, label: string}
     */
    public function format(string $code, Countries $countries, string $locale): string|array
    {
        $code = \strtoupper($code);
        $name = $countries->name($code, $locale);

        return match ($this) {
            self::Array => ['value' => $code, 'label' => $name ?? $code],
            self::Value => $code,
            self::Name => $name ?? $code,
            self::Emoji => $name === null ? '' : Flag::fromCode($code),
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Array => \__('Country code and name', 'acf-country'),
            self::Value => \__('Country code', 'acf-country'),
            self::Name => \__('Country name', 'acf-country'),
            self::Emoji => \__('Country emoji flag', 'acf-country'),
        };
    }
}
