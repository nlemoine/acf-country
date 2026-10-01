<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Integration;

/**
 * A third-party plugin integration.
 *
 * @internal Not part of the public API; may change in minor versions.
 */
interface IntegrationInterface
{
    /**
     * Whether the third-party plugin is active.
     */
    public function isSupported(): bool;

    public function registerHooks(): void;
}
