<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration\Core;

use Mantle\Testing\Concerns\Admin_Screen;
use n5s\AcfCountry\ReturnFormat;
use n5s\AcfCountry\Tests\TestCase;

use function Mantle\Support\Helpers\capture;
use function Mantle\Testing\html_string;

/**
 * The site is in English, the logged-in user reads the admin in French.
 */
final class AdminLocaleTest extends TestCase
{
    use Admin_Screen;

    protected function setUp(): void
    {
        parent::setUp();

        $this->acting_as(self::factory()->user->create(['role' => 'administrator', 'locale' => 'fr_FR']));
    }

    public function testFieldOptionsUseTheUserLanguage(): void
    {
        $field = $this->registerField();
        $field['value'] = 'DE';

        $html = html_string(capture(static fn () => \acf_render_field($field)));

        $this->assertSame("\u{1F1E9}\u{1F1EA}\u{00A0}\u{00A0}Allemagne", $html->first_by_selector('option[value="DE"]')->text());
    }

    public function testFieldSettingsUseTheUserLanguage(): void
    {
        $field = $this->registerField();

        $html = capture(fn () => $this->fieldType()->render_field_settings($field));

        $this->assertStringContainsString('DE : Allemagne', $html);
    }

    public function testFormattedValuesKeepTheSiteLanguage(): void
    {
        $field = $this->registerField(['return_format' => ReturnFormat::Name->value]);
        $postId = self::factory()->post->create();
        \update_field($field['key'], 'DE', $postId);

        $this->assertSame('Germany', \get_field($field['key'], $postId));
    }
}
