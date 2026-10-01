<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Unit;

use n5s\AcfCountry\Flag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FlagTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function codes(): iterable
    {
        yield 'uppercase' => ['FR', "\u{1F1EB}\u{1F1F7}"];
        yield 'lowercase' => ['fr', "\u{1F1EB}\u{1F1F7}"];
        yield 'mixed case' => ['jP', "\u{1F1EF}\u{1F1F5}"];
        yield 'empty' => ['', ''];
        yield 'three letters' => ['FRA', ''];
        yield 'digits' => ['12', ''];
        yield 'non-ASCII letter' => ['é', ''];
        yield 'spaces' => [' FR', ''];
    }

    #[DataProvider('codes')]
    public function testFromCode(string $code, string $expected): void
    {
        $this->assertSame($expected, Flag::fromCode($code));
    }
}
