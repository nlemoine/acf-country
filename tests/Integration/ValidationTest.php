<?php

declare(strict_types=1);

namespace HelloNico\AcfCountry\Tests\Integration;

use HelloNico\AcfCountry\Tests\TestCase;

class ValidationTest extends TestCase
{
    public function testAcceptsAValidCountryCode(): void
    {
        $field = $this->registerField();

        self::assertTrue($this->fieldType()->validate_value(true, 'FR', $field, 'acf[field]'));
    }

    public function testAcceptsValidCountryCodes(): void
    {
        $field = $this->registerField(['multiple' => 1]);

        self::assertTrue($this->fieldType()->validate_value(true, ['FR', 'DE'], $field, 'acf[field]'));
    }

    public function testAcceptsAnEmptyValue(): void
    {
        $field = $this->registerField();

        self::assertTrue($this->fieldType()->validate_value(true, '', $field, 'acf[field]'));
    }

    public function testRejectsAnInvalidCountryCode(): void
    {
        $field = $this->registerField();

        self::assertSame(
            'ZZ is not a valid country code',
            $this->fieldType()->validate_value(true, 'ZZ', $field, 'acf[field]')
        );
    }

    public function testRejectsInvalidCountryCodes(): void
    {
        $field = $this->registerField(['multiple' => 1]);

        $valid = $this->fieldType()->validate_value(true, ['FR', 'ZZ'], $field, 'acf[field]');

        self::assertIsString($valid);
        self::assertStringContainsString('ZZ', $valid);
    }

    public function testKeepsAnEarlierValidationError(): void
    {
        $field = $this->registerField();

        self::assertSame('Required', $this->fieldType()->validate_value('Required', '', $field, 'acf[field]'));
    }
}
