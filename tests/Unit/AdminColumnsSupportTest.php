<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Unit;

use n5s\AcfCountry\Countries;
use n5s\AcfCountry\Integration\AdminColumns;
use PHPUnit\Framework\TestCase;

final class AdminColumnsSupportTest extends TestCase
{
    public function testSupportedWhenAdminColumnsIsLoaded(): void
    {
        // The test bootstrap loads Admin Columns' classes but not its plugin file.
        $this->assertSame(\defined('AC_FILE'), (new AdminColumns(new Countries(__DIR__)))->isSupported());
    }
}
