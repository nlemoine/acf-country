<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Tests\Integration\Core;

use AC\Column\CustomFieldContext;
use n5s\AcfCountry\Tests\TestCase;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;

/**
 * The integration hooks are always registered: without their plugins, they must stay inert.
 */
final class IntegrationsTest extends TestCase
{
    public function testRunsWithoutTheIntegratedPlugins(): void
    {
        $this->assertFalse(\function_exists('register_graphql_acf_field_type'));
        $this->assertFalse(\class_exists(CustomFieldContext::class));
    }

    #[DoesNotPerformAssertions]
    public function testWpGraphQlWithoutWpGraphQlForAcfDeclaresNoType(): void
    {
        // WPGraphQL alone fires graphql_register_types; register_graphql_object_type() does not exist here either.
        \do_action('graphql_register_types');
    }

    public function testAdminColumnsRenderIgnoresOtherContexts(): void
    {
        $this->assertSame('FR', \apply_filters('ac/column/render', 'FR', null, 1));
    }
}
