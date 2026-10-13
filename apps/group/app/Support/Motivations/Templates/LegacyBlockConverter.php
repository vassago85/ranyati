<?php

namespace App\Support\Motivations\Templates;

use App\Models\ApplicationType;
use App\Models\MotivationTemplate;
use App\Models\MotivationTemplateVersion;
use App\Support\Motivations\QuestionSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Converts an application type's legacy `application_type_blocks` rows into
 * a new motivation template with a published v1. Idempotent per type: a
 * second run reuses the existing template and leaves its versions alone.
 *
 * Kind map:
 *   title_page / heading / text / image / page_break / document_insert   1:1
 *   yes_list_paragraph  →  yes_no_sentences  (scope=field, empty=label)
 *   firearms_table      →  table             (source=firearms)
 *   consecutive text blocks each gated on `answers.province equals X`
 *                       →  single crime_context  (province → body map)
 */
class LegacyBlockConverter
{
    /**
     * Convert a type's blocks and persist a template + v1 + default pivot.
     * Returns the created template, or the existing one if the conversion
     * has already been done.
     */
    public function convert(ApplicationType $type, ?int $actorId = null): MotivationTemplate
    {
        return DB::transaction(function () use ($type, $actorId) {
            $existing = $this->findConvertedTemplate($type);
            if ($existing) {
                return $existing;
            }

            $template = MotivationTemplate::create([
                'name' => $type->name,
                'slug' => $this->uniqueSlug($type),
                'category' => $type->category,
                'description' => $type->description,
                'status' => MotivationTemplate::STATUS_PUBLISHED,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            $version = MotivationTemplateVersion::create([
                'template_id' => $template->id,
                'version' => 1,
                'blocks' => $this->convertBlocks($type->blockDefinitions()),
                'settings' => MotivationTemplateVersion::defaultSettings(),
                'published_at' => now(),
                'published_by' => $actorId,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            $template->update(['published_version_id' => $version->id]);

            $type->templates()->syncWithoutDetaching([
                $template->id => ['is_default' => true, 'sort' => 0, 'condition' => null],
            ]);

            return $template;
        });
    }

    /**
     * Converts a flat legacy block list into the new schema. Public so the
     * fidelity tests can exercise it without touching the DB.
     *
     * @param  array<int, array<string, mixed>>  $legacy
     * @return array<int, array<string, mixed>>
     */
    public function convertBlocks(array $legacy): array
    {
        $out = [];
        $buffer = [];

        $flush = function () use (&$buffer, &$out) {
            if ($buffer === []) {
                return;
            }
            // A buffered run of province-gated text blocks collapses into
            // one crime_context block whose body map preserves the text
            // verbatim and whose fallback is left empty.
            $bodies = [];
            foreach ($buffer as $item) {
                $bodies[$item['province']] = $item['body'];
            }
            $out[] = $this->block(BlockKinds::CRIME_CONTEXT, [
                'province_field' => 'answers.province',
                'bodies' => $bodies,
                'fallback_body' => '',
            ]);
            $buffer = [];
        };

        foreach ($legacy as $def) {
            $kind = $def['kind'] ?? '';
            $settings = $def['settings'] ?? [];
            $condition = $def['condition'] ?? null;

            $provinceMatch = $this->provinceConditionProvince($kind, $condition);
            if ($provinceMatch !== null) {
                $buffer[] = ['province' => $provinceMatch, 'body' => (string) ($settings['body'] ?? '')];

                continue;
            }

            $flush();

            $converted = $this->convertOne($kind, $settings, $condition);
            if ($converted !== null) {
                $out[] = $converted;
            }
        }

        $flush();

        return $out;
    }

    /**
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>|null  $condition
     * @return array<string, mixed>|null
     */
    private function convertOne(string $kind, array $settings, ?array $condition): ?array
    {
        $block = match ($kind) {
            'title_page' => $this->block(BlockKinds::TITLE_PAGE, [
                'title' => (string) ($settings['title'] ?? ''),
                'subtitle' => (string) ($settings['subtitle'] ?? ''),
                'body' => (string) ($settings['body'] ?? ''),
                'asset_id' => $this->intOrNull($settings['asset_id'] ?? null),
            ]),
            'heading' => $this->block(BlockKinds::HEADING, [
                'text' => (string) ($settings['text'] ?? ''),
                'level' => (int) ($settings['level'] ?? 1),
            ]),
            'text' => $this->block(BlockKinds::TEXT, [
                'body' => (string) ($settings['body'] ?? ''),
            ]),
            'image' => $this->block(BlockKinds::IMAGE, [
                'asset_id' => $this->intOrNull($settings['asset_id'] ?? null),
                'width' => (int) ($settings['width'] ?? 60),
                'width_unit' => 'percent',
                'align' => (string) ($settings['align'] ?? 'center'),
                'caption' => (string) ($settings['caption'] ?? ''),
                'border' => false,
            ]),
            'page_break' => $this->block(BlockKinds::PAGE_BREAK, []),
            'yes_list_paragraph' => $this->block(BlockKinds::YES_NO_SENTENCES, [
                'scope' => 'field',
                'field' => (string) ($settings['field'] ?? ''),
                'intro' => (string) ($settings['intro'] ?? ''),
                'outro' => (string) ($settings['outro'] ?? ''),
                'as_list' => (bool) ($settings['as_list'] ?? false),
                // Preserve legacy behaviour: items answered Yes with no
                // sentence currently print their label.
                'empty_sentence' => 'label',
            ]),
            'firearms_table' => $this->block(BlockKinds::TABLE, [
                'title' => (string) ($settings['title'] ?? ''),
                'source' => 'firearms',
                'columns' => array_values(array_map(
                    fn ($col, $label) => ['key' => $col, 'label' => $label],
                    array_keys(QuestionSchema::FIREARM_COLUMNS),
                    array_values(QuestionSchema::FIREARM_COLUMNS),
                )),
                'rows' => [],
                'show_header' => true,
            ]),
            'document_insert' => $this->block(BlockKinds::DOCUMENT_INSERT, [
                'kinds' => array_values(is_array($settings['kinds'] ?? null) ? $settings['kinds'] : []),
                'divider_title' => (string) ($settings['divider_title'] ?? ''),
            ]),
            default => null,
        };

        if ($block === null) {
            return null;
        }

        if ($condition) {
            $block['condition'] = $condition;
        }

        return TemplateSchema::normalizeBlock($block);
    }

    /**
     * If this is a `text` block gated on `answers.province equals X`, return
     * X. Otherwise null. These are the blocks that collapse into a single
     * `crime_context`.
     */
    private function provinceConditionProvince(string $kind, mixed $condition): ?string
    {
        if ($kind !== 'text' || ! is_array($condition)) {
            return null;
        }
        if (($condition['field'] ?? null) !== 'answers.province') {
            return null;
        }
        if (($condition['operator'] ?? null) !== 'equals') {
            return null;
        }
        $value = trim((string) ($condition['value'] ?? ''));

        return $value === '' ? null : $value;
    }

    private function intOrNull(mixed $value): ?int
    {
        $int = (int) $value;

        return $int > 0 ? $int : null;
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function block(string $kind, array $settings): array
    {
        return [
            'id' => (string) Str::uuid(),
            'kind' => $kind,
            'settings' => $settings,
            'condition' => null,
        ];
    }

    private function uniqueSlug(ApplicationType $type): string
    {
        $base = Str::slug($type->slug ?: $type->name);
        $slug = $base;
        $i = 2;
        while (MotivationTemplate::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function findConvertedTemplate(ApplicationType $type): ?MotivationTemplate
    {
        // We treat the first default template attached to this type as the
        // converted one, so re-running the conversion is a no-op.
        $pivot = DB::table('application_type_template')
            ->where('application_type_id', $type->id)
            ->where('is_default', true)
            ->orderBy('sort')
            ->first();

        return $pivot ? MotivationTemplate::find($pivot->template_id) : null;
    }
}
