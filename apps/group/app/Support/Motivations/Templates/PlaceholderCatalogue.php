<?php

namespace App\Support\Motivations\Templates;

use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationTemplate;
use App\Support\Motivations\DocumentKinds;
use App\Support\Motivations\Placeholders;
use App\Support\Motivations\QuestionSchema;

/**
 * Builds the placeholder picker that the template builder shows. Union of:
 *
 *   - every assigned application type's questions and requirements, so a
 *     template bound to Self Defence can insert the exact answers.* paths,
 *   - firearm row columns and the firearm count,
 *   - documents.<kind>.{reference,count,issued_on,expires_on} for every
 *     document kind,
 *   - the applicant profile fields,
 *   - a few static date tokens.
 */
class PlaceholderCatalogue
{
    /**
     * @return array<string, array<string, string>> group label => path => label
     */
    public static function forTemplate(MotivationTemplate $template): array
    {
        $types = $template->applicationTypes()->get();
        if ($types->isEmpty()) {
            // Fall back to the full union across every type in the same
            // category so new templates still have fields to pick from.
            $types = ApplicationType::where('category', $template->category)->get();
        }

        return self::build($types->all());
    }

    /**
     * @param  array<int, ApplicationType>  $types
     * @return array<string, array<string, string>>
     */
    public static function build(array $types): array
    {
        $profile = [];
        foreach (ClientProfile::FIELDS as $key => $label) {
            $profile['profile.'.$key] = $label;
        }
        $profile['profile.full_name'] = 'Full name (names + surname)';
        $profile['profile.age'] = 'Age (from ID number)';
        $profile['profile.id_number_spaced'] = 'ID number (spaced YYMMDD SSSS CC A)';

        $firearm = [];
        foreach (QuestionSchema::FIREARM_COLUMNS as $col => $label) {
            $firearm['firearm.'.$col] = 'Current firearm: '.$label;
            $firearm['firearm.2.'.$col] = 'Second firearm (if any): '.$label;
        }
        $firearm['firearm_count'] = 'Number of firearms';
        $firearm['current_firearm_index'] = 'Position of the current firearm';

        $answers = [];
        $usedFields = [];
        foreach ($types as $type) {
            foreach (QuestionSchema::fields($type->sections()) as $field) {
                $kind = $field['type'] ?? 'text';
                if ($kind === 'firearms_table' || $kind === 'yes_no_list') {
                    continue;
                }
                $path = 'answers.'.$field['name'];
                if (in_array($path, $usedFields, true)) {
                    continue;
                }
                $usedFields[] = $path;
                $answers[$path] = $field['label'];
            }
        }

        $documents = [];
        $seenDocKinds = [];
        foreach ($types as $type) {
            foreach ($type->requirements() as $req) {
                $kind = $req['kind'] ?? '';
                if ($kind === '' || in_array($kind, $seenDocKinds, true)) {
                    continue;
                }
                $seenDocKinds[] = $kind;
                $label = $req['label'] ?: DocumentKinds::label($kind);
                $documents['documents.'.$kind.'.count'] = $label.': uploaded count';
                if (! empty($req['has_reference_number'])) {
                    $documents['documents.'.$kind.'.reference'] = $label.': reference number';
                }
                $documents['documents.'.$kind.'.issued_on'] = $label.': issue date';
                $documents['documents.'.$kind.'.expires_on'] = $label.': expiry date';
            }
        }

        return array_filter([
            'Profile' => $profile,
            'Firearm' => $firearm,
            'Answers' => $answers,
            'Documents' => $documents,
            'Request' => [
                'request.reference' => 'Request reference',
                'type.name' => 'Application type name',
            ],
            'Dates' => [
                'today' => "Today's date",
                'today_short' => 'Today (YYYY-MM-DD)',
                'today_long' => 'Today, long form',
                'year' => 'Current year',
            ],
        ]);
    }

    /**
     * Condition editor fields: union of all type conditionFields plus
     * firearm_count, documents.<kind>.count and the profile fields.
     *
     * @return array<string, string>
     */
    public static function conditionFieldsFor(MotivationTemplate $template): array
    {
        $types = $template->applicationTypes()->get();
        if ($types->isEmpty()) {
            $types = ApplicationType::where('category', $template->category)->get();
        }

        $out = [
            'firearm_count' => 'Number of firearms',
            'current_firearm_index' => 'Position of the current firearm',
        ];

        foreach ($types as $type) {
            foreach (Placeholders::conditionFields($type->sections()) as $path => $label) {
                $out[$path] = $label;
            }
        }

        $seen = [];
        foreach ($types as $type) {
            foreach ($type->requirements() as $req) {
                $kind = $req['kind'] ?? '';
                if ($kind === '' || in_array($kind, $seen, true)) {
                    continue;
                }
                $seen[] = $kind;
                $out['documents.'.$kind.'.count'] = DocumentKinds::label($kind).': uploaded count';
            }
        }

        return $out;
    }
}
