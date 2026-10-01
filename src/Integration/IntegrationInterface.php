<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Integration;

/**
 * A third-party plugin integration.
 */
interface IntegrationInterface
{
    /**
     * Whether the third-party plugin is active.
     */
    public function isSupported(): bool;

    public function registerHooks(): void;
}
