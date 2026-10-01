<?php

/**
 * Generates data/<locale>/country.php from symfony/intl (ICU/CLDR data).
 */

declare(strict_types=1);

use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Locales;

require __DIR__ . '/../vendor/autoload.php';

$dataDir = \dirname(__DIR__) . '/data';

// Start from scratch so locales dropped upstream are removed too.
foreach (\glob($dataDir . '/*/country.php') ?: [] as $file) {
    \unlink($file);
    \rmdir(\dirname($file));
}

$locales = Locales::getLocales();

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
