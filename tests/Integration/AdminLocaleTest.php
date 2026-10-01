<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration;

use AC\Column\CustomFieldContext;
use AC\MetaType;
use AC\Setting\Config;
use AC\Type\TableScreenContext;
use HelloNico\AcfCountry\CountryField;
use Mantle\Testing\Concerns\Admin_Screen;
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

    public function testAdminColumnsUseTheUserLanguage(): void
    {
        $field = $this->registerField();
        $postId = self::factory()->post->create();
        \update_field($field['key'], 'DE', $postId);

        $context = new CustomFieldContext(
            new Config(['type' => 'column-meta', 'field' => self::FIELD_NAME]),
            'Custom Field',
            '',
            self::FIELD_NAME,
            new TableScreenContext(new MetaType(MetaType::POST))
        );

        $this->assertSame("\u{1F1E9}\u{1F1EA} Allemagne", \apply_filters('ac/column/render', 'DE', $context, $postId));
    }

    public function testFormattedValuesKeepTheSiteLanguage(): void
    {
        $field = $this->registerField(['return_format' => CountryField::FORMAT_NAME]);
        $postId = self::factory()->post->create();
        \update_field($field['key'], 'DE', $postId);

        $this->assertSame('Germany', \get_field($field['key'], $postId));
    }
}
