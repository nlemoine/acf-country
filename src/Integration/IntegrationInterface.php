<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Integration;

/**
 * A third-party plugin integration.
 *
 * Its hooks are always registered: they belong to the third-party plugin, so they only fire when it is active.
 *
 * @internal Not part of the public API; may change in minor versions.
 */
interface IntegrationInterface
{
    public function registerHooks(): void;
}
