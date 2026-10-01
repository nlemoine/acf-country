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

    private bool $debug;

    private bool $missingDataReported = false;

    public function __construct(private string $dataDir, ?bool $debug = null)
    {
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
     * @return array<string, string>
     */
    private function load(string $locale): array
    {
        foreach ($this->candidates($locale) as $candidate) {
            if (isset($this->lists[$candidate])) {
                return $this->lists[$candidate];
            }

            $file = "{$this->dataDir}/{$candidate}/country.php";
            if (\is_file($file)) {
                /** @var array<string, string> $list */
                $list = require $file;

                return $this->lists[$candidate] = $list;
            }
        }

        return $this->missingData();
    }

    /**
     * The locale, then less specific ones: pt_PT_ao90, pt_PT, pt, then en.
     *
     * @return list<string>
     */
    private function candidates(string $locale): array
    {
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

        if (!$this->missingDataReported) {
            $this->missingDataReported = true;
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.Security.EscapeOutput.ExceptionNotEscaped
            \error_log($message);
        }

        return [];
    }
}
