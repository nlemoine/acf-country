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

    public function testListsOnlyTheInvalidCountryCode(): void
    {
        $field = $this->registerField(['multiple' => 1]);

        self::assertSame(
            'ZZ is not a valid country code',
            $this->fieldType()->validate_value(true, ['FR', 'ZZ'], $field, 'acf[field]')
        );
    }

    public function testListsOnlyTheInvalidCountryCodes(): void
    {
        $field = $this->registerField(['multiple' => 1]);

        self::assertSame(
            'ZZ, XX are not valid country codes',
            $this->fieldType()->validate_value(true, ['FR', 'ZZ', 'DE', 'XX'], $field, 'acf[field]')
        );
    }

    public function testAcceptsLowercaseCountryCodes(): void
    {
        $field = $this->registerField(['multiple' => 1]);

        self::assertTrue($this->fieldType()->validate_value(true, 'fr', $field, 'acf[field]'));
        self::assertTrue($this->fieldType()->validate_value(true, ['fr', 'DE'], $field, 'acf[field]'));
    }

    public function testRejectsNonStringValues(): void
    {
        $field = $this->registerField(['multiple' => 1]);

        self::assertSame(
            'array is not a valid country code',
            $this->fieldType()->validate_value(true, [['FR']], $field, 'acf[field]')
        );
    }

    public function testEscapesInvalidValuesInTheMessage(): void
    {
        $field = $this->registerField();

        self::assertSame(
            '&lt;b&gt; is not a valid country code',
            $this->fieldType()->validate_value(true, '<b>', $field, 'acf[field]')
        );
    }

    public function testKeepsAnEarlierValidationError(): void
    {
        $field = $this->registerField();

        self::assertSame('Required', $this->fieldType()->validate_value('Required', '', $field, 'acf[field]'));
    }
}
