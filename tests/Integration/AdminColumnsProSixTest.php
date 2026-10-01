<?php

declare(strict_types=1);

namespace HelloNico\AcfCountry\Tests\Integration;

use ACA\ACF\Column;
use ACA\ACF\Field;
use HelloNico\AcfCountry\CountryField;
use HelloNico\AcfCountry\Tests\TestCase;

/**
 * Admin Columns Pro before 7 used the ac/column/value filter with ACA\ACF\Column objects.
 */
final class AdminColumnsProSixTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        require_once __DIR__ . '/../fixtures/admin-columns-pro-6.php';
    }

    public function testShowsTheFormattedValueOfACountryColumn(): void
    {
        $field = $this->registerField(['return_format' => CountryField::FORMAT_NAME]);
        $postId = self::factory()->post->create();
        \update_field($field['key'], 'FR', $postId);
        $this->go_to(\get_permalink($postId));
        \the_post();

        $column = new Column(new Field(['type' => 'country']), self::FIELD_NAME);

        $this->assertSame('France', \apply_filters('ac/column/value', 'FR', $postId, $column));
    }

    public function testLeavesOtherColumnsUntouched(): void
    {
        $column = new Column(new Field(['type' => 'text']), 'subtitle');

        $this->assertSame('value', \apply_filters('ac/column/value', 'value', 1, $column));
        $this->assertSame('value', \apply_filters('ac/column/value', 'value', 1, new \stdClass()));
    }
}
