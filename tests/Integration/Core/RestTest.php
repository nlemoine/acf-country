<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration\Core;

use n5s\AcfCountry\Tests\TestCase;

final class RestTest extends TestCase
{
    public function testSchemaListsCountryCodes(): void
    {
        $field = $this->registerField(['multiple' => 1]);

        $schema = $this->fieldType()->get_rest_schema($field);

        $this->assertContains('array', $schema['type']);
        $this->assertContains('FR', $schema['items']['enum']);
        $this->assertArrayNotHasKey('maxItems', $schema);
    }

    public function testWritesMultipleCountries(): void
    {
        $field = $this->registerRestField(['multiple' => 1]);
        $postId = self::factory()->post->create();
        $this->acting_as('administrator');

        $this->post_json("/wp-json/wp/v2/posts/{$postId}", ['acf' => [$field['name'] => ['FR', 'DE']]])
            ->assertOk();

        $this->assertSame(['FR', 'DE'], \get_post_meta($postId, $field['name'], true));
    }

    public function testKeepsAnEarlierValidationError(): void
    {
        $error = new \WP_Error('rest_invalid_param', 'Invalid');

        $this->assertSame($error, $this->fieldType()->validate_rest_value($error, 'FR', $this->registerField()));
    }

    public function testRejectsAnInvalidCountryCode(): void
    {
        $field = $this->registerRestField();
        $postId = self::factory()->post->create();
        $this->acting_as('administrator');

        $this->post_json("/wp-json/wp/v2/posts/{$postId}", ['acf' => [$field['name'] => 'ZZ']])
            ->assertStatus(400);

        $this->assertSame('', \get_post_meta($postId, $field['name'], true));
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
