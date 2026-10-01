<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration\AdminColumns;

use AC\Column\Context;
use AC\Column\CustomFieldContext;
use AC\MetaType;
use AC\Setting\Config;
use AC\Type\TableScreenContext;
use n5s\AcfCountry\ReturnFormat;
use n5s\AcfCountry\Tests\TestCase;

final class AdminColumnsTest extends TestCase
{
    public function testShowsCountryNamesWithFlags(): void
    {
        $field = $this->registerField(['multiple' => 1]);
        $postId = self::factory()->post->create();
        \update_field($field['key'], ['FR', 'DE'], $postId);

        $this->assertSame(
            "\u{1F1EB}\u{1F1F7} France, \u{1F1E9}\u{1F1EA} Germany",
            $this->render('FR, DE', $this->customFieldContext(MetaType::POST), $postId)
        );
    }

    public function testShowsTheNameWhateverTheReturnFormat(): void
    {
        $field = $this->registerField(['return_format' => ReturnFormat::Value->value]);
        $postId = self::factory()->post->create();
        \update_field($field['key'], 'FR', $postId);

        $this->assertSame("\u{1F1EB}\u{1F1F7} France", $this->render('FR', $this->customFieldContext(MetaType::POST), $postId));
    }

    public function testUsesTheRowObject(): void
    {
        $field = $this->registerField();
        [$france, $germany] = self::factory()->post->create_many(2);
        \update_field($field['key'], 'FR', $france);
        \update_field($field['key'], 'DE', $germany);

        $this->assertSame("\u{1F1E9}\u{1F1EA} Germany", $this->render('DE', $this->customFieldContext(MetaType::POST), $germany));
    }

    public function testSupportsUserColumns(): void
    {
        $field = $this->registerField();
        $userId = self::factory()->user->create();
        \update_field($field['key'], 'JP', 'user_' . $userId);

        $this->assertSame("\u{1F1EF}\u{1F1F5} Japan", $this->render('JP', $this->customFieldContext(MetaType::USER), $userId));
    }

    public function testLeavesEmptyValuesUntouched(): void
    {
        $this->registerField();
        $postId = self::factory()->post->create();

        $this->assertSame('&ndash;', $this->render('&ndash;', $this->customFieldContext(MetaType::POST), $postId));
    }

    public function testLeavesOtherCustomFieldsUntouched(): void
    {
        $postId = self::factory()->post->create();
        \update_post_meta($postId, 'subtitle', 'FR');

        $this->assertSame('FR', $this->render('FR', $this->customFieldContext(MetaType::POST, 'subtitle'), $postId));
    }

    public function testLeavesUnsupportedMetaTypesUntouched(): void
    {
        $this->assertSame('FR', $this->render('FR', $this->customFieldContext(MetaType::SITE), 1));
    }

    public function testLeavesOtherColumnsUntouched(): void
    {
        $postId = self::factory()->post->create();

        $this->assertSame('Title', $this->render('Title', new Context(new Config(['type' => 'title']), 'Title'), $postId));
    }

    private function customFieldContext(string $metaType, string $metaKey = self::FIELD_NAME): CustomFieldContext
    {
        return new CustomFieldContext(
            new Config(['type' => 'column-meta', 'field' => $metaKey]),
            'Custom Field',
            '',
            $metaKey,
            new TableScreenContext(new MetaType($metaType))
        );
    }

    private function render(string $value, Context $context, int $id): string
    {
        return \apply_filters('ac/column/render', $value, $context, $id);
    }
}
