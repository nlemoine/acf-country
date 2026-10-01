<?php

declare(strict_types=1);

namespace n5s\AcfCountry;

use n5s\AcfCountry\Field\CountryField;
use n5s\AcfCountry\Integration\AdminColumns;
use n5s\AcfCountry\Integration\WpGraphQl;

/**
 * Wires the field and the integrations.
 */
final class Plugin
{
    /**
     * Integrations, booted when supported.
     *
     * @internal Not part of the public API; may change in minor versions.
     *
     * @var list<class-string<Integration\IntegrationInterface>>
     */
    public const INTEGRATIONS = [AdminColumns::class, WpGraphQl::class];

    private static ?self $instance = null;

    private bool $initialized = false;

    private function __construct(private readonly Container $container)
    {
    }

    public static function getInstance(): self
    {
        // src/ lives in the plugin directory.
        return self::$instance ??= new self(new Container(\dirname(__DIR__)));
    }

    /**
     * @internal Not part of the public API; may change in minor versions.
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    public function countries(): Countries
    {
        return $this->container->get(Countries::class);
    }

    public function init(): self
    {
        if ($this->initialized) {
            return $this;
        }

        \add_action('acf/include_field_types', [$this, 'registerFieldType']);

        foreach (self::INTEGRATIONS as $class) {
            $integration = $this->container->get($class);
            if ($integration->isSupported()) {
                $integration->registerHooks();
            }
        }

        $this->initialized = true;

        return $this;
    }

    public function registerFieldType(): void
    {
        \acf_register_field_type($this->container->get(CountryField::class));
    }
}
