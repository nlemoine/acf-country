<?php

// Minimal stand-ins for the Admin Columns Pro 6 classes acf-country.php uses.

declare(strict_types=1);

namespace ACA\ACF;

class Field
{
    /** @param array<string, mixed> $settings */
    public function __construct(private array $settings)
    {
    }

    /** @return array<string, mixed> */
    public function get_settings(): array
    {
        return $this->settings;
    }
}

class Column
{
    public function __construct(private Field $field, private string $metaKey)
    {
    }

    public function get_field(): Field
    {
        return $this->field;
    }

    public function get_meta_key(): string
    {
        return $this->metaKey;
    }
}
