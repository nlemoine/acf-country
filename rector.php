<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertInstanceOfComparisonRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\FlipAssertRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ArrayParamTypeByMethodCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StrictArrayParamDimFetchRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/bin',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withRootFiles()
    ->withCache(__DIR__ . '/tmp/rector')
    // PHP version comes from the "php" constraint in composer.json, so the plugin stays PHP 8.2 compatible.
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
        // WordPress hook callbacks stay array callables: closures cannot be removed with remove_action()/remove_filter().
        ArrayToFirstClassCallableRector::class,
        // Hook callbacks receive third-party data: a native array type would turn bad input into a fatal error.
        // acf_field methods stay untyped: if ACF declares them untyped in acf_field, a narrower type would be fatal.
        ArrayParamTypeByMethodCallTypeRector::class => [__DIR__ . '/src/Field/CountryField.php'],
        StrictArrayParamDimFetchRector::class => [
            __DIR__ . '/acf-country.php',
            __DIR__ . '/src/Field/CountryField.php',
        ],
        // The 3.x alias test checks the native instanceof operator, which never autoloads, unlike assertInstanceOf().
        AssertInstanceOfComparisonRector::class => [__DIR__ . '/tests/Integration/Core/DeprecationsTest.php'],
        // It also takes a deprecated constant for the expected value and swaps the arguments.
        FlipAssertRector::class => [__DIR__ . '/tests/Integration/Core/DeprecationsTest.php'],
        // Data fixtures mirror data/ files.
        SafeDeclareStrictTypesRector::class => [__DIR__ . '/tests/fixtures'],
    ]);
