<?php

declare(strict_types=1);

use function Mantle\Testing\manager;

require_once __DIR__ . '/../vendor/autoload.php';
$rootDir = realpath(__DIR__ . '/..');

// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
putenv("WP_CORE_DIR=$rootDir/tmp/wordpress");
putenv("CACHEDIR=$rootDir/tmp/test-cache");

// Each integration runs in its own process, so the plugins it loads cannot affect the other tests.
$integration = (string) getenv('ACF_COUNTRY_TEST_INTEGRATION');
if (!in_array($integration, ['', 'wpgraphql', 'admin-columns'], true)) {
    // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- CLI message.
    throw new InvalidArgumentException("Unknown ACF_COUNTRY_TEST_INTEGRATION value: {$integration}.");
}

manager()
    ->with_sqlite()
    ->loaded(static function () use ($integration): void {
        $plugins = __DIR__ . '/../wp-content/plugins';
        require "{$plugins}/advanced-custom-fields/acf.php";

        if ($integration === 'wpgraphql') {
            require "{$plugins}/wp-graphql/wp-graphql.php";
            require "{$plugins}/wpgraphql-acf/wpgraphql-acf.php";
        }

        if ($integration === 'admin-columns') {
            // Admin Columns only boots in wp-admin; its classes are enough to test the render filter.
            require "{$plugins}/codepress-admin-columns/vendor/autoload.php";
        }

        require __DIR__ . '/../acf-country.php';
    })
    ->install();
