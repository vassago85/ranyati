<?php

namespace App\Support\Motivations\Templates;

use App\Models\ApplicationType;
use App\Models\MotivationTemplate;
use App\Models\MotivationTemplateVersion;
use App\Support\Motivations\QuestionSchema;
use Illuminate\Support\Facades\DB;

/**
 * The one-page Section 13 firearm motivation, distilled from Ranyati's
 * live 2026 Canik METE MC9L (Botha) letter. Same facts and legal basis,
 * none of the 20-page SAPS-stats annexure.
 *
 * Placeholders fill name, spaced ID, address, work, age, firearm and
 * province. An empty Image block is left for the pistol photo — drop the
 * asset in the builder when you have it.
 */
class OnePageSelfDefenceTemplate
{
    public const SLUG = 'self-defence-one-page';

    public const NAME = 'Self Defence — one page';

    public static function seed(?int $actorId = null): MotivationTemplate
    {
        return DB::transaction(function () use ($actorId) {
            $template = MotivationTemplate::updateOrCreate(
                ['slug' => self::SLUG],
                [
                    'name' => self::NAME,
                    'category' => 'self_defence',
                    'description' => 'One-page Section 13 motivation from the 2026 Canik METE MC9L letter. Name, ID, work and firearm come from the request. Add the pistol photo in the Image block.',
                    'updated_by' => $actorId,
                ],
            );

            if (! $template->created_by) {
                $template->update(['created_by' => $actorId]);
            }

            $blocks = self::blocks();
            $settings = self::settings();

            if (! self::publishedMatches($template, $blocks, $settings)) {
                $manager = new TemplateVersionManager;
                $manager->ensureDraft($template, $actorId);
                $manager->saveDraft($template, $blocks, $settings, $actorId);
                $manager->publish($template, $actorId);
            }

            self::attachAsDefault($template);

            return $template->fresh(['publishedVersion', 'applicationTypes']);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public static function settings(): array
    {
        return [
            'page' => [
                'size' => 'A4',
                'orientation' => 'portrait',
                'margin_top' => 16,
                'margin_right' => 14,
                'margin_bottom' => 14,
                'margin_left' => 14,
                'font_family' => 'DejaVu Sans',
                'font_size' => 9,
            ],
            'header' => [
                'enabled' => true,
                'asset_id' => null,
                'text' => '{{profile.full_name}} ID: {{profile.id_number_spaced}} Initial_________',
                'different_first_page' => false,
            ],
            'footer' => [
                'enabled' => false,
                'text' => '',
                'show_page_numbers' => false,
                'different_first_page' => false,
            ],
            'letter_mode' => 'per_firearm',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function blocks(): array
    {
        return [
            [
                'kind' => BlockKinds::HEADING,
                'settings' => [
                    'level' => 2,
                    'text' => 'MOTIVATION FOR A {{firearm.calibre}} {{firearm.type}}',
                ],
            ],
            [
                'kind' => BlockKinds::HEADING,
                'settings' => [
                    'level' => 3,
                    'text' => '{{firearm.make}} {{firearm.model}}',
                ],
            ],
            [
                'kind' => BlockKinds::TEXT,
                'settings' => [
                    'body' => 'SERIAL NO: {{firearm.serial}}',
                ],
            ],
            [
                'kind' => BlockKinds::TEXT,
                'settings' => [
                    'body' => "I, the undersigned,\n\n{{profile.full_name}}\nID: {{profile.id_number_spaced}}\n\nmotivate my application for licensing of the above-mentioned firearm, as follows:",
                ],
            ],
            [
                'kind' => BlockKinds::TEXT,
                'settings' => [
                    'body' => 'I am a {{profile.age}}-year-old South African. I am currently residing at {{profile.physical_address}}.',
                ],
            ],
            [
                'kind' => BlockKinds::TEXT,
                'settings' => [
                    'body' => 'I am self-employed at {{profile.employer}} as a {{profile.occupation}}.',
                ],
                'condition' => ['field' => 'profile.self_employed', 'operator' => 'equals', 'value' => 'yes'],
            ],
            [
                'kind' => BlockKinds::TEXT,
                'settings' => [
                    'body' => 'I am employed at {{profile.employer}} as a {{profile.occupation}}.',
                ],
                'condition' => ['field' => 'profile.self_employed', 'operator' => 'not_equals', 'value' => 'yes'],
            ],
            [
                'kind' => BlockKinds::TEXT,
                'settings' => [
                    'body' => 'The basis for my motivation for licensing is a licence to possess a firearm for self-defence in terms of section 13 of the Firearms Control Act 60 of 2000.',
                ],
            ],
            [
                'kind' => BlockKinds::TEXT,
                'settings' => [
                    'body' => 'I have no criminal record against my name and have not been found guilty of any offence that can declare me unfit to possess a firearm as stipulated in Section 9 of the Firearms Control Act. I have kept strictly to the stipulations of the previous Act, and have never been charged or convicted of a firearm- or violence-related crime. I regard myself to be a responsible person. This application is in line with the constitutionally entrenched right to Freedom and Security of the person in terms of Section 12 of the Constitution.',
                ],
            ],
            [
                'kind' => BlockKinds::HEADING,
                'settings' => [
                    'level' => 3,
                    'text' => 'Introduction',
                ],
            ],
            [
                'kind' => BlockKinds::TEXT,
                'settings' => [
                    'body' => "I have successfully completed my proficiency training as required by law and the required training unit standards. I have demonstrated the proficiency, competency and understanding to safely possess, handle and use the following:\n\n".
                        "• Demonstrate Knowledge of the Firearms Control Act 60 of 2000 — Unit Standard 117705\n".
                        "• Use of a Handgun — Unit Standard 119649\n".
                        '• SAPS Competency Certificate',
                ],
            ],
            [
                'kind' => BlockKinds::HEADING,
                'settings' => [
                    'level' => 3,
                    'text' => 'Purpose of the firearm',
                ],
            ],
            [
                'kind' => BlockKinds::TEXT,
                'settings' => [
                    'body' => "Section 13(2) of the FCA states that the Registrar may issue a licence to any natural person who (a) needs a firearm for self-defence; and (b) cannot reasonably satisfy that need by means other than the possession of a firearm.\n\n".
                        'I work at {{profile.employer}} as a {{profile.occupation}}. {{answers.employer_specialises}}',
                ],
            ],
            [
                'kind' => BlockKinds::YES_NO_SENTENCES,
                'settings' => [
                    'scope' => 'field',
                    'field' => 'employment_profile',
                    'intro' => 'In the course of my work:',
                    'outro' => 'I am often alone in my vehicle after dark. Managing equipment, documents and cash raises my risk of becoming a statistic. I rely on my own transport and travel through {{answers.province}} in and around {{answers.policing_precinct}}.',
                    'as_list' => false,
                    'empty_sentence' => 'skip',
                ],
            ],
            [
                'kind' => BlockKinds::YES_NO_SENTENCES,
                'settings' => [
                    'scope' => 'field',
                    'field' => 'other_profile',
                    'intro' => 'In my personal life:',
                    'outro' => 'There is no other reasonable means to protect the lives of my family and those travelling with me other than by a licensed firearm.',
                    'as_list' => false,
                    'empty_sentence' => 'skip',
                ],
            ],
            [
                'kind' => BlockKinds::CRIME_CONTEXT,
                'settings' => [
                    'province_field' => 'answers.province',
                    'bodies' => self::provinceBodies(),
                    'fallback_body' => 'All citizens of South Africa are affected by crime. I live and work in {{answers.province}} around {{answers.policing_precinct}}, and this exposure is a material part of my need for a licensed self-defence firearm.',
                ],
            ],
            [
                'kind' => BlockKinds::HEADING,
                'settings' => [
                    'level' => 3,
                    'text' => 'Alternative means of protection',
                ],
            ],
            [
                'kind' => BlockKinds::YES_NO_SENTENCES,
                'settings' => [
                    'scope' => 'field',
                    'field' => 'home_security',
                    'intro' => 'My home is already protected as follows:',
                    'outro' => 'It is not cost-effective to hire a security guard for every movement. Not being able to travel would mean I cannot work or support my family. I cannot reasonably satisfy the need for self-defence other than by a licensed firearm — to protect my life, my family, people travelling with me, my property, and cash and belongings I carry.',
                    'as_list' => false,
                    'empty_sentence' => 'label',
                ],
            ],
            [
                'kind' => BlockKinds::HEADING,
                'settings' => [
                    'level' => 3,
                    'text' => 'The firearm and safety rules',
                ],
            ],
            [
                'kind' => BlockKinds::TEXT,
                'settings' => [
                    'body' => 'The firearm applied for is a {{firearm.calibre}} {{firearm.make}} {{firearm.model}} ({{firearm.type}}), serial {{firearm.serial}}. Ammunition in this calibre is widely available. The pistol is accurate, comfortable to fire, and suited to concealed carry while travelling and to home defence. It fits my posture and intended use.',
                ],
            ],
            [
                'kind' => BlockKinds::IMAGE,
                'settings' => [
                    'asset_id' => null,
                    'width' => 40,
                    'width_unit' => 'percent',
                    'align' => 'center',
                    'caption' => 'Example of a {{firearm.make}} {{firearm.model}} — not necessarily the actual firearm applied for.',
                    'border' => false,
                ],
            ],
            [
                'kind' => BlockKinds::TEXT,
                'settings' => [
                    'body' => 'I undertake the three fundamental rules of safe gun handling: ALWAYS keep the gun pointed in a safe direction; ALWAYS keep my finger off the trigger until ready to shoot; ALWAYS keep the gun unloaded until ready to use.',
                ],
            ],
            [
                'kind' => BlockKinds::TEXT,
                'settings' => [
                    'body' => 'I am older than 21, a fit and proper person of stable mental condition, not inclined to violence, and I do not depend on intoxicating or narcotic substances. I have not been convicted of any offence that would render me unfit under Section 9(2), and I have completed the prescribed knowledge and practical tests. I have complied with Section 13 and disclose the purpose as self-defence.',
                ],
            ],
            [
                'kind' => BlockKinds::SIGNATURE,
                'settings' => [
                    'name' => '{{profile.full_name}}',
                    'id_number' => '{{profile.id_number_spaced}}',
                    'date' => '{{today}}',
                    'place' => '',
                    'show_signed_at_line' => true,
                ],
            ],
        ];
    }

    /**
     * One short paragraph per province — not the 20-page stats dump.
     *
     * @return array<string, string>
     */
    public static function provinceBodies(): array
    {
        $bodies = [];
        foreach (QuestionSchema::PROVINCES as $province) {
            $bodies[$province] = $province.' remains a high-risk environment for violent and property crime. I live and work in this province and travel through {{answers.policing_precinct}} and surrounding areas, including after dark. This exposure is a material part of my need for a licensed self-defence firearm.';
        }

        $bodies['Gauteng'] = 'Gauteng records the highest carjacking and trio-crime figures in the country. I live and work here and travel through {{answers.policing_precinct}} and other districts, often after dark. This exposure is a material part of my need for a licensed self-defence firearm.';

        return $bodies;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, mixed>  $settings
     */
    private static function publishedMatches(MotivationTemplate $template, array $blocks, array $settings): bool
    {
        $published = $template->publishedVersion;
        if (! $published instanceof MotivationTemplateVersion) {
            return false;
        }

        $wantBlocks = self::fingerprintBlocks(TemplateSchema::normalizeBlocks($blocks));
        $haveBlocks = self::fingerprintBlocks((array) $published->blocks);
        $wantSettings = TemplateSchema::normalizeSettingsJson($settings);
        $haveSettings = TemplateSchema::normalizeSettingsJson((array) $published->settings);

        return $wantBlocks === $haveBlocks && $wantSettings === $haveSettings;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private static function fingerprintBlocks(array $blocks): array
    {
        return array_map(fn (array $block) => [
            'kind' => $block['kind'] ?? null,
            'settings' => $block['settings'] ?? [],
            'condition' => $block['condition'] ?? null,
        ], $blocks);
    }

    private static function attachAsDefault(MotivationTemplate $template): void
    {
        $types = ApplicationType::query()
            ->where('category', 'self_defence')
            ->where('has_motivation', true)
            ->get();

        foreach ($types as $type) {
            foreach ($type->templates as $existing) {
                if ((int) $existing->id !== (int) $template->id && $existing->pivot->is_default) {
                    $type->templates()->updateExistingPivot($existing->id, ['is_default' => false]);
                }
            }

            $type->templates()->syncWithoutDetaching([
                $template->id => ['is_default' => true, 'sort' => 0, 'condition' => null],
            ]);
        }
    }
}
