<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Application types are seeded once and then edited in the builder, so the
 * seeder change alone won't reach existing types. Only fields an admin
 * hasn't already given a condition are touched.
 */
return new class extends Migration
{
    private const FIELD = 'shotgun_action_notes';

    private const CONDITION = ['field' => 'firearms.*.type', 'operator' => 'equals', 'value' => 'Shotgun'];

    public function up(): void
    {
        $this->rewrite(function (array $field): array {
            if (empty($field['condition'])) {
                $field['condition'] = self::CONDITION;
            }

            return $field;
        });
    }

    public function down(): void
    {
        $this->rewrite(function (array $field): array {
            if (($field['condition'] ?? null) === self::CONDITION) {
                unset($field['condition']);
            }

            return $field;
        });
    }

    private function rewrite(callable $change): void
    {
        foreach (DB::table('application_types')->get(['id', 'questions']) as $type) {
            $sections = json_decode((string) $type->questions, true);
            if (! is_array($sections)) {
                continue;
            }

            $touched = false;
            foreach ($sections as $s => $section) {
                foreach ($section['fields'] ?? [] as $f => $field) {
                    if (($field['name'] ?? null) === self::FIELD) {
                        $sections[$s]['fields'][$f] = $change($field);
                        $touched = true;
                    }
                }
            }

            if ($touched) {
                DB::table('application_types')->where('id', $type->id)->update(['questions' => json_encode($sections)]);
            }
        }
    }
};
