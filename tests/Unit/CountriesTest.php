<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Unit;

use LogicException;
use n5s\AcfCountry\Countries;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CountriesTest extends TestCase
{
    private const DATA = __DIR__ . '/../fixtures/data';

    protected function tearDown(): void
    {
        \remove_all_filters('acf/country/countries');
        parent::tearDown();
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function locales(): iterable
    {
        yield 'exact locale' => ['pt_PT', 'Arménia'];
        yield 'variant falls back to region' => ['pt_PT_ao90', 'Arménia'];
        yield 'region falls back to language' => ['pt_BR', 'Armênia'];
        yield 'language only' => ['fr', 'Arménie'];
        yield 'unknown falls back to English' => ['xx_XX', 'Armenia'];
        yield 'three-letter language without data' => ['ast', 'Armenia'];
    }

    #[DataProvider('locales')]
    public function testResolvesLocales(string $locale, string $armenia): void
    {
        $this->assertSame($armenia, (new Countries(self::DATA))->all($locale)['AM']);
    }

    public function testNameIsCaseInsensitive(): void
    {
        $countries = new Countries(self::DATA);

        $this->assertSame('Allemagne', $countries->name('de', 'fr'));
        $this->assertNull($countries->name('ZZ', 'fr'));
    }

    public function testAppliesTheFilterWithTheRequestedLocale(): void
    {
        \add_filter('acf/country/countries', static function (array $countries, string $locale): array {
            $countries['ZZ'] = "Test land ({$locale})";

            return $countries;
        }, 10, 2);

        $this->assertSame('Test land (pt_PT_ao90)', (new Countries(self::DATA))->name('ZZ', 'pt_PT_ao90'));
    }

    public function testFilterRunsOnEveryCall(): void
    {
        $countries = new Countries(self::DATA);
        $countries->all('fr');

        \add_filter('acf/country/countries', static fn (array $list): array => \array_diff_key($list, ['FR' => true]));

        $this->assertNull($countries->name('FR', 'fr'));
    }

    public function testMissingDataThrowsInDebug(): void
    {
        $this->expectException(LogicException::class);

        (new Countries(__DIR__, true))->all('en');
    }

    public function testMissingDataReturnsAnEmptyListInProduction(): void
    {
        $previous = \ini_set('error_log', '/dev/null');

        try {
            $this->assertSame([], (new Countries(__DIR__, false))->all('fr'));
        } finally {
            \ini_set('error_log', (string) $previous);
        }
    }
}
