<?php

namespace App\Support\Motivations\Templates;

/**
 * Catalogue of block kinds a motivation template can hold. The builder, the
 * normaliser and the renderer all read from this one list so a new kind
 * only has to be registered in one place (plus a renderer class).
 */
class BlockKinds
{
    public const TITLE_PAGE = 'title_page';

    public const HEADING = 'heading';

    public const TEXT = 'text';

    public const RICH_TEXT = 'rich_text';

    public const IMAGE = 'image';

    public const DYNAMIC_IMAGE = 'dynamic_image';

    public const TWO_COLUMN = 'two_column';

    public const TABLE = 'table';

    public const YES_NO_SENTENCES = 'yes_no_sentences';

    public const CONDITIONAL_GROUP = 'conditional_group';

    public const PAGE_BREAK = 'page_break';

    public const SPACER = 'spacer';

    public const DIVIDER = 'divider';

    public const SIGNATURE = 'signature';

    public const CRIME_CONTEXT = 'crime_context';

    public const DOCUMENT_INSERT = 'document_insert';

    /**
     * Human labels, grouped for the builder's palette.
     *
     * @return array<string, array<string, string>>
     */
    public static function groups(): array
    {
        return [
            'Pages' => [
                self::TITLE_PAGE => 'Title page',
                self::PAGE_BREAK => 'Page break',
            ],
            'Text' => [
                self::HEADING => 'Heading',
                self::TEXT => 'Plain text',
                self::RICH_TEXT => 'Rich text',
                self::YES_NO_SENTENCES => 'Yes/No sentences',
                self::CRIME_CONTEXT => 'Crime context (per province)',
            ],
            'Layout' => [
                self::TWO_COLUMN => 'Two columns',
                self::CONDITIONAL_GROUP => 'Conditional group',
                self::SPACER => 'Spacer',
                self::DIVIDER => 'Divider',
            ],
            'Media' => [
                self::IMAGE => 'Image',
                self::DYNAMIC_IMAGE => 'Member document image',
                self::SIGNATURE => 'Signature block',
            ],
            'Data' => [
                self::TABLE => 'Table',
                self::DOCUMENT_INSERT => 'Insert member documents',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        $out = [];
        foreach (self::groups() as $kinds) {
            foreach ($kinds as $kind => $label) {
                $out[$kind] = $label;
            }
        }

        return $out;
    }

    public static function label(string $kind): string
    {
        return self::all()[$kind] ?? ucfirst(str_replace('_', ' ', $kind));
    }

    public static function exists(string $kind): bool
    {
        return array_key_exists($kind, self::all());
    }
}
