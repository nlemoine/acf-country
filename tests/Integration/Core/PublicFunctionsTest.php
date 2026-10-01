<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration\Core;

use n5s\AcfCountry\Plugin;
use n5s\AcfCountry\Tests\TestCase;

use function n5s\AcfCountry\get_countries;
use function n5s\AcfCountry\get_country_flag;
use function n5s\AcfCountry\get_country_name;

final class PublicFunctionsTest extends TestCase
{
    public function testGetCountriesUsesTheSiteLanguageByDefault(): void
    {
        $countries = get_countries();

        $this->assertSame('France', $countries['FR']);
        $this->assertCount(249, $countries);
    }

    public function testGetCountriesInAnotherLanguage(): void
    {
        $this->assertSame('Allemagne', get_countries('fr_FR')['DE']);
    }

    public function testGetCountryName(): void
    {
        $this->assertSame('Germany', get_country_name('de'));
        $this->assertSame('Deutschland', get_country_name('DE', 'de_DE_formal'));
        $this->assertNull(get_country_name('ZZ'));
    }

    public function testGetCountryFlag(): void
    {
        $this->assertSame("\u{1F1EB}\u{1F1F7}", get_country_flag('fr'));
        $this->assertSame('', get_country_flag('FRA'));
    }

    public function testFunctionsWorkWithoutInit(): void
    {
        // Plugin is a process-wide singleton that may already be booted, so reflection resets it to get a fresh, never-initialized instance.
        $property = new \ReflectionProperty(Plugin::class, 'instance');
        $saved = $property->getValue();
        $property->setValue(null, null);

        try {
            $this->assertSame('France', get_country_name('FR', 'en_US'));
            $this->assertSame('Allemagne', get_countries('fr_FR')['DE']);

            $this->assertFalse(\has_action('acf/include_field_types', [Plugin::getInstance(), 'registerFieldType']));
        } finally {
            $property->setValue(null, $saved);
        }
    }
}
