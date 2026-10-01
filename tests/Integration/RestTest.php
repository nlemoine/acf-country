<?php

declare(strict_types=1);

namespace HelloNico\AcfCountry\Tests\Integration;

use HelloNico\AcfCountry\Tests\TestCase;

class RestTest extends TestCase
{
    public function testSchemaListsCountryCodes(): void
    {
        $field = $this->registerField(['multiple' => 1]);

        $schema = $this->fieldType()->get_rest_schema($field);

        self::assertContains('array', $schema['type']);
        self::assertContains('FR', $schema['items']['enum']);
        self::assertArrayNotHasKey('maxItems', $schema);
    }

    public function testWritesMultipleCountries(): void
    {
        $field = $this->registerRestField(['multiple' => 1]);
        $postId = static::factory()->post->create();
        $this->acting_as('administrator');

        $this->post_json("/wp-json/wp/v2/posts/{$postId}", ['acf' => [$field['name'] => ['FR', 'DE']]])
            ->assertOk();

        self::assertSame(['FR', 'DE'], \get_post_meta($postId, $field['name'], true));
    }

    public function testRejectsAnInvalidCountryCode(): void
    {
        $field = $this->registerRestField();
        $postId = static::factory()->post->create();
        $this->acting_as('administrator');

        $this->post_json("/wp-json/wp/v2/posts/{$postId}", ['acf' => [$field['name'] => 'ZZ']])
            ->assertStatus(400);

        self::assertSame('', \get_post_meta($postId, $field['name'], true));
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private function registerRestField(array $settings = []): array
    {
        // A unique name: REST resolves fields by name across all field groups.
        return $this->registerField(
            $settings + ['name' => 'country_' . \uniqid()],
            ['show_in_rest' => 1]
        );
    }
}
