<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StrictArrayParamDimFetchRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/bin',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withRootFiles()
    ->withCache(__DIR__ . '/tmp/rector')
    // PHP version comes from the "php" constraint in composer.json, so the plugin stays PHP 7.4 compatible.
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        instanceOf: true,
        earlyReturn: true,
        phpunitCodeQuality: true,
        phpunitNarrowAsserts: true,
    )
    // PHPUnit migration rules, bound to the PHPUnit version in composer.lock.
    ->withComposerBased(phpunit: true)
    // WordPress and ACF signatures, so type inference does not go blind on their APIs.
    ->withPhpstanConfigs([__DIR__ . '/phpstan.neon.dist'])
    ->withImportNames(importShortClasses: false, removeUnusedImports: true)
    ->withSkip([
        // Hook callbacks receive third-party data: a native array type would turn bad input into a fatal error.
        // acf_field methods stay untyped: if ACF declares them untyped in acf_field, a narrower type would be fatal.
        // CountryField can be extended: keep its 3.0 method signatures until 4.0.
        AddVoidReturnTypeWhereNoReturnRector::class => [__DIR__ . '/src/CountryField.php'],
        ReturnTypeFromStrictTypedCallRector::class => [__DIR__ . '/src/CountryField.php'],
        StrictArrayParamDimFetchRector::class => [
            __DIR__ . '/acf-country.php',
            __DIR__ . '/src/CountryField.php',
        ],
    ]);
