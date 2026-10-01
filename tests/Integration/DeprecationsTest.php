<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration;

use Mantle\Testing\Attributes\Expected_Deprecation;
use n5s\AcfCountry\Field\CountryField;
use n5s\AcfCountry\Tests\TestCase;

final class DeprecationsTest extends TestCase
{
    /**
     * instanceof and parameter types never run autoloaders: the alias must exist as soon as the field class is loaded.
     *
     * No other test names the 3.x class, so the first assertion cannot pass because the autoloader already ran.
     */
    public function testLegacyClassNameIsAnAlias(): void
    {
        $fieldType = $this->fieldType();

        $this->assertTrue(\class_exists('HelloNico\AcfCountry\CountryField', false));
        $this->assertTrue($fieldType instanceof \HelloNico\AcfCountry\CountryField);

        $typed = static fn (\HelloNico\AcfCountry\CountryField $field): string => $field->name;
        $this->assertSame('country', $typed($fieldType));

        $this->assertSame(CountryField::class, (new \ReflectionClass('HelloNico\AcfCountry\CountryField'))->getName());
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
        $this->assertSame('value', CountryField::FORMAT_VALUE);
        $this->assertSame('array', CountryField::FORMAT_ARRAY);
        $this->assertSame('name', CountryField::FORMAT_NAME);
        $this->assertSame('emoji', CountryField::FORMAT_EMOJI);
        $this->assertSame(['value', 'array', 'name', 'emoji'], CountryField::FORMAT_FORMATS);
    }
}
