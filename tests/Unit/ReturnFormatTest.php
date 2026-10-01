<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Unit;

use n5s\AcfCountry\Countries;
use n5s\AcfCountry\ReturnFormat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReturnFormatTest extends TestCase
{
    /**
     * @return iterable<string, array{mixed, ReturnFormat}>
     */
    public static function fields(): iterable
    {
        yield 'array' => ['array', ReturnFormat::Array];
        yield 'value' => ['value', ReturnFormat::Value];
        yield 'name' => ['name', ReturnFormat::Name];
        yield 'emoji' => ['emoji', ReturnFormat::Emoji];
        yield 'select field "label"' => ['label', ReturnFormat::Name];
        yield 'unknown' => ['other', ReturnFormat::Array];
        yield 'not a string' => [1, ReturnFormat::Array];
    }

    #[DataProvider('fields')]
    public function testFromField(mixed $format, ReturnFormat $expected): void
    {
        $this->assertSame($expected, ReturnFormat::fromField(['return_format' => $format]));
    }

    public function testFromFieldWithoutFormat(): void
    {
        $this->assertSame(ReturnFormat::Array, ReturnFormat::fromField([]));
    }

    /**
     * @return iterable<string, array{ReturnFormat, string, mixed}>
     */
    public static function formats(): iterable
    {
        yield 'array, known' => [ReturnFormat::Array, 'fr', ['value' => 'FR', 'label' => 'France']];
        yield 'array, unknown' => [ReturnFormat::Array, 'XX', ['value' => 'XX', 'label' => 'XX']];
        yield 'value, known' => [ReturnFormat::Value, 'fr', 'FR'];
        yield 'value, unknown' => [ReturnFormat::Value, 'xx', 'XX'];
        yield 'name, known' => [ReturnFormat::Name, 'FR', 'France'];
        yield 'name, unknown' => [ReturnFormat::Name, 'XX', 'XX'];
        yield 'emoji, known' => [ReturnFormat::Emoji, 'FR', "\u{1F1EB}\u{1F1F7}"];
        yield 'emoji, unknown' => [ReturnFormat::Emoji, 'XX', ''];
    }

    #[DataProvider('formats')]
    public function testFormat(ReturnFormat $format, string $code, mixed $expected): void
    {
        $countries = new Countries(__DIR__ . '/../fixtures/data');

        $this->assertSame($expected, $format->format($code, $countries, 'en_US'));
    }
}
