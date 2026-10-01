<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration;

use n5s\AcfCountry\Integration\WpGraphQl;
use n5s\AcfCountry\Plugin;
use n5s\AcfCountry\ReturnFormat;
use n5s\AcfCountry\Tests\TestCase;

final class GraphqlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // WPGraphQL for ACF is not active in tests: register the integration hooks directly.
        Plugin::getInstance()->getContainer()->get(WpGraphQl::class)->registerHooks();
    }

    public function testLeavesOtherFieldTypesUntouched(): void
    {
        $config = ['type' => 'String', 'resolve' => static fn (): string => 'x'];

        $this->assertSame($config, $this->registerGraphqlField($config, ['type' => 'text']));
    }

    public function testValueFormat(): void
    {
        $config = $this->registerGraphqlField(
            ['resolve' => static fn (): string => 'FR'],
            $this->acfField(ReturnFormat::Value->value)
        );

        $this->assertSame('String', $config['type']);
        $this->assertSame('FR', $config['resolve'](null, [], null, null));
    }

    public function testNameFormat(): void
    {
        $config = $this->registerGraphqlField(
            ['resolve' => static fn (): array => ['FR', 'DE']],
            $this->acfField(ReturnFormat::Name->value, true)
        );

        $this->assertSame(['list_of' => 'String'], $config['type']);
        $this->assertSame(['France', 'Germany'], $config['resolve'](null, [], null, null));
    }

    public function testArrayFormat(): void
    {
        $config = $this->registerGraphqlField(
            ['resolve' => static fn (): string => 'FR'],
            $this->acfField(ReturnFormat::Array->value)
        );

        $this->assertSame(['value' => 'FR', 'label' => 'France'], $config['resolve'](null, [], null, null));
    }

    public function testDeclaresCountryAsASupportedField(): void
    {
        $this->assertContains('country', \apply_filters('wpgraphql_acf_supported_fields', ['text']));
    }

    public function testNamesUseTheSiteLanguage(): void
    {
        \add_filter('locale', static fn (): string => 'fr_FR');

        $config = $this->registerGraphqlField(
            ['resolve' => static fn (): string => 'DE'],
            $this->acfField(ReturnFormat::Name->value)
        );

        $this->assertSame('Allemagne', $config['resolve'](null, [], null, null));
    }

    public function testEmptyValues(): void
    {
        $resolve = static fn (): string => '';

        $this->assertNull($this->registerGraphqlField(['resolve' => $resolve], $this->acfField(ReturnFormat::Value->value))['resolve'](null, [], null, null));
        $this->assertNull($this->registerGraphqlField(['resolve' => $resolve], $this->acfField(ReturnFormat::Name->value))['resolve'](null, [], null, null));
        $this->assertSame([], $this->registerGraphqlField(['resolve' => $resolve], $this->acfField(ReturnFormat::Array->value))['resolve'](null, [], null, null));
    }

    /**
     * @return array<string, mixed>
     */
    private function acfField(string $format, bool $multiple = false): array
    {
        return [
            'type' => 'country',
            'return_format' => $format,
            'multiple' => (int) $multiple,
        ];
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $acfField
     * @return array<string, mixed>
     */
    private function registerGraphqlField(array $config, array $acfField): array
    {
        return \apply_filters('wpgraphql_acf_register_graphql_field', $config, 'Post', 'country', ['acf_field' => $acfField]);
    }
}
