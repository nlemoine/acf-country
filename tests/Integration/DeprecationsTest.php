<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration;

use Mantle\Testing\Attributes\Expected_Deprecation;
use n5s\AcfCountry\Field\CountryField;
use n5s\AcfCountry\Tests\TestCase;

final class DeprecationsTest extends TestCase
{
    public function testLegacyClassNameIsAnAlias(): void
    {
        $this->assertInstanceOf(\HelloNico\AcfCountry\CountryField::class, $this->fieldType());
        $this->assertSame(CountryField::class, (new \ReflectionClass(\HelloNico\AcfCountry\CountryField::class))->getName());
    }

    #[Expected_Deprecation('n5s\AcfCountry\Field\CountryField::get_countries')]
    public function testGetCountriesMethod(): void
    {
        $this->assertSame('France', $this->fieldType()->get_countries()['FR']);
    }

    #[Expected_Deprecation('n5s\AcfCountry\Field\CountryField::get_countries_for_locale')]
    public function testGetCountriesForLocaleMethod(): void
    {
        $this->assertSame('Allemagne', $this->fieldType()->get_countries_for_locale('fr_FR')['DE']);
    }

    #[Expected_Deprecation('n5s\AcfCountry\Field\CountryField::country_flag_emoji')]
    public function testCountryFlagEmojiMethod(): void
    {
        $this->assertSame("\u{1F1EB}\u{1F1F7}", $this->fieldType()->country_flag_emoji('FR'));
    }

    public function testFormatConstantsKeepTheirValues(): void
    {
        $this->assertSame(CountryField::FORMAT_FORMATS, ['value', 'array', 'name', 'emoji']);
    }
}
