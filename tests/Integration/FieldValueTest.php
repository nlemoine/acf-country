<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration;

use n5s\AcfCountry\ReturnFormat;
use n5s\AcfCountry\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class FieldValueTest extends TestCase
{
    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function singleValueFormats(): iterable
    {
        yield 'value' => [ReturnFormat::Value->value, 'FR'];
        yield 'name' => [ReturnFormat::Name->value, 'France'];
        yield 'array' => [ReturnFormat::Array->value, ['value' => 'FR', 'label' => 'France']];
        yield 'emoji' => [ReturnFormat::Emoji->value, "\u{1F1EB}\u{1F1F7}"];
    }

    #[DataProvider('singleValueFormats')]
    public function testFormatsASingleValue(string $format, mixed $expected): void
    {
        $field = $this->registerField(['return_format' => $format]);
        $postId = self::factory()->post->create();

        \update_field($field['key'], 'FR', $postId);

        $this->assertSame($expected, \get_field($field['key'], $postId));
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function multipleValueFormats(): iterable
    {
        yield 'value' => [ReturnFormat::Value->value, ['FR', 'DE']];
        yield 'name' => [ReturnFormat::Name->value, ['France', 'Germany']];
        yield 'array' => [
            ReturnFormat::Array->value,
            [
                ['value' => 'FR', 'label' => 'France'],
                ['value' => 'DE', 'label' => 'Germany'],
            ],
        ];
        yield 'emoji' => [ReturnFormat::Emoji->value, ["\u{1F1EB}\u{1F1F7}", "\u{1F1E9}\u{1F1EA}"]];
    }

    public function testFormatsACountryAddedByTheFilter(): void
    {
        \add_filter('acf/country/countries', static fn (array $countries): array => $countries + ['ZZ' => 'Test land']);
        $field = $this->registerField(['return_format' => ReturnFormat::Name->value]);
        $postId = self::factory()->post->create();

        \update_field($field['key'], 'ZZ', $postId);

        $this->assertSame('Test land', \get_field($field['key'], $postId));
    }

    #[DataProvider('multipleValueFormats')]
    public function testFormatsMultipleValues(string $format, mixed $expected): void
    {
        $field = $this->registerField(['return_format' => $format, 'multiple' => 1]);
        $postId = self::factory()->post->create();

        \update_field($field['key'], ['FR', 'DE'], $postId);

        $this->assertSame($expected, \get_field($field['key'], $postId));
    }

    public function testFormatsNamesInTheSiteLanguage(): void
    {
        $this->useLocale('fr_FR');
        $field = $this->registerField(['return_format' => ReturnFormat::Name->value]);
        $postId = self::factory()->post->create();

        \update_field($field['key'], 'DE', $postId);

        $this->assertSame('Allemagne', \get_field($field['key'], $postId));
    }

    public function testStoresTheCountryCode(): void
    {
        $field = $this->registerField();
        $postId = self::factory()->post->create();

        \update_field($field['key'], 'FR', $postId);

        $this->assertSame('FR', \get_post_meta($postId, self::FIELD_NAME, true));
        $this->assertSame('FR', \get_field($field['key'], $postId, false));
    }

    public function testStoresCountryCodesInUppercase(): void
    {
        $field = $this->registerField(['multiple' => 1]);
        $postId = self::factory()->post->create();

        \update_field($field['key'], ['fr', 'De'], $postId);

        $this->assertSame(['FR', 'DE'], \get_post_meta($postId, self::FIELD_NAME, true));
    }

    public function testFormatsLowercaseStoredValues(): void
    {
        $field = $this->registerField(['return_format' => ReturnFormat::Name->value]);
        $postId = self::factory()->post->create();

        // Stored by an older version, an import or a direct meta write.
        \update_post_meta($postId, self::FIELD_NAME, 'fr');
        \update_post_meta($postId, '_' . self::FIELD_NAME, $field['key']);

        $this->assertSame('France', \get_field($field['key'], $postId));
        $this->assertSame('FR', \get_field($field['key'], $postId, false));
    }

    public function testEmptyValueStaysEmpty(): void
    {
        $field = $this->registerField(['return_format' => ReturnFormat::Emoji->value]);
        $postId = self::factory()->post->create();

        $this->assertEmpty(\get_field($field['key'], $postId));
    }

    public function testSkipsInvalidItemsInStoredArrays(): void
    {
        $field = $this->registerField(['return_format' => ReturnFormat::Name->value, 'multiple' => 1]);
        $postId = self::factory()->post->create();
        \update_post_meta($postId, self::FIELD_NAME, ['FR', null, '', ['DE']]);
        \update_post_meta($postId, '_' . self::FIELD_NAME, $field['key']);

        $this->assertSame(['France'], \get_field($field['key'], $postId));
    }
}
