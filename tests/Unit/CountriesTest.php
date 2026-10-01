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

    /**
     * @return iterable<string, array{string}>
     */
    public static function malformedLocales(): iterable
    {
        yield 'parent directory' => ['../data/fr'];
        yield 'nested traversal' => ['fr/../../data/fr'];
        yield 'slash' => ['fr/x'];
        yield 'empty' => [''];
    }

    #[DataProvider('malformedLocales')]
    public function testMalformedLocalesFallBackToEnglish(string $locale): void
    {
        $this->assertSame('Armenia', (new Countries(self::DATA))->all($locale)['AM']);
    }

    public function testNameIsCaseInsensitive(): void
    {
        $countries = new Countries(self::DATA);

        $this->assertSame('Allemagne', $countries->name('de', 'fr'));
        $this->assertNull($countries->name('ZZ', 'fr'));
    }

    public function testFlagIsEmptyForAnUnknownCode(): void
    {
        $countries = new Countries(self::DATA);

        $this->assertSame("\u{1F1E9}\u{1F1EA}", $countries->flag('de', 'fr'));
        $this->assertSame('', $countries->flag('ZZ', 'fr'));
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

    public function testProbesLocaleFilesOnce(): void
    {
        // phpcs:disable WordPress.WP.AlternativeFunctions -- A throwaway data directory, outside WordPress.
        $dataDir = \sys_get_temp_dir() . '/acf-country-' . \uniqid();
        \mkdir("{$dataDir}/en", 0777, true);
        \file_put_contents("{$dataDir}/en/country.php", "<?php return ['AM' => 'Armenia'];");

        try {
            $countries = new Countries($dataDir);
            $this->assertSame('Armenia', $countries->name('AM', 'fr_FR'));

            // A file added after the first lookup is not probed again: the resolved data locale is cached.
            \mkdir("{$dataDir}/fr");
            \file_put_contents("{$dataDir}/fr/country.php", "<?php return ['AM' => 'Arménie'];");
            $this->assertSame('Armenia', $countries->name('AM', 'fr_FR'));
            $this->assertSame('Arménie', (new Countries($dataDir))->name('AM', 'fr_FR'));
        } finally {
            \array_map(\unlink(...), \glob("{$dataDir}/*/country.php") ?: []);
            \array_map(\rmdir(...), \glob("{$dataDir}/*") ?: []);
            \rmdir($dataDir);
        }
        // phpcs:enable
    }

    public function testMissingDataThrowsInDebug(): void
    {
        $this->expectException(LogicException::class);

        (new Countries(__DIR__, true))->all('en');
    }

    public function testMissingDataReturnsAnEmptyListInProduction(): void
    {
        $warnings = [];
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Captures the warning under test.
        \set_error_handler(
            static function (int $level, string $message) use (&$warnings): bool {
                $warnings[] = [$level, $message];
                return true;
            },
            \E_USER_WARNING
        );

        try {
            $countries = new Countries(__DIR__, false);
            $this->assertSame([], $countries->all('fr'));
            $this->assertSame([], $countries->all('de'));
        } finally {
            \restore_error_handler();
        }

        $this->assertCount(1, $warnings);
        $this->assertSame(\E_USER_WARNING, $warnings[0][0]);
        $this->assertStringContainsString('no country data found', $warnings[0][1]);
    }
}
