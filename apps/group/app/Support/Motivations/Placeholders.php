<?php

namespace App\Support\Motivations;

use App\Models\ApplicationType;
use App\Models\ClientProfile;

/**
 * Resolves {{ path }} placeholders in book text against a request's data.
 *
 *   {{profile.full_name}}  {{answers.closest_police_station}}
 *   {{firearm.serial}}     (first firearm)   {{firearm.2.calibre}}
 *   {{today}}  {{request.reference}}  {{type.name}}
 */
class Placeholders
{
    public const PATTERN = '/\{\{\s*([A-Za-z0-9_.]+)\s*\}\}/';

    /**
     * @param  array<string, mixed>  $profile
     * @param  array<string, mixed>  $answers
     * @return array<string, mixed>
     */
    public static function context(array $profile, array $answers, ?ApplicationType $type, ?string $reference = null): array
    {
        $firearms = QuestionSchema::firearms($type?->sections() ?? [], $answers);
        $firearm = ($firearms[1] ?? []) + $firearms;

        return [
            'profile' => $profile,
            'answers' => $answers,
            'firearm' => $firearm,
            'firearm_count' => count($firearms),
            'today' => now()->format('j F Y'),
            'request' => ['reference' => $reference],
            'type' => ['name' => $type?->name],
        ];
    }

    /**
     * Replace every placeholder. $onMissing receives the path of a blank value
     * and returns the text to put in its place.
     *
     * @param  array<string, mixed>  $context
     * @param  array<int, string>  $missing  Collects paths that resolved to blank.
     */
    public static function replace(string $text, array $context, callable $onMissing, array &$missing = []): string
    {
        return preg_replace_callback(self::PATTERN, function ($m) use ($context, $onMissing, &$missing) {
            $value = self::format(data_get($context, $m[1]));
            if ($value === '') {
                $missing[] = $m[1];

                return $onMissing($m[1]);
            }

            return $value;
        }, $text);
    }

    public static function format(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            $flat = [];
            array_walk_recursive($value, function ($v) use (&$flat) {
                if (is_scalar($v) && trim((string) $v) !== '') {
                    $flat[] = trim((string) $v);
                }
            });

            return implode(', ', $flat);
        }

        return trim((string) $value);
    }

    /**
     * Placeholders offered in the builder's picker, grouped for display.
     *
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<string, array<string, string>> group => [path => label]
     */
    public static function catalogue(array $sections): array
    {
        $profile = [];
        foreach (ClientProfile::FIELDS as $key => $label) {
            $profile['profile.'.$key] = $label;
        }
        $profile['profile.full_name'] = 'Full name (names + surname)';
        $profile['profile.age'] = 'Age (from ID number)';
        $profile['profile.id_number_spaced'] = 'ID number (spaced YYMMDD SSSS CC A)';

        $answers = [];
        $firearm = [];
        foreach (QuestionSchema::fields($sections) as $field) {
            $type = $field['type'] ?? 'text';
            if ($type === 'firearms_table') {
                foreach (QuestionSchema::FIREARM_COLUMNS as $col => $label) {
                    $firearm['firearm.'.$col] = 'First firearm: '.$label;
                }
                foreach (QuestionSchema::FIREARM_COLUMNS as $col => $label) {
                    $firearm['firearm.2.'.$col] = 'Second firearm: '.$label;
                }
                $firearm['firearm_count'] = 'Number of firearms';

                continue;
            }
            if ($type === 'yes_no_list') {
                continue;
            }
            $answers['answers.'.$field['name']] = $field['label'];
        }

        return array_filter([
            'Profile' => $profile,
            'Answers' => $answers,
            'Firearm' => $firearm,
            'General' => [
                'today' => "Today's date",
                'request.reference' => 'Request reference',
                'type.name' => 'Application type name',
            ],
        ]);
    }

    /**
     * Context paths offered in condition editors.
     *
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<string, string> path => label
     */
    public static function conditionFields(array $sections): array
    {
        $out = [];
        foreach (QuestionSchema::fields($sections) as $field) {
            $type = $field['type'] ?? 'text';
            if ($type === 'firearms_table') {
                $out['firearm_count'] = 'Number of firearms';
                $out['firearms.*.type'] = 'Any firearm type';
                $out['firearms.*.action'] = 'Any firearm action';

                continue;
            }
            if ($type === 'yes_no_list') {
                foreach ($field['items'] ?? [] as $item) {
                    $out['answers.'.$field['name'].'.'.$item['name']] = $field['label'].': '.$item['label'];
                }

                continue;
            }
            $out['answers.'.$field['name']] = $field['label'];
        }

        foreach (['self_employed', 'association_name', 'association_number', 'occupation'] as $key) {
            $out['profile.'.$key] = 'Profile: '.ClientProfile::FIELDS[$key];
        }

        return $out;
    }
}
