<?php

declare(strict_types=1);

namespace HelloNico\AcfCountry\Tests\Integration;

use HelloNico\AcfCountry\CountryField;
use HelloNico\AcfCountry\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class FieldValueTest extends TestCase
{
    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function singleValueFormats(): iterable
    {
        yield 'value' => [CountryField::FORMAT_VALUE, 'FR'];
        yield 'name' => [CountryField::FORMAT_NAME, 'France'];
        yield 'array' => [CountryField::FORMAT_ARRAY, ['value' => 'FR', 'label' => 'France']];
        yield 'emoji' => [CountryField::FORMAT_EMOJI, "\u{1F1EB}\u{1F1F7}"];
    }

    #[DataProvider('singleValueFormats')]
    public function testFormatsASingleValue(string $format, mixed $expected): void
    {
        $field = $this->registerField(['return_format' => $format]);
        $postId = static::factory()->post->create();

        \update_field($field['key'], 'FR', $postId);

        self::assertSame($expected, \get_field($field['key'], $postId));
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function multipleValueFormats(): iterable
    {
        yield 'value' => [CountryField::FORMAT_VALUE, ['FR', 'DE']];
        yield 'name' => [CountryField::FORMAT_NAME, ['France', 'Germany']];
        yield 'array' => [
            CountryField::FORMAT_ARRAY,
            [
                ['value' => 'FR', 'label' => 'France'],
                ['value' => 'DE', 'label' => 'Germany'],
            ],
        ];
        yield 'emoji' => [CountryField::FORMAT_EMOJI, ["\u{1F1EB}\u{1F1F7}", "\u{1F1E9}\u{1F1EA}"]];
    }

    #[DataProvider('multipleValueFormats')]
    public function testFormatsMultipleValues(string $format, mixed $expected): void
    {
        $field = $this->registerField(['return_format' => $format, 'multiple' => 1]);
        $postId = static::factory()->post->create();

        \update_field($field['key'], ['FR', 'DE'], $postId);

        self::assertSame($expected, \get_field($field['key'], $postId));
    }

    public function testFormatsNamesInTheSiteLanguage(): void
    {
        $this->useLocale('fr_FR');
        $field = $this->registerField(['return_format' => CountryField::FORMAT_NAME]);
        $postId = static::factory()->post->create();

        \update_field($field['key'], 'DE', $postId);

        self::assertSame('Allemagne', \get_field($field['key'], $postId));
    }

    public function testStoresTheCountryCode(): void
    {
        $field = $this->registerField();
        $postId = static::factory()->post->create();

        \update_field($field['key'], 'FR', $postId);

        self::assertSame('FR', \get_post_meta($postId, self::FIELD_NAME, true));
        self::assertSame('FR', \get_field($field['key'], $postId, false));
    }

    public function testStoresCountryCodesInUppercase(): void
    {
        $field = $this->registerField(['multiple' => 1]);
        $postId = static::factory()->post->create();

        \update_field($field['key'], ['fr', 'De'], $postId);

        self::assertSame(['FR', 'DE'], \get_post_meta($postId, self::FIELD_NAME, true));
    }

    public function testFormatsLowercaseStoredValues(): void
    {
        $field = $this->registerField(['return_format' => CountryField::FORMAT_NAME]);
        $postId = static::factory()->post->create();

        // Stored by an older version, an import or a direct meta write.
        \update_post_meta($postId, self::FIELD_NAME, 'fr');
        \update_post_meta($postId, '_' . self::FIELD_NAME, $field['key']);

        self::assertSame('France', \get_field($field['key'], $postId));
        self::assertSame('FR', \get_field($field['key'], $postId, false));
    }

    public function testEmptyValueStaysEmpty(): void
    {
        $field = $this->registerField(['return_format' => CountryField::FORMAT_EMOJI]);
        $postId = static::factory()->post->create();

        self::assertEmpty(\get_field($field['key'], $postId));
    }
}
