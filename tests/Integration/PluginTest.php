<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration;

use n5s\AcfCountry\Field\CountryField;
use n5s\AcfCountry\Plugin;
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

    public function testInitRegistersTheFieldAndSupportedIntegrations(): void
    {
        // The test bootstrap already booted the plugin: reset the singleton to boot a fresh instance.
        $instance = new \ReflectionProperty(Plugin::class, 'instance');
        $saved = $instance->getValue();
        $instance->setValue(null, null);
        $fieldType = $this->fieldType();

        try {
            $fieldTypeHooks = \count($GLOBALS['wp_filter']['acf/include_field_types']->callbacks[10] ?? []);
            $plugin = Plugin::getInstance()->init();

            // The field registers on acf/include_field_types; WPGraphQL for ACF is loaded in tests, Admin Columns is not.
            $this->assertCount($fieldTypeHooks + 1, $GLOBALS['wp_filter']['acf/include_field_types']->callbacks[10]);
            $this->assertNotFalse(\has_action('wpgraphql/acf/registry_init'));
            $this->assertFalse(\has_filter('ac/column/render'));
            $this->assertSame($plugin, $plugin->init());

            \do_action('acf/include_field_types');
            $registered = \acf_get_field_type('country');
            $this->assertInstanceOf(CountryField::class, $registered);
            $this->assertNotSame($fieldType, $registered);
        } finally {
            $instance->setValue(null, $saved);
            \acf_register_field_type($fieldType);
        }
    }

    public function testLoadsTranslationsBeforeAcfBuildsTheFieldLabel(): void
    {
        // ACF fires acf/include_field_types from ACF::init(); the field label is translated there.
        $this->assertSame(4, \has_action('init', 'n5s\\AcfCountry\\load_textdomain'));
        $this->assertSame(5, \has_action('init', [\acf(), 'init']));
    }

    public function testHooksCanBeRemoved(): void
    {
        $this->assertSame(0, \has_action('plugins_loaded', 'n5s\\AcfCountry\\boot'));
        $this->assertSame(10, \has_action('acf/include_field_types', [Plugin::getInstance(), 'registerFieldType']));
    }

    public function testEnqueuesTheFieldScriptFromTheManifest(): void
    {
        $this->fieldType()->input_admin_enqueue_scripts();

        $this->assertScriptEnqueued('acf-country-field');

        $manifest = \json_decode((string) \file_get_contents(\dirname(__DIR__, 2) . '/assets/dist/manifest.json'), true);
        $this->assertStringEndsWith('/assets/dist/' . $manifest['field.js'], \wp_scripts()->registered['acf-country-field']->src);
    }

    public function testEnqueuesTheFieldStyleOnFieldGroupScreens(): void
    {
        $this->fieldType()->field_group_admin_enqueue_scripts();

        $this->assertScriptEnqueued('acf-country-field');
        $this->assertStyleEnqueued('acf-country-field');
    }
}
