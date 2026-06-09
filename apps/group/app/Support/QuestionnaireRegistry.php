<?php

namespace App\Support;

/**
 * Definitions for the digital (fill-in) questionnaires.
 *
 * This is the single source of truth for the scaffold. Each questionnaire is
 * described declaratively (sections -> fields) so the fill form, validation
 * rules, and the read-only submission view are all generated from one place.
 *
 * Field types: text, email, tel, date, number, textarea, select, radio, checkbox.
 */
class QuestionnaireRegistry
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            'self-defence-handgun' => [
                'title' => 'Self-Defence Handgun Questionnaire',
                'blurb' => 'Section 13 self-defence handgun licence application.',
                'intro' => 'Complete every field as fully and accurately as possible. The detail you provide forms the backbone of the SAPS-compliant motivation we prepare on your behalf.',
                'sections' => [
                    [
                        'title' => 'Applicant Details',
                        'fields' => [
                            ['name' => 'full_name', 'label' => 'Full name and surname', 'type' => 'text', 'required' => true, 'primary' => 'name'],
                            ['name' => 'id_number', 'label' => 'SA ID number', 'type' => 'text', 'required' => true],
                            ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true, 'primary' => 'email'],
                            ['name' => 'phone', 'label' => 'Contact number', 'type' => 'tel', 'required' => true],
                            ['name' => 'occupation', 'label' => 'Occupation', 'type' => 'text', 'required' => true],
                            ['name' => 'physical_address', 'label' => 'Residential address', 'type' => 'textarea', 'required' => true],
                        ],
                    ],
                    [
                        'title' => 'Firearm Details',
                        'fields' => [
                            ['name' => 'firearm_type', 'label' => 'Firearm type', 'type' => 'select', 'required' => true, 'options' => ['Pistol', 'Revolver']],
                            ['name' => 'make_model', 'label' => 'Make & model (if known)', 'type' => 'text', 'required' => false],
                            ['name' => 'calibre', 'label' => 'Calibre', 'type' => 'text', 'required' => false],
                            ['name' => 'serial_number', 'label' => 'Serial number (if already owned)', 'type' => 'text', 'required' => false],
                        ],
                    ],
                    [
                        'title' => 'Competency',
                        'fields' => [
                            ['name' => 'has_competency', 'label' => 'Do you hold a competency certificate for self-defence?', 'type' => 'radio', 'required' => true, 'options' => ['Yes', 'No', 'In progress']],
                            ['name' => 'competency_number', 'label' => 'Competency certificate number (if held)', 'type' => 'text', 'required' => false],
                            ['name' => 'previously_refused', 'label' => 'Have you ever been refused a firearm licence?', 'type' => 'radio', 'required' => true, 'options' => ['Yes', 'No']],
                        ],
                    ],
                    [
                        'title' => 'Motivation',
                        'fields' => [
                            ['name' => 'threat_assessment', 'label' => 'Describe the specific threat(s) to your personal safety', 'type' => 'textarea', 'required' => true, 'hint' => 'Be specific: incidents, location, work/travel patterns, risk factors.'],
                            ['name' => 'why_self_defence', 'label' => 'Why is a firearm necessary for your self-defence?', 'type' => 'textarea', 'required' => true],
                            ['name' => 'why_handgun', 'label' => 'Why a handgun specifically (vs other measures)?', 'type' => 'textarea', 'required' => true],
                            ['name' => 'carry_storage', 'label' => 'How and where will you carry and store the firearm?', 'type' => 'textarea', 'required' => true],
                        ],
                    ],
                    [
                        'title' => 'Declaration',
                        'fields' => [
                            ['name' => 'declaration', 'label' => 'I declare that the information provided is true and correct to the best of my knowledge.', 'type' => 'checkbox', 'required' => true],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function find(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    public static function exists(string $key): bool
    {
        return array_key_exists($key, self::all());
    }

    /**
     * Flat list of every field across all sections of a questionnaire.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function fields(array $definition): array
    {
        return collect($definition['sections'] ?? [])
            ->flatMap(fn ($section) => $section['fields'] ?? [])
            ->all();
    }
}
