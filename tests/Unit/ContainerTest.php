<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Unit;

use InvalidArgumentException;
use n5s\AcfCountry\Container;
use n5s\AcfCountry\Countries;
use PHPUnit\Framework\TestCase;

final class ContainerTest extends TestCase
{
    public function testBuildsServicesOnce(): void
    {
        $container = new Container(\dirname(__DIR__, 2));

        $this->assertInstanceOf(Countries::class, $container->get(Countries::class));
        $this->assertSame($container->get(Countries::class), $container->get(Countries::class));
    }

    public function testRejectsUnknownServices(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Container(\dirname(__DIR__, 2)))->get(\stdClass::class);
    }
}
