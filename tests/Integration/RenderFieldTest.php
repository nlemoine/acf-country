<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration;

use n5s\AcfCountry\Tests\TestCase;

use function Mantle\Support\Helpers\capture;
use function Mantle\Testing\html_string;

final class RenderFieldTest extends TestCase
{
    public function testRendersASelectOfCountriesWithFlags(): void
    {
        $field = $this->registerField();
        $field['value'] = 'FR';

        $html = html_string(capture(static fn () => \acf_render_field($field)));

        $html->assertQuerySelectorCount('select option', 249);
        $option = $html->first_by_selector('option[value="FR"]');
        $this->assertSame('selected', $option->get_attribute('selected'));
        $this->assertSame("\u{1F1EB}\u{1F1F7}\u{00A0}\u{00A0}France", $option->text());
    }

    public function testUppercasesStoredValuesWhenRendering(): void
    {
        $field = $this->registerField(['multiple' => 1]);
        $field['value'] = ['fr', 'de'];

        $html = html_string(capture(static fn () => \acf_render_field($field)));

        $this->assertSame('selected', $html->first_by_selector('option[value="FR"]')->get_attribute('selected'));
        $this->assertSame('selected', $html->first_by_selector('option[value="DE"]')->get_attribute('selected'));
    }

    public function testRendersTheValidationAndPresentationSettings(): void
    {
        $field = $this->registerField();

        $this->assertStringContainsString('[allow_null]', capture(fn () => $this->fieldType()->render_field_validation_settings($field)));
        $this->assertStringContainsString('[ui]', capture(fn () => $this->fieldType()->render_field_presentation_settings($field)));
    }

    public function testDecodesSettingsWhenTheFieldIsSaved(): void
    {
        $field = $this->fieldType()->update_field($this->registerField(['default_value' => "FR\nDE", 'multiple' => 1]));

        $this->assertSame(['FR', 'DE'], $field['default_value']);
    }

    public function testLoadsAnEmptyValueUnchanged(): void
    {
        $field = $this->registerField();

        $this->assertNull($this->fieldType()->load_value(null, self::factory()->post->create(), $field));
    }

    public function testSelectsALowercaseSingleValue(): void
    {
        $field = $this->registerField();
        $field['value'] = 'fr';

        $html = html_string(capture(static fn () => \acf_render_field($field)));

        $this->assertSame('selected', $html->first_by_selector('option[value="FR"]')->get_attribute('selected'));
    }
}
