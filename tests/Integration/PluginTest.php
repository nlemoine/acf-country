<?php

declare(strict_types=1);

namespace HelloNico\AcfCountry\Tests\Integration;

use HelloNico\AcfCountry\Tests\TestCase;

class PluginTest extends TestCase
{
    public function testRegistersTheCountryFieldType(): void
    {
        $fieldType = $this->fieldType();

        self::assertSame('country', $fieldType->name);
        self::assertSame('choice', $fieldType->category);
        self::assertTrue($fieldType->show_in_rest);
    }

    public function testEnqueuesTheFieldScriptFromTheManifest(): void
    {
        $this->fieldType()->input_admin_enqueue_scripts();

        $this->assertScriptEnqueued('country');

        $manifest = \json_decode((string) \file_get_contents(\dirname(__DIR__, 2) . '/assets/dist/manifest.json'), true);
        self::assertStringEndsWith(
            '/assets/dist/' . $manifest['field.js'],
            \wp_scripts()->registered['country']->src
        );
    }

    public function testEnqueuesTheFieldStyleOnFieldGroupScreens(): void
    {
        $this->fieldType()->field_group_admin_enqueue_scripts();

        $this->assertScriptEnqueued('country');
        $this->assertStyleEnqueued('country');
    }
}
