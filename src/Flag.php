<?php

declare(strict_types=1);

namespace n5s\AcfCountry;

/**
 * Emoji flags: a flag is the pair of regional indicator symbols matching a country code.
 */
final class Flag
{
    private const REGIONAL_INDICATOR_A = 0x1F1E6;

    public static function fromCode(string $code): string
    {
        if (\preg_match('/^[A-Za-z]{2}$/', $code) !== 1) {
            return '';
        }

        $code = \strtoupper($code);
        $offset = self::REGIONAL_INDICATOR_A - \ord('A');

        return \html_entity_decode(
            \sprintf('&#%d;&#%d;', $offset + \ord($code[0]), $offset + \ord($code[1])),
            \ENT_QUOTES,
            'UTF-8'
        );
    }
}
