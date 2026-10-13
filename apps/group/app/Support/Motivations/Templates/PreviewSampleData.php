<?php

namespace App\Support\Motivations\Templates;

use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationRequest;
use App\Models\MotivationTemplate;
use App\Models\User;
use App\Support\Motivations\QuestionSchema;

/**
 * Produces an in-memory MotivationRequest (not persisted) that lets the
 * builder preview a template against believable data without touching a
 * real member. The applicant is Jane Example from Pretoria with sensible
 * yes/no answers filled in.
 */
class PreviewSampleData
{
    public static function requestFor(MotivationTemplate $template): MotivationRequest
    {
        $type = $template->applicationTypes()->first()
            ?? ApplicationType::where('category', $template->category)->first()
            ?? ApplicationType::first();

        $request = new MotivationRequest;
        $request->id = 0;
        $request->status = MotivationRequest::STATUS_SUBMITTED;
        $request->application_type_id = $type?->id;
        $request->template_version_id = $template->published_version_id;

        // Fake profile — never persisted.
        $profile = new ClientProfile([
            'full_names' => 'Jane',
            'surname' => 'Example',
            'id_number' => '9001011234080',
            'physical_address' => '1 Sample Street, Pretoria, 0083',
            'occupation' => 'IT Consultant',
            'cell' => '082 123 4567',
            'email' => 'jane.example@sample.co.za',
            'self_employed' => false,
            'employer' => 'Example Corp',
            'business_address' => '42 Business Park, Pretoria',
            'association_name' => '',
            'association_number' => '',
        ]);

        $user = new User(['name' => 'Jane Example', 'email' => $profile->email, 'role' => User::ROLE_CLIENT]);
        $user->setRelation('clientProfile', $profile);

        $request->setRelation('user', $user);
        if ($type) {
            $request->setRelation('applicationType', $type);
            $request->answers = self::sampleAnswers($type);
        } else {
            $request->answers = [];
        }
        $request->setRelation('documents', collect());

        return $request;
    }

    /**
     * @return array<string, mixed>
     */
    private static function sampleAnswers(ApplicationType $type): array
    {
        $answers = [];
        foreach (QuestionSchema::fields($type->sections()) as $field) {
            $name = $field['name'];
            $answers[$name] = match ($field['type'] ?? 'text') {
                'checkbox' => true,
                'yes_no_list' => collect($field['items'] ?? [])->mapWithKeys(fn ($i) => [$i['name'] => 'yes'])->all(),
                'firearms_table' => [[
                    'type' => 'Pistol',
                    'calibre' => '9mm Parabellum',
                    'serial' => 'EX-123456',
                    'action' => 'Semi-automatic',
                    'make' => 'Canik',
                    'model' => 'MC9 L',
                ]],
                'select', 'radio' => (string) (($field['options'] ?? [])[0] ?? 'Yes'),
                'date' => now()->format('Y-m-d'),
                'number' => 1,
                default => self::sampleScalar($name),
            };
        }

        return $answers;
    }

    private static function sampleScalar(string $field): string
    {
        return match ($field) {
            'province' => 'Gauteng',
            'policing_precinct', 'closest_police_station' => 'Pretoria Central',
            'section_of_act' => '13',
            'provinces_of_shooting' => 'Gauteng',
            default => 'Sample '.str_replace('_', ' ', $field),
        };
    }
}
