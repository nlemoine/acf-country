<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration\Core;

use n5s\AcfCountry\Field\CountryField;
use n5s\AcfCountry\Integration\AdminColumns;
use n5s\AcfCountry\Integration\WpGraphQl;
use n5s\AcfCountry\Plugin;
use n5s\AcfCountry\Tests\TestCase;

use function Mantle\Support\Helpers\capture;

final class PluginTest extends TestCase
{
    public function testRegistersTheCountryFieldType(): void
    {
        $fieldType = $this->fieldType();

        $this->assertSame('country', $fieldType->name);
        $this->assertSame('choice', $fieldType->category);
        $this->assertTrue($fieldType->show_in_rest);
    }

    public function testInitRegistersTheFieldAndTheIntegrations(): void
    {
        // The test bootstrap already booted the plugin: reset the singleton to boot a fresh instance.
        $instance = new \ReflectionProperty(Plugin::class, 'instance');
        $saved = $instance->getValue();
        $instance->setValue(null, null);
        $fieldType = $this->fieldType();

        try {
            $fieldTypeHooks = \count($GLOBALS['wp_filter']['acf/include_field_types']->callbacks[10] ?? []);
            $plugin = Plugin::getInstance()->init();

            // The field registers on acf/include_field_types; integrations hook into their plugin's hooks.
            $this->assertCount($fieldTypeHooks + 1, $GLOBALS['wp_filter']['acf/include_field_types']->callbacks[10]);
            $this->assertNotFalse(\has_action('wpgraphql/acf/registry_init', [$plugin->getContainer()->get(WpGraphQl::class), 'registerFieldType']));
            $this->assertNotFalse(\has_filter('ac/column/render', [$plugin->getContainer()->get(AdminColumns::class), 'render']));
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
        $this->assertSame(10, \has_action('plugins_loaded', 'n5s\\AcfCountry\\boot'));
        $this->assertSame(10, \has_action('acf/include_field_types', [Plugin::getInstance(), 'registerFieldType']));
    }

    public function testEnqueuesTheFieldScriptFromTheManifest(): void
    {
        $this->fieldType()->input_admin_enqueue_scripts();

        $this->assertScriptEnqueued('acf-country-field');
        $this->assertBuiltAsset('field.js', \wp_scripts()->registered['acf-country-field']->src);
    }

    public function testEnqueuesTheFieldStyleOnFieldGroupScreens(): void
    {
        $this->fieldType()->field_group_admin_enqueue_scripts();

        $this->assertScriptEnqueued('acf-country-field');
        $this->assertStyleEnqueued('acf-country-field');
        $this->assertBuiltAsset('field.css', \wp_styles()->registered['acf-country-field']->src);
    }

    public function testLoadsTheRtlStyleOnRtlSites(): void
    {
        // WP_Styles reads the text direction once, when it is created.
        $styles = \wp_styles();
        $direction = $styles->text_direction;
        $styles->text_direction = 'rtl';

        try {
            $this->fieldType()->field_group_admin_enqueue_scripts();
            $html = capture(static fn () => $styles->do_item('acf-country-field'));
        } finally {
            $styles->text_direction = $direction;
        }

        $this->assertMatchesRegularExpression('#/assets/dist/(field-[0-9a-f]{20}-rtl\.css)#', $html);
        \preg_match('#/assets/dist/(field-[0-9a-f]{20}-rtl\.css)#', $html, $matches);
        $this->assertFileExists(\dirname(__DIR__, 3) . '/assets/dist/' . $matches[1]);
    }

    /**
     * The asset URL points to the hashed file listed in the manifest, and the file exists.
     */
    private function assertBuiltAsset(string $asset, mixed $src): void
    {
        $dist = \dirname(__DIR__, 3) . '/assets/dist';
        $manifest = \json_decode((string) \file_get_contents("{$dist}/manifest.json"), true);
        $this->assertIsArray($manifest);
        $this->assertIsString($manifest[$asset] ?? null);
        $this->assertMatchesRegularExpression('/-[0-9a-f]{20}\.(js|css)\z/', $manifest[$asset]);
        $this->assertFileExists("{$dist}/{$manifest[$asset]}");
        $this->assertIsString($src);
        $this->assertStringEndsWith("/assets/dist/{$manifest[$asset]}", $src);
    }
}
