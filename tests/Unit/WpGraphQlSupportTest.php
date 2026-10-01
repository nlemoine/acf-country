<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Unit;

use n5s\AcfCountry\Countries;
use n5s\AcfCountry\Integration\WpGraphQl;
use PHPUnit\Framework\TestCase;

final class WpGraphQlSupportTest extends TestCase
{
    public function testNotSupportedWithoutWpGraphQlForAcf(): void
    {
        $this->assertFalse((new WpGraphQl(new Countries(__DIR__)))->isSupported());
    }
}
