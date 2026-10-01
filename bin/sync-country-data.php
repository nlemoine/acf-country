<?php

/**
 * Generates data/<locale>/country.php from symfony/intl (ICU/CLDR data).
 *
 * With --check, only verifies that data/ is up to date and exits with 1 if not.
 * Names are sorted at runtime by the ICU collator of ext-intl, whose ordering
 * varies between ICU versions, so the check ignores order.
 */

declare(strict_types=1);

use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Locales;

require __DIR__ . '/../vendor/autoload.php';

$dataDir = \dirname(__DIR__) . '/data';
$locales = Locales::getLocales();
$existing = \array_map(
    static fn (string $file): string => \basename(\dirname($file)),
    \glob($dataDir . '/*/country.php') ?: []
);

if (\in_array('--check', $argv, true)) {
    $errors = [];
    foreach (\array_diff($existing, $locales) as $locale) {
        $errors[] = "{$locale}: not provided by symfony/intl";
    }
    foreach ($locales as $locale) {
        $file = "{$dataDir}/{$locale}/country.php";
        if (!\is_file($file)) {
            $errors[] = "{$locale}: missing";
            continue;
        }
        $countries = require $file;
        $expected = Countries::getNames($locale);
        \ksort($countries);
        \ksort($expected);
        if ($countries !== $expected) {
            $errors[] = "{$locale}: out of date";
        }
    }

    if ($errors !== []) {
        \fwrite(\STDERR, \implode("\n", $errors) . "\n");
        exit(1);
    }

    \printf("Country lists for %d locales are up to date.\n", \count($locales));
    exit(0);
}

// Start from scratch so locales dropped upstream are removed too.
foreach ($existing as $locale) {
    \unlink("{$dataDir}/{$locale}/country.php");
    \rmdir("{$dataDir}/{$locale}");
}

foreach ($locales as $locale) {
    $dir = $dataDir . '/' . $locale;
    if (!\is_dir($dir)) {
        \mkdir($dir, 0755, true);
    }

    $countries = Countries::getNames($locale);
    \file_put_contents(
        $dir . '/country.php',
        '<?php return ' . \var_export($countries, true) . ";\n"
    );
}

\printf("Generated country lists for %d locales.\n", \count($locales));
