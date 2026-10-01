<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration;

use HelloNico\AcfCountry\CountryField;
use n5s\AcfCountry\Tests\TestCase;

final class GraphqlTest extends TestCase
{
    public function testLeavesOtherFieldTypesUntouched(): void
    {
        $config = ['type' => 'String', 'resolve' => static fn (): string => 'x'];

        $this->assertSame($config, $this->registerGraphqlField($config, ['type' => 'text']));
    }

    public function testValueFormat(): void
    {
        $config = $this->registerGraphqlField(
            ['resolve' => static fn (): string => 'FR'],
            $this->acfField(CountryField::FORMAT_VALUE)
        );

        $this->assertSame('String', $config['type']);
        $this->assertSame('FR', $config['resolve'](null, [], null, null));
    }

    public function testNameFormat(): void
    {
        $config = $this->registerGraphqlField(
            ['resolve' => static fn (): array => ['FR', 'DE']],
            $this->acfField(CountryField::FORMAT_NAME, true)
        );

        $this->assertSame(['list_of' => 'String'], $config['type']);
        $this->assertSame(['France', 'Germany'], $config['resolve'](null, [], null, null));
    }

    public function testArrayFormat(): void
    {
        $config = $this->registerGraphqlField(
            ['resolve' => static fn (): string => 'FR'],
            $this->acfField(CountryField::FORMAT_ARRAY)
        );

        $this->assertSame(['value' => 'FR', 'label' => 'France'], $config['resolve'](null, [], null, null));
    }

    public function testEmptyValues(): void
    {
        $resolve = static fn (): string => '';

        $this->assertNull($this->registerGraphqlField(['resolve' => $resolve], $this->acfField(CountryField::FORMAT_VALUE))['resolve'](null, [], null, null));
        $this->assertNull($this->registerGraphqlField(['resolve' => $resolve], $this->acfField(CountryField::FORMAT_NAME))['resolve'](null, [], null, null));
        $this->assertSame([], $this->registerGraphqlField(['resolve' => $resolve], $this->acfField(CountryField::FORMAT_ARRAY))['resolve'](null, [], null, null));
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
            'choices' => ['FR' => 'France', 'DE' => 'Germany'],
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
