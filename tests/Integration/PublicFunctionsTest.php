<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration;

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
        // Public functions only need the container, not the hooks registered by init().
        $this->assertSame(
            'France',
            Plugin::getInstance()->countries()->name('FR', 'en_US')
        );
    }
}
