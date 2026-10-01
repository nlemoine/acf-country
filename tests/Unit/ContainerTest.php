<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Unit;

use InvalidArgumentException;
use n5s\AcfCountry\Container;
use n5s\AcfCountry\Countries;
use n5s\AcfCountry\Field\CountryField;
use n5s\AcfCountry\Integration\AdminColumns;
use n5s\AcfCountry\Integration\WpGraphQl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContainerTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function services(): iterable
    {
        yield 'countries' => [Countries::class];
        yield 'field' => [CountryField::class];
        yield 'Admin Columns' => [AdminColumns::class];
        yield 'WPGraphQL' => [WpGraphQl::class];
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('services')]
    public function testBuildsServicesOnce(string $class): void
    {
        // The field constructor adds ACF hooks and replaces the registered field type: keep both out of the other tests.
        $hooks = \array_map(static fn (\WP_Hook $hook): \WP_Hook => clone $hook, $GLOBALS['wp_filter']);
        $fieldType = \acf_get_field_type('country');

        try {
            $container = new Container(\dirname(__DIR__, 2));

            $this->assertInstanceOf($class, $container->get($class));
            $this->assertSame($container->get($class), $container->get($class));
        } finally {
            // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restores the hooks saved above.
            $GLOBALS['wp_filter'] = $hooks;
            \acf_register_field_type($fieldType);
        }
    }

    public function testRejectsUnknownServices(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Container(\dirname(__DIR__, 2)))->get(\stdClass::class);
    }
}
