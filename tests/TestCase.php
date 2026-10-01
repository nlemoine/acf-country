<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests;

use Mantle\Testing\Concerns\Refresh_Database;
use Mantle\Testkit\TestCase as MantleTestCase;
use n5s\AcfCountry\Field\CountryField;

abstract class TestCase extends MantleTestCase
{
    use Refresh_Database;

    protected const FIELD_NAME = 'country';

    protected function setUp(): void
    {
        parent::setUp();

        // ACF caches values per post ID, and rolled back post IDs are reused.
        \acf_get_store('values')->reset();
    }

    protected function fieldType(): CountryField
    {
        $fieldType = \acf_get_field_type('country');
        $this->assertInstanceOf(CountryField::class, $fieldType);

        return $fieldType;
    }

    /**
     * Register a country field on posts and return its settings.
     *
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $groupSettings
     * @return array<string, mixed>
     */
    protected function registerField(array $settings = [], array $groupSettings = []): array
    {
        // ACF caches local fields by key: use a fresh key for each registration.
        $suffix = \uniqid();
        $field = \array_merge(
            [
                'key' => 'field_acf_country_' . $suffix,
                'label' => 'Country',
                'name' => self::FIELD_NAME,
                'type' => 'country',
            ],
            $settings
        );

        \acf_add_local_field_group(\array_merge([
            'key' => 'group_acf_country_' . $suffix,
            'title' => 'Country test',
            'fields' => [$field],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post',
                    ],
                ],
            ],
        ], $groupSettings));

        return \acf_get_field($field['key']);
    }

    protected function useLocale(string $locale): void
    {
        \add_filter('locale', static fn (): string => $locale);
    }
}
