<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration\WpGraphQl;

use n5s\AcfCountry\ReturnFormat;
use n5s\AcfCountry\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class GraphqlTest extends TestCase
{
    private const QUERY = <<<'GRAPHQL'
        query ($id: ID!) {
          post(id: $id, idType: DATABASE_ID) {
            countryDetails { country { code name emoji } }
          }
        }
        GRAPHQL;

    protected function setUp(): void
    {
        parent::setUp();

        // Local field groups outlive a test: earlier groups would shadow the GraphQL names of this one.
        \acf_get_local_store('groups')->reset();
        \acf_get_local_store('fields')->reset();
    }

    protected function tearDown(): void
    {
        // The show_in_graphql groups of this test must not leak into later tests.
        \acf_get_local_store('groups')->reset();
        \acf_get_local_store('fields')->reset();

        parent::tearDown();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function returnFormats(): iterable
    {
        yield 'value' => [ReturnFormat::Value->value];
        yield 'emoji' => [ReturnFormat::Emoji->value];
        yield 'array' => [ReturnFormat::Array->value];
    }

    #[DataProvider('returnFormats')]
    public function testSingleField(string $format): void
    {
        $postId = $this->postWithCountry(['return_format' => $format], 'FR');

        $this->assertSame(
            ['code' => 'FR', 'name' => 'France', 'emoji' => "\u{1F1EB}\u{1F1F7}"],
            $this->country($postId)
        );
    }

    public function testMultipleField(): void
    {
        $postId = $this->postWithCountry(['multiple' => 1], ['FR', 'DE']);

        $this->assertSame(
            [
                ['code' => 'FR', 'name' => 'France', 'emoji' => "\u{1F1EB}\u{1F1F7}"],
                ['code' => 'DE', 'name' => 'Germany', 'emoji' => "\u{1F1E9}\u{1F1EA}"],
            ],
            $this->country($postId)
        );
    }

    public function testEmptySingleField(): void
    {
        $this->assertNull($this->country($this->postWithCountry([], '')));
    }

    public function testEmptyMultipleField(): void
    {
        $this->assertSame([], $this->country($this->postWithCountry(['multiple' => 1], [])));
    }

    public function testUnknownCode(): void
    {
        $postId = $this->postWithCountry([], 'FR');
        \update_post_meta($postId, 'country', 'ZZ');

        $this->assertSame(['code' => 'ZZ', 'name' => null, 'emoji' => ''], $this->country($postId));
    }

    public function testNamesUseTheSiteLanguage(): void
    {
        $this->useLocale('fr_FR');

        $this->assertSame('Allemagne', $this->country($this->postWithCountry([], 'DE'))['name'] ?? null);
    }

    public function testSchemaDeclaresTheType(): void
    {
        $this->postWithCountry([], 'FR');

        // WPGraphQL disables public introspection by default.
        $this->acting_as('administrator');
        $result = \graphql(['query' => '{ __type(name: "AcfCountry") { fields { name } } }']);

        $this->assertArrayNotHasKey('errors', $result, \json_encode($result['errors'] ?? []));
        // Introspection lists fields alphabetically.
        $names = \array_column($result['data']['__type']['fields'] ?? [], 'name');
        \sort($names);
        $this->assertSame(['code', 'emoji', 'name'], $names);
    }

    /**
     * @param array<string, mixed> $settings
     * @param string|list<string> $value
     */
    private function postWithCountry(array $settings, string|array $value): int
    {
        $field = $this->registerField(
            ['name' => 'country', 'show_in_graphql' => 1, 'graphql_field_name' => 'country'] + $settings,
            ['show_in_graphql' => 1, 'graphql_field_name' => 'countryDetails']
        );
        \WPGraphQL::clear_schema();

        $postId = self::factory()->post->create(['post_status' => 'publish']);
        \update_field($field['key'], $value, $postId);

        return $postId;
    }

    /**
     * @return array<string, mixed>|list<array<string, mixed>>|null
     */
    private function country(int $postId): ?array
    {
        $result = \graphql(['query' => self::QUERY, 'variables' => ['id' => $postId]]);
        $this->assertArrayNotHasKey('errors', $result, \json_encode($result['errors'] ?? []));

        return $result['data']['post']['countryDetails']['country'];
    }
}
