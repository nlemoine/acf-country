<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration;

use n5s\AcfCountry\Tests\TestCase;

final class PluginTest extends TestCase
{
    public function testRegistersTheCountryFieldType(): void
    {
        $fieldType = $this->fieldType();

        $this->assertSame('country', $fieldType->name);
        $this->assertSame('choice', $fieldType->category);
        $this->assertTrue($fieldType->show_in_rest);
    }

    public function testEnqueuesTheFieldScriptFromTheManifest(): void
    {
        $this->fieldType()->input_admin_enqueue_scripts();

        $this->assertScriptEnqueued('country');

        $manifest = \json_decode((string) \file_get_contents(\dirname(__DIR__, 2) . '/assets/dist/manifest.json'), true);
        $this->assertStringEndsWith('/assets/dist/' . $manifest['field.js'], \wp_scripts()->registered['country']->src);
    }

    public function testEnqueuesTheFieldStyleOnFieldGroupScreens(): void
    {
        $this->fieldType()->field_group_admin_enqueue_scripts();

        $this->assertScriptEnqueued('country');
        $this->assertStyleEnqueued('country');
    }
}
