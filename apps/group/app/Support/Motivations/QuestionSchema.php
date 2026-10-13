<?php

namespace App\Support\Motivations;

use App\Models\ApplicationType;
use App\Support\FirearmTypes;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Field definitions for application-type questionnaires.
 *
 * Questions are stored on the application type as a list of sections:
 *
 *   [{ key, title, intro?, condition?, fields: [
 *       { name, label, type, required?, options?, hint?, condition?,
 *         items?: [{ name, label, sentence? }] }   // yes_no_list only
 *   ]}]
 *
 * Answers are keyed by field name. A yes_no_list answer is a map of item
 * name to "yes"/"no"; a firearms_table answer is a list of rows keyed by
 * FIREARM_COLUMNS.
 */
class QuestionSchema
{
    public const TYPES = [
        'text' => 'Short text',
        'textarea' => 'Long text',
        'email' => 'Email',
        'tel' => 'Phone',
        'date' => 'Date',
        'number' => 'Number',
        'select' => 'Dropdown',
        'radio' => 'Single choice',
        'checkbox' => 'Tick box',
        'yes_no_list' => 'Yes/No list',
        'firearms_table' => 'Firearms table',
    ];

    /** South African provinces — used by province dropdowns and the admin
     *  builder's placeholder picker. The strings here are the canonical
     *  values that condition blocks match against. */
    public const PROVINCES = [
        'Gauteng',
        'Western Cape',
        'KwaZulu-Natal',
        'Eastern Cape',
        'Free State',
        'Mpumalanga',
        'Limpopo',
        'North West',
        'Northern Cape',
    ];

    public const FIREARM_COLUMNS = [
        'type' => 'Type of firearm',
        'calibre' => 'Calibre',
        'serial' => 'Serial number',
        'action' => 'Action',
        'make' => 'Make',
        'model' => 'Model',
    ];

    public const MAX_FIREARM_ROWS = 20;

    /**
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<int, array<string, mixed>> Fields with their section's condition attached.
     */
    public static function fields(array $sections): array
    {
        $out = [];
        foreach ($sections as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                $field['section_condition'] = $section['condition'] ?? null;
                $out[] = $field;
            }
        }

        return $out;
    }

    /**
     * Firearm type labels offered in the firearms table dropdown.
     *
     * @return array<int, string>
     */
    public static function firearmTypeOptions(): array
    {
        return array_values(array_map(fn ($t) => $t['label'], FirearmTypes::all()));
    }

    /**
     * Rows of the first firearms_table field, re-indexed from 1.
     *
     * @param  array<int, array<string, mixed>>  $sections
     * @param  array<string, mixed>  $answers
     * @return array<int, array<string, string>>
     */
    public static function firearms(array $sections, array $answers): array
    {
        foreach (self::fields($sections) as $field) {
            if (($field['type'] ?? null) === 'firearms_table') {
                $rows = array_values(array_filter($answers[$field['name']] ?? [], 'is_array'));

                return $rows === [] ? [] : array_combine(range(1, count($rows)), $rows);
            }
        }

        return [];
    }

    /**
     * The context every condition and placeholder resolves against.
     *
     * @param  array<string, mixed>  $profile
     * @param  array<string, mixed>  $answers
     * @return array<string, mixed>
     */
    public static function context(array $profile, array $answers, ?ApplicationType $type): array
    {
        $sections = $type?->sections() ?? [];
        $firearms = self::firearms($sections, $answers);

        return [
            'profile' => $profile,
            'answers' => $answers,
            'firearms' => array_values($firearms),
            'firearm_count' => count($firearms),
        ];
    }

    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, mixed>  $context
     */
    public static function isVisible(array $field, array $context): bool
    {
        return ConditionEvaluator::passes($field['section_condition'] ?? null, $context)
            && ConditionEvaluator::passes($field['condition'] ?? null, $context);
    }

    /**
     * Validation rules for a draft save. Nothing is required here — the
     * completeness check happens on submit so members can save as they go.
     *
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<string, mixed>
     */
    public static function rules(array $sections): array
    {
        $rules = ['answers' => ['nullable', 'array']];

        foreach (self::fields($sections) as $field) {
            $key = 'answers.'.$field['name'];
            $options = $field['options'] ?? [];

            switch ($field['type'] ?? 'text') {
                case 'textarea':
                    $rules[$key] = ['nullable', 'string', 'max:5000'];
                    break;
                case 'email':
                    $rules[$key] = ['nullable', 'email', 'max:255'];
                    break;
                case 'date':
                    $rules[$key] = ['nullable', 'date'];
                    break;
                case 'number':
                    $rules[$key] = ['nullable', 'numeric'];
                    break;
                case 'select':
                case 'radio':
                    $rules[$key] = ['nullable', 'string', Rule::in($options)];
                    break;
                case 'checkbox':
                    $rules[$key] = ['nullable'];
                    break;
                case 'yes_no_list':
                    $rules[$key] = ['nullable', 'array'];
                    $rules[$key.'.*'] = ['nullable', 'in:yes,no'];
                    break;
                case 'firearms_table':
                    $rules[$key] = ['nullable', 'array', 'max:'.self::MAX_FIREARM_ROWS];
                    foreach (array_keys(self::FIREARM_COLUMNS) as $col) {
                        $rules[$key.'.*.'.$col] = ['nullable', 'string', 'max:255'];
                    }
                    break;
                default:
                    $rules[$key] = ['nullable', 'string', 'max:255'];
            }
        }

        return $rules;
    }

    /**
     * Keep only known fields and coerce each to its stored shape.
     *
     * @param  array<int, array<string, mixed>>  $sections
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalizeAnswers(array $sections, array $input): array
    {
        $answers = [];

        foreach (self::fields($sections) as $field) {
            $name = $field['name'];
            $value = $input[$name] ?? null;

            switch ($field['type'] ?? 'text') {
                case 'checkbox':
                    $answers[$name] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'yes_no_list':
                    $map = [];
                    foreach ($field['items'] ?? [] as $item) {
                        $v = is_array($value) ? ($value[$item['name']] ?? null) : null;
                        $map[$item['name']] = in_array($v, ['yes', 'no'], true) ? $v : null;
                    }
                    $answers[$name] = $map;
                    break;
                case 'firearms_table':
                    $rows = [];
                    foreach (is_array($value) ? $value : [] as $row) {
                        if (! is_array($row)) {
                            continue;
                        }
                        $clean = [];
                        foreach (array_keys(self::FIREARM_COLUMNS) as $col) {
                            $clean[$col] = trim((string) ($row[$col] ?? ''));
                        }
                        if (implode('', $clean) !== '') {
                            $rows[] = $clean;
                        }
                    }
                    $answers[$name] = array_slice($rows, 0, self::MAX_FIREARM_ROWS);
                    break;
                default:
                    $answers[$name] = is_scalar($value) ? trim((string) $value) : null;
                    if ($answers[$name] === '') {
                        $answers[$name] = null;
                    }
            }
        }

        return $answers;
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     * @param  array<string, mixed>  $answers
     * @param  array<string, mixed>  $context
     * @return array<int, string> Labels of visible required fields that are blank, plus firearm rows without a serial.
     */
    public static function missingRequired(array $sections, array $answers, array $context): array
    {
        $missing = [];

        foreach (self::fields($sections) as $field) {
            if (! self::isVisible($field, $context)) {
                continue;
            }

            $value = $answers[$field['name']] ?? null;

            if (($field['type'] ?? null) === 'firearms_table' && is_array($value)) {
                foreach (array_values($value) as $i => $row) {
                    if (is_array($row) && blank($row['serial'] ?? null)) {
                        $missing[] = $field['label'].': serial number for firearm '.($i + 1);
                    }
                }
            }

            if (! ($field['required'] ?? false)) {
                continue;
            }

            $blank = match ($field['type'] ?? 'text') {
                'checkbox' => $value !== true,
                'yes_no_list' => ! is_array($value) || array_filter($value) === [],
                'firearms_table' => ! is_array($value) || $value === [],
                default => blank($value),
            };

            if ($blank) {
                $missing[] = $field['label'];
            }
        }

        return $missing;
    }

    /**
     * Read-only display of an answer for admin screens.
     *
     * @param  array<string, mixed>  $field
     */
    public static function display(array $field, mixed $value): string
    {
        return match ($field['type'] ?? 'text') {
            'checkbox' => $value ? 'Yes' : 'No',
            'yes_no_list' => collect($field['items'] ?? [])
                ->filter(fn ($item) => is_array($value) && ($value[$item['name']] ?? null) === 'yes')
                ->pluck('label')->implode('; ') ?: '—',
            'firearms_table' => collect(is_array($value) ? $value : [])
                ->map(fn ($r) => trim(implode(' ', array_filter([$r['type'] ?? '', $r['make'] ?? '', $r['model'] ?? '', $r['calibre'] ?? '', ($r['serial'] ?? '') ? 'S/N '.$r['serial'] : '']))))
                ->implode("\n") ?: '—',
            default => filled($value) ? (string) $value : '—',
        };
    }

    /**
     * Sanitise the sections posted by the admin builder.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function normalizeSections(mixed $sections): array
    {
        $out = [];
        $usedNames = [];

        foreach (is_array($sections) ? $sections : [] as $i => $section) {
            if (! is_array($section)) {
                continue;
            }

            $title = trim((string) ($section['title'] ?? ''));
            $clean = [
                'key' => self::slug($section['key'] ?? '', $title ?: 'section_'.($i + 1)),
                'title' => mb_substr($title ?: 'Section '.($i + 1), 0, 200),
                'intro' => mb_substr(trim((string) ($section['intro'] ?? '')), 0, 2000) ?: null,
                'condition' => ConditionEvaluator::normalize($section['condition'] ?? null),
                'fields' => [],
            ];

            foreach (is_array($section['fields'] ?? null) ? $section['fields'] : [] as $field) {
                if (! is_array($field)) {
                    continue;
                }

                $label = trim((string) ($field['label'] ?? ''));
                if ($label === '') {
                    continue;
                }

                $type = array_key_exists($field['type'] ?? '', self::TYPES) ? $field['type'] : 'text';
                $name = self::slug($field['name'] ?? '', $label);
                $base = $name;
                $n = 2;
                while (in_array($name, $usedNames, true)) {
                    $name = $base.'_'.$n++;
                }
                $usedNames[] = $name;

                $f = [
                    'name' => $name,
                    'label' => mb_substr($label, 0, 500),
                    'type' => $type,
                    'required' => filter_var($field['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ];

                $hint = trim((string) ($field['hint'] ?? ''));
                if ($hint !== '') {
                    $f['hint'] = mb_substr($hint, 0, 1000);
                }

                if (in_array($type, ['select', 'radio'], true)) {
                    $options = is_array($field['options'] ?? null)
                        ? $field['options']
                        : preg_split('/\r?\n|,/', (string) ($field['options'] ?? ''));
                    $f['options'] = array_values(array_unique(array_filter(array_map(fn ($o) => mb_substr(trim((string) $o), 0, 200), $options), fn ($o) => $o !== '')));
                }

                if ($type === 'yes_no_list') {
                    $items = [];
                    $itemNames = [];
                    foreach (is_array($field['items'] ?? null) ? $field['items'] : [] as $item) {
                        $itemLabel = trim((string) ($item['label'] ?? ''));
                        if ($itemLabel === '') {
                            continue;
                        }
                        $itemName = self::slug($item['name'] ?? '', $itemLabel);
                        $ib = $itemName;
                        $k = 2;
                        while (in_array($itemName, $itemNames, true)) {
                            $itemName = $ib.'_'.$k++;
                        }
                        $itemNames[] = $itemName;
                        $items[] = [
                            'name' => $itemName,
                            'label' => mb_substr($itemLabel, 0, 500),
                            'sentence' => mb_substr(trim((string) ($item['sentence'] ?? '')), 0, 2000) ?: null,
                        ];
                    }
                    $f['items'] = $items;
                }

                $condition = ConditionEvaluator::normalize($field['condition'] ?? null);
                if ($condition) {
                    $f['condition'] = $condition;
                }

                $clean['fields'][] = $f;
            }

            $out[] = $clean;
        }

        return $out;
    }

    private static function slug(mixed $preferred, string $fallback): string
    {
        $slug = Str::slug((string) $preferred, '_');
        if ($slug === '') {
            $slug = Str::slug(Str::limit($fallback, 40, ''), '_');
        }

        return $slug !== '' ? mb_substr($slug, 0, 60) : 'field_'.Str::random(6);
    }
}
