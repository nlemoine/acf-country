<?php

declare(strict_types=1);

namespace n5s\AcfCountry\Integration;

use AC\Column\CustomFieldContext;
use n5s\AcfCountry\Countries;
use n5s\AcfCountry\Flag;

/**
 * Admin Columns 7+: country flags and names in custom field columns.
 */
final readonly class AdminColumns implements IntegrationInterface
{
    public function __construct(private Countries $countries)
    {
    }

    public function isSupported(): bool
    {
        // Defined by the plugin file; Admin Columns classes are only autoloaded later, on after_setup_theme.
        return \defined('AC_FILE');
    }

    public function registerHooks(): void
    {
        \add_filter('ac/column/render', $this->render(...), 10, 3);
    }

    public function render(mixed $value, mixed $context, mixed $id): mixed
    {
        if (!$context instanceof CustomFieldContext || !\is_numeric($id)) {
            return $value;
        }

        // Admin Columns can be active without ACF. Checked here rather than in isSupported(): ACF bundled in a theme loads after plugins.
        if (!\function_exists('get_field_object')) {
            return $value;
        }

        // ACF post ID format for each Admin Columns meta type.
        $postId = [
            'post' => (int) $id,
            'user' => 'user_' . $id,
            'term' => 'term_' . $id,
            'comment' => 'comment_' . $id,
        ][$context->get_meta_type()] ?? null;
        if ($postId === null) {
            return $value;
        }

        $field = \get_field_object($context->get_meta_key(), $postId, false);
        if (!\is_array($field) || $field['type'] !== 'country') {
            return $value;
        }

        // Admin screens follow the language of the logged-in user.
        $countries = $this->countries->all(\determine_locale());
        $names = [];
        foreach ((array) $field['value'] as $code) {
            if (\is_string($code) && isset($countries[$code])) {
                $names[] = \trim(Flag::fromCode($code) . ' ' . $countries[$code]);
            }
        }

        return $names === [] ? $value : \esc_html(\implode(', ', $names));
    }
}
