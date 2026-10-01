<?php

declare(strict_types=1);

namespace n5s\AcfCountry;

use n5s\AcfCountry\Field\CountryField;

/**
 * Country names indexed by ISO 3166-1 alpha-2 code, sorted by name.
 *
 * @param string|null $locale WordPress locale, the site locale by default.
 *
 * @return array<string, string>
 */
function get_countries(?string $locale = null): array
{
    return Plugin::getInstance()->countries()->all($locale ?? \get_locale());
}

/**
 * The name of a country, null for an unknown code.
 *
 * @param string|null $locale WordPress locale, the site locale by default.
 */
function get_country_name(string $code, ?string $locale = null): ?string
{
    return Plugin::getInstance()->countries()->name($code, $locale ?? \get_locale());
}

/**
 * The emoji flag of a country code, an empty string when the code is not two letters.
 */
function get_country_flag(string $code): string
{
    return Flag::fromCode($code);
}

// Deprecated in 4.0.0, removed in 5.0.0: the 3.x class name, for new and static calls before the field class is loaded.
// Loading the field class declares the alias; it extends acf_field, so it cannot load without ACF.
\spl_autoload_register(static function (string $class): void {
    if ($class === 'HelloNico\AcfCountry\CountryField' && \class_exists('acf_field')) {
        \class_exists(CountryField::class);
    }
});
