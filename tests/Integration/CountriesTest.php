<?php

declare(strict_types=1);

namespace HelloNico\AcfCountry\Tests\Integration;

use HelloNico\AcfCountry\Tests\TestCase;

final class CountriesTest extends TestCase
{
    public function testDefaultsToEnglish(): void
    {
        $countries = $this->fieldType()->get_countries();

        $this->assertSame('France', $countries['FR']);
        $this->assertSame('South Africa', $countries['ZA']);
        $this->assertCount(249, $countries);
    }

    public function testUsesTheFullWordPressLocale(): void
    {
        $this->useLocale('pt_BR');

        $this->assertSame('Alemanha', $this->fieldType()->get_countries()['DE']);
    }

    public function testFallsBackToTheLanguageCode(): void
    {
        // No data/de_DE_formal, so "de" is used.
        $this->useLocale('de_DE_formal');

        $this->assertSame('Deutschland', $this->fieldType()->get_countries()['DE']);
    }

    public function testFallsBackToTheRegionalLocaleOfAVariant(): void
    {
        // No data/pt_PT_ao90: pt_PT is used, not Brazilian Portuguese (pt).
        $this->useLocale('pt_PT_ao90');

        $this->assertSame('Arménia', $this->fieldType()->get_countries()['AM']);
    }

    public function testThreeLetterLanguagesDoNotFallBackToAnotherLanguage(): void
    {
        // No data/ast (Asturian): "as" is Assamese, so English must be used.
        $this->useLocale('ast');

        $this->assertSame('France', $this->fieldType()->get_countries()['FR']);
    }

    public function testFallsBackToEnglishForUnknownLocales(): void
    {
        $this->useLocale('xx_XX');

        $this->assertSame('Germany', $this->fieldType()->get_countries()['DE']);
    }

    public function testCountriesCanBeFiltered(): void
    {
        \add_filter('acf/country/countries', static fn (array $countries): array => \array_intersect_key(
            $countries,
            \array_flip(['FR', 'DE'])
        ));

        $this->assertSame(['FR' => 'France', 'DE' => 'Germany'], $this->fieldType()->get_countries());
    }

    public function testEveryLocaleFileListsTheSameCountries(): void
    {
        $expected = \array_keys(require \dirname(__DIR__, 2) . '/data/en/country.php');
        \sort($expected);

        foreach (\glob(\dirname(__DIR__, 2) . '/data/*/country.php') ?: [] as $file) {
            $codes = \array_keys(require $file);
            \sort($codes);
            $this->assertSame($expected, $codes, $file);
        }
    }
}
