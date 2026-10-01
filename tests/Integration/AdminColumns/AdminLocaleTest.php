<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration\AdminColumns;

use AC\Column\CustomFieldContext;
use AC\MetaType;
use AC\Setting\Config;
use AC\Type\TableScreenContext;
use Mantle\Testing\Concerns\Admin_Screen;
use n5s\AcfCountry\Tests\TestCase;

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
}
