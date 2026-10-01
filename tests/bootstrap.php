<?php

declare(strict_types=1);

use function Mantle\Testing\manager;

require_once __DIR__ . '/../vendor/autoload.php';
$rootDir = realpath(__DIR__ . '/..');

// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
putenv("WP_CORE_DIR=$rootDir/tmp/wordpress");
putenv("CACHEDIR=$rootDir/tmp/test-cache");

manager()
    ->with_sqlite()
    ->loaded(static function (): void {
        require __DIR__ . '/../wp-content/plugins/advanced-custom-fields/acf.php';
        require __DIR__ . '/../wp-content/plugins/wp-graphql/wp-graphql.php';
        require __DIR__ . '/../wp-content/plugins/wpgraphql-acf/wpgraphql-acf.php';
        // Admin Columns only boots in wp-admin; its classes are enough to test the render filter.
        require __DIR__ . '/../wp-content/plugins/codepress-admin-columns/vendor/autoload.php';
        require __DIR__ . '/../acf-country.php';
    })
    ->install();
