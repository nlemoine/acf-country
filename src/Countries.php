<?php

declare(strict_types=1);

namespace n5s\AcfCountry;

use LogicException;

/**
 * Country names by locale, from the data/<locale>/country.php files.
 */
final class Countries
{
    /**
     * Raw lists, by data locale.
     *
     * @var array<string, array<string, string>>
     */
    private array $lists = [];

    /**
     * The data locale of each requested locale, null when no data file exists, so missing files are probed once.
     *
     * @var array<string, string|null>
     */
    private array $dataLocales = [];

    private readonly bool $debug;

    private bool $missingDataReported = false;

    public function __construct(
        private readonly string $dataDir,
        ?bool $debug = null,
    ) {

        $this->debug = $debug ?? (\defined('WP_DEBUG') && \WP_DEBUG);
    }

    /**
     * Country names indexed by code, sorted by name, for a WordPress locale.
     *
     * @return array<string, string>
     */
    public function all(string $locale): array
    {
        /** @var array<string, string> */
        return \apply_filters('acf/country/countries', $this->load($locale), $locale);
    }

    public function name(string $code, string $locale): ?string
    {
        return $this->all($locale)[\strtoupper($code)] ?? null;
    }

    /**
     * The emoji flag of a country code, an empty string for a code that is not in the country list.
     */
    public function flag(string $code, string $locale): string
    {
        return $this->name($code, $locale) === null ? '' : Flag::fromCode($code);
    }

    /**
     * @return array<string, string>
     */
    private function load(string $locale): array
    {
        if (!\array_key_exists($locale, $this->dataLocales)) {
            $this->dataLocales[$locale] = $this->resolve($locale);
        }

        $dataLocale = $this->dataLocales[$locale];
        if ($dataLocale === null) {
            return $this->missingData();
        }

        if (!isset($this->lists[$dataLocale])) {
            /** @var array<string, string> $list */
            $list = require "{$this->dataDir}/{$dataLocale}/country.php";
            $this->lists[$dataLocale] = $list;
        }

        return $this->lists[$dataLocale];
    }

    /**
     * The first candidate locale with a data file.
     */
    private function resolve(string $locale): ?string
    {
        foreach ($this->candidates($locale) as $candidate) {
            if (isset($this->lists[$candidate]) || \is_file("{$this->dataDir}/{$candidate}/country.php")) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * The locale, then less specific ones: pt_PT_ao90, pt_PT, pt, then en.
     *
     * @return list<string>
     */
    private function candidates(string $locale): array
    {
        // The locale ends up in a require path: anything that is not a WordPress locale shape gets English.
        if (\preg_match('/^[A-Za-z]{2,3}(?:_[A-Za-z0-9]+)*\z/', $locale) !== 1) {
            return ['en'];
        }

        $candidates = [];
        for ($parts = \explode('_', $locale); $parts !== []; \array_pop($parts)) {
            $candidates[] = \implode('_', $parts);
        }

        return \array_values(\array_unique([...$candidates, 'en']));
    }

    /**
     * @return array<string, string>
     */
    private function missingData(): array
    {
        $message = "ACF Country: no country data found in {$this->dataDir}, the plugin package is incomplete.";
        if ($this->debug) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            throw new LogicException($message);
        }

        // trigger_error() rather than error_log(), so error handlers see it; once per request.
        if (!$this->missingDataReported) {
            $this->missingDataReported = true;
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error, WordPress.Security.EscapeOutput.OutputNotEscaped -- Intended warning; the message only holds the plugin data path.
            \trigger_error($message, \E_USER_WARNING);
        }

        return [];
    }
}
