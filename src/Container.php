<?php

declare(strict_types=1);

namespace n5s\AcfCountry;

use InvalidArgumentException;
use n5s\AcfCountry\Integration\AdminColumns;
use n5s\AcfCountry\Integration\WpGraphQl;

/**
 * Builds services on first use.
 */
final class Container
{
    /**
     * @var array<class-string, object>
     */
    private array $services = [];

    /**
     * @var array<class-string, callable(): object>
     */
    private array $factories;

    public function __construct(string $pluginDir)
    {
        $this->factories = [
            Countries::class => static fn (): Countries => new Countries($pluginDir . '/data'),
            AdminColumns::class => fn (): AdminColumns => new AdminColumns(
                $this->get(Countries::class)
            ),
            WpGraphQl::class => fn (): WpGraphQl => new WpGraphQl(
                $this->get(Countries::class)
            ),
        ];
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $name
     *
     * @return T
     */
    public function get(string $name): object
    {
        if (!isset($this->services[$name])) {
            if (!isset($this->factories[$name])) {
                // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
                throw new InvalidArgumentException("Unknown service: {$name}");
            }

            $this->services[$name] = $this->factories[$name]();
        }

        /** @var T $service */
        $service = $this->services[$name];

        return $service;
    }

    /**
     * @param class-string $name
     */
    public function has(string $name): bool
    {
        return isset($this->factories[$name]);
    }
}
