<?php

namespace Database\Seeders;

use App\Models\ApplicationType;
use App\Support\Motivations\BookSchema;
use App\Support\Motivations\DocumentKinds;
use App\Support\Motivations\QuestionSchema;
use App\Support\Motivations\Templates\LegacyBlockConverter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds Ranyati's five printable application types from the 2026
 * questionnaires. Starter book blocks use placeholder text so Riaan can
 * polish the final wording in the builder before go-live.
 */
class MotivationTypesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->types() as $sort => $def) {
            $this->seed($sort, $def);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function types(): array
    {
        return [
            $this->sportShooting(),
            $this->hunting(),
            $this->selfDefence(),
            $this->renewalAllCategories(),
            $this->renewalNoMotivation(),
            $this->competency(),
        ];
    }

    private function seed(int $sort, array $def): void
    {
        DB::transaction(function () use ($sort, $def) {
            $type = ApplicationType::updateOrCreate(
                ['slug' => $def['slug']],
                [
                    'name' => $def['name'],
                    'category' => $def['category'],
                    'description' => $def['description'],
                    'has_motivation' => $def['has_motivation'],
                    'is_active' => true,
                    'sort' => $sort,
                    'questions' => QuestionSchema::normalizeSections($def['sections']),
                    'document_requirements' => BookSchema::normalizeRequirements($def['requirements']),
                ],
            );

            // Keep the legacy blocks around while the new template flow is
            // bedded in — a follow-up migration drops them when the switch
            // is verified in prod.
            $type->blocks()->delete();
            foreach (BookSchema::normalizeBlocks($def['blocks']) as $i => $block) {
                $type->blocks()->create([
                    'sort' => $i,
                    'kind' => $block['kind'],
                    'settings' => $block['settings'],
                    'condition' => $block['condition'],
                ]);
            }

            if ($type->blocks()->exists()) {
                (new LegacyBlockConverter)->convert($type->fresh('blocks'));
            }
        });
    }

    private function firearmsSection(string $hint = ''): array
    {
        return [
            'key' => 'firearm_details',
            'title' => 'Firearm details',
            'intro' => trim($hint.' Motivations are firearm- and serial-specific — add every firearm the application covers.'),
            'fields' => [
                ['name' => 'firearms', 'label' => 'Firearm(s) to be licensed / renewed', 'type' => 'firearms_table', 'required' => true],
                [
                    'name' => 'shotgun_action_notes',
                    'label' => 'If any firearm is a shotgun, specify action and choke',
                    'type' => 'text',
                    'condition' => ['field' => 'firearms.*.type', 'operator' => 'equals', 'value' => 'Shotgun'],
                ],
            ],
        ];
    }

    /** Yes/No items that appear on both the Self Defence and the Renewal (all categories) questionnaires. */
    private function selfDefenceYesLists(): array
    {
        return [
            [
                'key' => 'sd_employed',
                'title' => 'Section 13: Employed and/or self-employed',
                'condition' => null,
                'fields' => [
                    ['name' => 'employer_specialises', 'label' => 'Describe what your employer / company specialises in', 'type' => 'textarea'],
                    ['name' => 'closest_police_station', 'label' => 'Closest police station', 'type' => 'text'],
                    [
                        'name' => 'employment_profile',
                        'label' => 'Mark the options that apply to your work',
                        'type' => 'yes_no_list',
                        'items' => [
                            ['name' => 'shifts', 'label' => 'I work shifts', 'sentence' => 'The applicant works shift hours.'],
                            ['name' => 'travel_work', 'label' => 'I travel for work purposes', 'sentence' => 'The applicant travels frequently for work.'],
                            ['name' => 'travel_other_provinces', 'label' => 'I travel to other provinces for work', 'sentence' => 'The applicant travels between provinces for work.'],
                            ['name' => 'work_in_province_only', 'label' => 'I work inside the province only'],
                            ['name' => 'after_hours', 'label' => 'I work outside normal hours (8am-5pm)', 'sentence' => 'The applicant works outside of normal office hours, exposing him/her to higher risk.'],
                            ['name' => 'site_visits', 'label' => 'I attend site inspections or site meetings', 'sentence' => 'The applicant conducts site visits that take him/her away from a secure office environment.'],
                            ['name' => 'audits', 'label' => 'I conduct audits, surveys or reports'],
                            ['name' => 'quotes_tenders', 'label' => 'I prepare quotes or tenders'],
                            ['name' => 'work_overtime', 'label' => 'I work overtime'],
                            ['name' => 'travel_alone', 'label' => 'I travel alone', 'sentence' => 'The applicant frequently travels alone.'],
                            ['name' => 'transport_laptop', 'label' => 'I carry a laptop, tablet or phone', 'sentence' => 'The applicant transports high-value electronics that make him/her a target.'],
                            ['name' => 'own_transport', 'label' => 'I rely on my own transport'],
                            ['name' => 'public_transport', 'label' => 'I rely on public transport', 'sentence' => 'The applicant uses public transport, which exposes him/her to opportunistic crime.'],
                            ['name' => 'travel_10km', 'label' => 'I travel more than 10km to work'],
                            ['name' => 'client_visits', 'label' => 'I travel more than 10km to visit clients'],
                            ['name' => 'meetings', 'label' => 'I attend meetings / appointments'],
                            ['name' => 'part_time', 'label' => 'I have a part-time job'],
                            ['name' => 'expensive_equipment', 'label' => 'I transport expensive equipment for work', 'sentence' => 'The applicant transports expensive equipment that is attractive to criminals.'],
                            ['name' => 'transport_tools', 'label' => 'I transport tools'],
                            ['name' => 'confidential_documents', 'label' => 'I transport confidential documents / IDs / invoices'],
                            ['name' => 'drawings', 'label' => 'I transport drawings or plans'],
                            ['name' => 'cash_personal', 'label' => 'I carry cash for personal purposes', 'sentence' => 'The applicant occasionally carries cash for personal purposes.'],
                            ['name' => 'cash_business', 'label' => 'I carry cash for business purposes', 'sentence' => 'The applicant handles business cash, which raises robbery risk.'],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'sd_home_executive',
                'title' => 'Section 13: Home executive / housewife',
                'condition' => null,
                'fields' => [
                    [
                        'name' => 'home_executive_profile',
                        'label' => 'Mark the options that apply at home',
                        'type' => 'yes_no_list',
                        'items' => [
                            ['name' => 'provinces', 'label' => 'I travel to other provinces'],
                            ['name' => 'family_friends', 'label' => 'I travel more than 5km to visit family and friends'],
                            ['name' => 'own_transport', 'label' => 'I rely on my own transport'],
                            ['name' => 'children_school', 'label' => 'I travel more than 5km to transport children to school', 'sentence' => 'The applicant regularly transports children to and from school.'],
                            ['name' => 'extramural', 'label' => 'I transport children to extramural activities'],
                            ['name' => 'attend_extramural', 'label' => 'I attend extramural activities'],
                            ['name' => 'shopping', 'label' => 'I frequent shopping centres', 'sentence' => 'The applicant frequents shopping centres, which are known hotspots for crime.'],
                            ['name' => 'groceries', 'label' => 'I transport household goods / groceries'],
                            ['name' => 'medical', 'label' => 'I travel more than 3km to the doctor / pharmacy / clinic'],
                        ],
                    ],
                    ['name' => 'spouse_travel', 'label' => 'Does your spouse / partner travel often? How often and for how long?', 'type' => 'textarea'],
                ],
            ],
            [
                'key' => 'sd_pensioner',
                'title' => 'Section 13: Pensioners',
                'fields' => [
                    [
                        'name' => 'pensioner_profile',
                        'label' => 'Mark the options that apply',
                        'type' => 'yes_no_list',
                        'items' => [
                            ['name' => 'provinces', 'label' => 'I travel to other provinces'],
                            ['name' => 'family_friends', 'label' => 'I travel more than 5km to visit family and friends'],
                            ['name' => 'shopping', 'label' => 'I frequent shopping centres', 'sentence' => 'The applicant frequents shopping centres.'],
                            ['name' => 'grandchildren', 'label' => 'I transport grandchildren to school / extramural activities'],
                            ['name' => 'own_transport', 'label' => 'I rely on my own transport'],
                            ['name' => 'medical', 'label' => 'I travel more than 3km to the doctor / pharmacy / clinic'],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'sd_student',
                'title' => 'Section 13: Students',
                'fields' => [
                    ['name' => 'studying_what', 'label' => 'What are you studying?', 'type' => 'text'],
                    ['name' => 'student_mode', 'label' => 'Full-time or part-time?', 'type' => 'radio', 'options' => ['Full-time', 'Part-time']],
                    ['name' => 'student_transport', 'label' => 'Public or private transport?', 'type' => 'radio', 'options' => ['Public', 'Private']],
                    [
                        'name' => 'student_profile',
                        'label' => 'Mark the options that apply',
                        'type' => 'yes_no_list',
                        'items' => [
                            ['name' => 'travel_classes', 'label' => 'I travel more than 5km to attend classes'],
                            ['name' => 'laptop', 'label' => 'I transport a laptop / tablet / phone to class', 'sentence' => 'The applicant transports high-value study aids.'],
                            ['name' => 'part_time_job', 'label' => 'I have a part-time (student) job'],
                            ['name' => 'practical', 'label' => 'I do practical training on-site', 'sentence' => 'The applicant performs practical training away from campus.'],
                        ],
                    ],
                    ['name' => 'part_time_details', 'label' => 'Part-time job details and hours', 'type' => 'textarea'],
                    ['name' => 'practical_details', 'label' => 'Practical training details', 'type' => 'textarea'],
                ],
            ],
            [
                'key' => 'sd_other',
                'title' => 'Section 13: Other information',
                'fields' => [
                    [
                        'name' => 'other_profile',
                        'label' => 'Mark the options that apply',
                        'type' => 'yes_no_list',
                        'items' => [
                            ['name' => 'family_travel', 'label' => 'My family and I travel'],
                            ['name' => 'camping', 'label' => 'We camp'],
                            ['name' => 'hunting', 'label' => 'We hunt'],
                            ['name' => 'bikes', 'label' => 'I transport personal equipment (bikes, bicycles, etc.)'],
                            ['name' => 'mechanical', 'label' => 'My vehicle has had mechanical trouble on a trip'],
                            ['name' => 'children_school', 'label' => 'I travel more than 5km to transport children to school'],
                            ['name' => 'extramural', 'label' => 'I transport children to extramural activities'],
                            ['name' => 'shopping', 'label' => 'I frequent shopping centres'],
                            ['name' => 'groceries', 'label' => 'I transport household goods / groceries'],
                            ['name' => 'victim', 'label' => 'I have been a victim of violent crime or hijacking', 'sentence' => 'The applicant has previously been a victim of violent crime, demonstrating a direct personal threat.'],
                        ],
                    ],
                    ['name' => 'crime_case_number', 'label' => 'Case number (if a crime victim)', 'type' => 'text'],
                    ['name' => 'camping_frequency', 'label' => 'How often do you camp?', 'type' => 'text'],
                    ['name' => 'hunting_frequency', 'label' => 'How often do you hunt?', 'type' => 'text'],
                ],
            ],
            [
                'key' => 'sd_home_security',
                'title' => 'Section 13: Home security',
                'fields' => [
                    [
                        'name' => 'home_security',
                        'label' => 'What have you done to prevent crime on your property?',
                        'type' => 'yes_no_list',
                        'items' => [
                            ['name' => 'electric_fence', 'label' => 'Electric fencing'],
                            ['name' => 'palisade', 'label' => 'Palisade fencing'],
                            ['name' => 'high_walls', 'label' => 'High walls'],
                            ['name' => 'burglar_bars', 'label' => 'Burglar bars'],
                            ['name' => 'alarm', 'label' => 'Alarm system'],
                            ['name' => 'access_control', 'label' => 'Access control'],
                            ['name' => 'cctv', 'label' => 'CCTV'],
                            ['name' => 'security_24h', 'label' => '24-hour security'],
                            ['name' => 'armed_response', 'label' => 'Armed response'],
                            ['name' => 'neighbourhood_watch', 'label' => 'Neighbourhood watch'],
                        ],
                    ],
                    ['name' => 'armed_response_company', 'label' => 'Name of armed response company', 'type' => 'text'],
                ],
            ],
            [
                'key' => 'sd_farm',
                'title' => 'Section 13: Plot, smallholding or farm',
                'fields' => [
                    ['name' => 'has_farm', 'label' => 'Do you reside on / own a plot, smallholding or farm?', 'type' => 'radio', 'options' => ['Yes', 'No']],
                    ['name' => 'farm_location', 'label' => 'Where is the property?', 'type' => 'text', 'condition' => ['field' => 'answers.has_farm', 'operator' => 'equals', 'value' => 'Yes']],
                    ['name' => 'farm_province', 'label' => 'Province (GP, MP, Limp, NW, EC, WC, NC, FS, KZN)', 'type' => 'text', 'condition' => ['field' => 'answers.has_farm', 'operator' => 'equals', 'value' => 'Yes']],
                    ['name' => 'farm_frequency', 'label' => 'Permanently / regularly or over weekends?', 'type' => 'radio', 'options' => ['Permanently / regularly', 'Over weekends'], 'condition' => ['field' => 'answers.has_farm', 'operator' => 'equals', 'value' => 'Yes']],
                    [
                        'name' => 'farm_profile',
                        'label' => 'Mark the options that apply',
                        'type' => 'yes_no_list',
                        'condition' => ['field' => 'answers.has_farm', 'operator' => 'equals', 'value' => 'Yes'],
                        'items' => [
                            ['name' => 'travel_10km', 'label' => 'I travel more than 10km to the nearest town'],
                            ['name' => 'dirt_road', 'label' => 'Dirt road is the only access'],
                            ['name' => 'open_gates', 'label' => 'I open more than one gate to reach my property'],
                            ['name' => 'inspect_fence', 'label' => 'I inspect pivot irrigation / perimeter fencing'],
                            ['name' => 'visit_markets', 'label' => 'I visit markets and other towns'],
                            ['name' => 'generate_cash', 'label' => 'I generate cash from sales'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function commonRequirements(array $extra = [], bool $endorsement = true): array
    {
        $base = [
            ['kind' => DocumentKinds::POWER_OF_ATTORNEY, 'label' => 'Signed Power of Attorney', 'required' => true, 'hint' => 'Download the pre-filled POA, print, sign in black pen and upload.'],
            ['kind' => 'id_document', 'label' => 'ID document (no passport / drivers licence)', 'required' => true],
            ['kind' => 'proof_of_residence', 'label' => 'Proof of residence (water & lights)', 'required' => true, 'date_rule' => 'max_age_days', 'max_age_days' => 90, 'hint' => 'Not older than three months.'],
            ['kind' => 'safe_photos', 'label' => 'Photos of your safe (closed + inside)', 'required' => true, 'min_count' => 2],
            ['kind' => 'current_licences', 'label' => 'Current firearm licence cards (front and back)', 'required' => false, 'has_reference_number' => true, 'date_rule' => 'not_expired'],
            ['kind' => 'proficiency_certificate', 'label' => 'Proficiency / competency training certificate', 'required' => true, 'has_reference_number' => true],
            ['kind' => 'pftc_results', 'label' => 'PFTC Statement of Results', 'required' => true],
            ['kind' => 'saps_competency', 'label' => 'SAPS competency certificate', 'required' => true, 'has_reference_number' => true],
            ['kind' => 'membership_certificate', 'label' => 'Confirmation of membership certificate', 'required' => false, 'has_reference_number' => true, 'date_rule' => 'not_expired'],
            ['kind' => 'dedicated_certificate', 'label' => 'Dedicated hunter / sport shooter certificate (if 4+ firearms owned)', 'required' => false, 'has_reference_number' => true, 'date_rule' => 'not_expired'],
        ];

        if ($endorsement) {
            $base[] = ['kind' => DocumentKinds::ENDORSEMENT, 'label' => 'Club / association endorsement', 'required' => true, 'hint' => 'Ranyati requests this after the motivation is written.'];
        }

        return array_merge($base, $extra);
    }

    private function sportShooting(): array
    {
        return [
            'slug' => 'sport-shooting',
            'name' => 'Motivation: Sport Shooting',
            'category' => 'sport',
            'description' => 'Section 15 (occasional) or Section 16 (dedicated) sport-shooting applications. The generated motivation adapts automatically based on the applicant\'s section and discipline.',
            'has_motivation' => true,
            'sections' => [
                $this->firearmsSection(),
                [
                    'key' => 'sport_details',
                    'title' => 'Sport shooting details',
                    'intro' => 'The section of the Act and your sport discipline drive the generated wording — pick the ones that match the application.',
                    'fields' => [
                        ['name' => 'section', 'label' => 'Section of the Firearms Control Act', 'type' => 'radio', 'required' => true, 'options' => [
                            '15 — Occasional sports person',
                            '16 — Dedicated sports person',
                        ]],
                        ['name' => 'discipline', 'label' => 'Primary discipline', 'type' => 'select', 'required' => true, 'options' => [
                            'IPSC Production',
                            'IPSC Standard',
                            'IPSC Open',
                            'SADPA Ultra Compact',
                            'SADPA Classic',
                            'SADPA Open',
                            'Bisley',
                            'PRS (Precision Rifle Series)',
                            'Other',
                        ]],
                        ['name' => 'association_name', 'label' => 'Accredited association (if Section 16)', 'type' => 'text'],
                        ['name' => 'association_number', 'label' => 'Membership number', 'type' => 'text'],
                        ['name' => 'club_notes', 'label' => 'How often do you compete and at which clubs?', 'type' => 'textarea'],
                    ],
                ],
            ],
            'requirements' => $this->commonRequirements([
                ['kind' => 'compliance_history', 'label' => 'Compliance history / activity log', 'required' => false],
                ['kind' => 'sport_proof', 'label' => 'Sport shooting proof (calendars, scores, photos)', 'required' => true],
            ]),
            'blocks' => $this->sportShootingBlocks(),
        ];
    }

    /**
     * Sport-shooting book — covers both Section 15 occasional and Section 16
     * dedicated applications, with discipline-specific wording (IPSC, SADPA,
     * etc.) in conditional blocks lifted (and PII-sanitised) from Ranyati's
     * live 2026 reference motivations.
     *
     * @return array<int, array<string, mixed>>
     */
    private function sportShootingBlocks(): array
    {
        $out = $this->commonMotivationIntro(
            titleSuffix: 'Sport shooting application',
            section13Or15Or16: 'sport',
        );

        // Section-specific quote and member evidence.
        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Basis for application', 'level' => 2]];
        $out[] = [
            'kind' => 'text',
            'settings' => ['body' => "The basis for this application is a licence to possess a firearm for occasional sport shooting in terms of section 15 of the Firearms Control Act (Act 60 of 2000).\n\n".
                "I qualify for a firearm licence pertaining to occasional sport shooting. In terms of section 15 of the Act I may hold up to four such licences, subject to the restriction that no more than one licence may be issued in respect of a handgun contemplated in section 15(1)(a).\n\n".
                'I am aware of the fact that there are numerous applications of the same nature brought on a daily basis, as well as motivations that accompany these applications. It is only logical that there will be similarities and commonalities in most motivations because applicants apply for the same firearms and calibres, participate in the same sport-shooting events, join the same associations and clubs, and have the same needs in respect of their sport-shooting firearms. My motivation is, however, totally relevant to my personal circumstances and needs.',
            ],
            'condition' => ['field' => 'answers.section', 'operator' => 'contains', 'value' => '15'],
        ];
        $out[] = [
            'kind' => 'text',
            'settings' => ['body' => "The basis for this application is a licence to possess a firearm for dedicated sport shooting in terms of section 16 of the Firearms Control Act (Act 60 of 2000).\n\n".
                "I am a dedicated sports person and an active member of {{answers.association_name}} (membership number {{answers.association_number}}), which is a SAPS-accredited sport-shooting association. (See annexure: Dedicated Sport Shooter certificate.)\n\n".
                "In terms of section 16 of the Act, the FCA does not prescribe a quantity limit nor a calibre type. There is no prohibition on owning firearms in the same calibre, make, model or action, provided each firearm is required for the applicant's dedicated sport-shooting purpose.\n\n".
                'I am aware of the fact that there are numerous applications of the same nature brought on a daily basis. It is only logical that there will be similarities and commonalities in most motivations because applicants apply for the same firearms and calibres, participate in the same sport-shooting events and categories, and join the same associations and clubs. My motivation is, however, totally relevant to my personal circumstances and the specific discipline I compete in.',
            ],
            'condition' => ['field' => 'answers.section', 'operator' => 'contains', 'value' => '16'],
        ];

        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Section 15 of the Firearms Control Act (quoted)', 'level' => 2]];
        $out[] = [
            'kind' => 'text',
            'settings' => ['body' => "(1) A firearm in respect of which a licence may be issued in terms of this section is any —\n".
                "    (a) handgun which is not fully automatic;\n".
                "    (b) rifle or shotgun which is not fully or semi-automatic; or\n".
                "    (c) barrel, frame or receiver of a handgun, rifle or shotgun contemplated in paragraph (a) or (b), and which is not a restricted firearm.\n\n".
                "(2) The Registrar may issue a licence in terms of this section to any natural person who is an occasional hunter or occasional sports person.\n\n".
                "(3) Subject to the restrictions set out in section 15(3), no person may hold more than four licences issued in terms of this section, and a person may not hold more than one licence in respect of a handgun contemplated in subsection (1)(a).\n\n".
                '(4) A firearm in respect of which a licence has been issued in terms of this section may be used where it is safe to use the firearm and for a lawful purpose.',
            ],
            'condition' => ['field' => 'answers.section', 'operator' => 'contains', 'value' => '15'],
        ];
        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Section 16 of the Firearms Control Act (quoted)', 'level' => 2]];
        $out[] = [
            'kind' => 'text',
            'settings' => ['body' => "(1) A firearm in respect of which a licence may be issued in terms of this section is any —\n".
                "    (a) handgun which is not fully automatic;\n".
                "    (b) rifle or shotgun which is not fully automatic;\n".
                "    (c) any semi-automatic shotgun manufactured to fire no more than five shots in succession without having to be reloaded; or\n".
                "    (d) barrel, frame or receiver of a handgun, rifle or shotgun contemplated in paragraph (a), (b) or (c).\n\n".
                "(2) The Registrar may issue a licence in terms of this section to any natural person who is a dedicated hunter or dedicated sports person, if the application is accompanied by a sworn statement or solemn declaration from the chairperson of an accredited hunting association or sports-shooting organisation, or someone delegated in writing by him or her, stating that the applicant is a registered member of that association.\n\n".
                '(3) A firearm in respect of which a licence has been issued in terms of this section may be used where it is safe to use the firearm and for a lawful purpose.',
            ],
            'condition' => ['field' => 'answers.section', 'operator' => 'contains', 'value' => '16'],
        ];

        // Discipline-conditional blocks.
        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Discipline — {{answers.discipline}}', 'level' => 2]];
        foreach ($this->sportDisciplineBlocks() as $block) {
            $out[] = $block;
        }

        // Shared sport-shooting commentary on 9 mm Parabellum.
        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Why this calibre / this firearm', 'level' => 2]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => "The sport I compete in has specific restrictions on calibre, size, bullet weight and accessories for semi-automatic handguns. Shooting clubs and associations have different divisions and calibre restrictions for their categories — one cannot use a single handgun for every sport-shooting activity. I cannot effectively participate at club, national and international level if I am prevented from using the most suitable equipment for each category.\n\n".
            "The {{firearm.calibre}} {{firearm.make}} {{firearm.model}} ({{firearm.type}}, serial {{firearm.serial}}) offers the exceptional magazine capacity, mild recoil, superb accuracy and widely-available ammunition that make it ideal for the discipline I compete in. The low cost and minimal recoil of the {{firearm.calibre}} cartridge in particular make this calibre an ideal competition round for both novice and experienced shooters, and allow me to compete in multiple divisions without compromising on scoring.\n\n".
            'I currently do not own a {{firearm.calibre}} {{firearm.make}} {{firearm.model}} for sport-shooting purposes, and the firearm is specifically required for the division and class I motivate above.',
        ]];

        // Shared tail (firearm particulars, safety rules, Section 9(2), signature, document insert).
        foreach ($this->commonMotivationTail(sport: true) as $block) {
            $out[] = $block;
        }

        return $out;
    }

    /**
     * Discipline-specific text blocks. Only the one matching the applicant's
     * `answers.discipline` is rendered. Admin can tune or add new ones in
     * the builder — nothing is hard-coded in the renderer.
     *
     * @return array<int, array<string, mixed>>
     */
    private function sportDisciplineBlocks(): array
    {
        $drafts = [
            'IPSC Production' => "I intend to use the {{firearm.calibre}} {{firearm.make}} {{firearm.model}} in the IPSC Production, Minor-power-factor class.\n\n".
                "IPSC Production has specific equipment constraints: only handguns listed as approved on the IPSC website may be used; no optic or electronic sights are permitted; no ports or compensators are permitted; there is a minimum trigger-pull requirement; and the firearm must fit wholly within the confines of the IPSC equipment box. The minimum cartridge case dimension for handguns in IPSC matches is 9×19 mm, and the minimum bullet diameter is 9 mm (.354 inches). The {{firearm.calibre}} {{firearm.make}} {{firearm.model}} meets all of these requirements and is specifically suited to IPSC Production Minor.\n\n".
                'IPSC is a shooting sport where the three principles of Accuracy, Speed and Power are balanced. A match requires the competitor to complete various stages against the clock while scoring as many points as possible in the shortest time. Each stage typically requires 7 to 32 rounds; targets are engaged from 3 m to 50 m; movement is usually required; and the challenge may include moving targets, steel plates, barricades, partial and disappearing targets.',
            'IPSC Standard' => "I intend to use the {{firearm.calibre}} {{firearm.make}} {{firearm.model}} in the IPSC Standard division.\n\n".
                'IPSC Standard has specific equipment constraints: no optic or electronic sights, no ports or compensators, no minimum trigger pull, holster position restrictions, and the firearm must fit wholly within the confines of the IPSC equipment box. The {{firearm.calibre}} {{firearm.make}} {{firearm.model}} meets these requirements and is suited to the balanced accuracy, speed and power scoring regime of IPSC Standard.',
            'IPSC Open' => "I intend to use the {{firearm.calibre}} {{firearm.make}} {{firearm.model}} in the IPSC Open division.\n\n".
                'IPSC Open permits optic/electronic sights, ports and compensators, has no minimum trigger-pull requirement, no maximum firearm size and no restrictions on holster position. The {{firearm.calibre}} {{firearm.make}} {{firearm.model}} is specifically suited to the equipment profile of the Open division.',
            'SADPA Ultra Compact' => "I intend to use the {{firearm.calibre}} {{firearm.make}} {{firearm.model}} competitively in the SADPA Ultra Compact division.\n\n".
                'The South African Defensive Pistol Association (SADPA) defines Ultra Compact as a specific equipment-restricted division of defensive pistol shooting. The {{firearm.calibre}} {{firearm.make}} {{firearm.model}} is specifically dimensioned and configured for this division, and I cannot compete in Ultra Compact without a firearm that falls within its equipment profile.',
            'SADPA Classic' => "I intend to use the {{firearm.calibre}} {{firearm.make}} {{firearm.model}} competitively in the SADPA Classic division.\n\n".
                "SADPA Classic has specific equipment constraints that differ from the association's other divisions, and I cannot effectively compete at club or national level in Classic without a firearm configured for that specific division.",
            'SADPA Open' => "I intend to use the {{firearm.calibre}} {{firearm.make}} {{firearm.model}} competitively in the SADPA Open division.\n\n".
                "SADPA Open permits a wider range of equipment than the association's restricted divisions, and I require a firearm suited to that equipment profile to compete effectively.",
            'Bisley' => 'I intend to use the firearm applied for in the Bisley target-shooting discipline. Bisley is a precision target-shooting sport with established equipment and distance protocols; the firearm detailed above fits those requirements for my chosen Bisley class.',
            'PRS (Precision Rifle Series)' => 'I intend to use the firearm applied for in the Precision Rifle Series (PRS) discipline. PRS is a tactical precision-rifle discipline with equipment and calibre norms suited to the firearm detailed above.',
            'Other' => 'I compete in the {{answers.discipline}} discipline. The equipment restrictions and competitive profile of this discipline are summarised in my application and I motivate the firearm applied for above on that basis.',
        ];

        $blocks = [];
        foreach ($drafts as $discipline => $body) {
            $blocks[] = [
                'kind' => 'text',
                'settings' => ['body' => $body],
                'condition' => ['field' => 'answers.discipline', 'operator' => 'equals', 'value' => $discipline],
            ];
        }

        return $blocks;
    }

    private function hunting(): array
    {
        return [
            'slug' => 'hunting',
            'name' => 'Motivation: Hunting',
            'category' => 'hunting',
            'description' => 'Section 15 (occasional) or Section 16 (dedicated) hunting applications. Covers primary-rifle and backup-handgun motivations.',
            'has_motivation' => true,
            'sections' => [
                $this->firearmsSection(),
                [
                    'key' => 'hunting_details',
                    'title' => 'Hunting details',
                    'intro' => 'The section of the Act, whether this firearm is a primary or backup firearm, and the terrain / quarry drive the generated wording.',
                    'fields' => [
                        ['name' => 'section', 'label' => 'Section of the Firearms Control Act', 'type' => 'radio', 'required' => true, 'options' => [
                            '15 — Occasional hunter',
                            '16 — Dedicated hunter',
                        ]],
                        ['name' => 'hunting_role', 'label' => 'Role of this firearm when hunting', 'type' => 'radio', 'required' => true, 'options' => [
                            'Primary — long range / stalking',
                            'Backup — close-range follow-up / dangerous game',
                        ]],
                        ['name' => 'association_name', 'label' => 'Accredited hunting association (if Section 16)', 'type' => 'text'],
                        ['name' => 'association_number', 'label' => 'Membership number', 'type' => 'text'],
                        ['name' => 'hunting_frequency', 'label' => 'How often do you hunt?', 'type' => 'text'],
                        ['name' => 'hunting_locations', 'label' => 'Where do you hunt most often?', 'type' => 'textarea'],
                        ['name' => 'species', 'label' => 'Which species / what terrain?', 'type' => 'textarea'],
                    ],
                ],
            ],
            'requirements' => $this->commonRequirements([
                ['kind' => 'compliance_history', 'label' => 'Compliance history / activity log', 'required' => false],
                ['kind' => 'hunting_proof', 'label' => 'Hunting proof (letters, invoices, photos)', 'required' => true],
            ]),
            'blocks' => $this->huntingBlocks(),
        ];
    }

    /**
     * Hunting book — covers Section 15 occasional + Section 16 dedicated,
     * with a conditional "backup handgun" sub-flow lifted from Ranyati's
     * live Canik Occ Hunt and Glock 19 Ded Hunt reference motivations.
     *
     * @return array<int, array<string, mixed>>
     */
    private function huntingBlocks(): array
    {
        $out = $this->commonMotivationIntro(
            titleSuffix: 'Hunting application',
            section13Or15Or16: 'hunting',
        );

        // Section-specific quote and member evidence.
        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Basis for application', 'level' => 2]];
        $out[] = [
            'kind' => 'text',
            'settings' => ['body' => "The basis for this application is a licence to possess a firearm for occasional hunting in terms of section 15 of the Firearms Control Act (Act 60 of 2000).\n\n".
                "In terms of section 15 of the Act I qualify for a licence pertaining to occasional hunting. The Act does not restrict the use of duplicate rifles or rifles of the same calibre.\n\n".
                'I am aware of the fact that there are numerous applications of the same nature brought on a daily basis. It is only logical that there will be similarities and commonalities in most motivations because applicants apply for the same firearms and calibres, hunt in the same areas, hunt the same species, and have the same needs in respect of their hunting firearms. My motivation is, however, totally relevant to my personal circumstances and the specific terrain and game I hunt.',
            ],
            'condition' => ['field' => 'answers.section', 'operator' => 'contains', 'value' => '15'],
        ];
        $out[] = [
            'kind' => 'text',
            'settings' => ['body' => "The basis for this application is a licence to possess a firearm for dedicated hunting in terms of section 16 of the Firearms Control Act (Act 60 of 2000).\n\n".
                "I am a dedicated hunter and an active member of {{answers.association_name}} (membership number {{answers.association_number}}), which is a SAPS-accredited hunting association. (See annexure: Dedicated Hunter certificate.)\n\n".
                "In terms of section 16 of the Act, the FCA does not prescribe quantity or calibre type. The Act is not specific to the number of firearms a person may own, and makes no reference to duplicate calibres or handguns. There is no prohibition on owning firearms in the same calibre, provided each firearm is required for the applicant's dedicated hunting purpose.\n\n".
                'I am aware that there are many motivations with similar wording. It is logical that applicants in the same accredited association, hunting the same species in similar terrain, will share common needs. My motivation is, however, specific to my own hunting profile, which I set out below.',
            ],
            'condition' => ['field' => 'answers.section', 'operator' => 'contains', 'value' => '16'],
        ];

        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Section 15 of the Firearms Control Act (quoted)', 'level' => 2]];
        $out[] = [
            'kind' => 'text',
            'settings' => ['body' => "(1) A firearm in respect of which a licence may be issued in terms of this section is any —\n".
                "    (a) handgun which is not fully automatic;\n".
                "    (b) rifle or shotgun which is not fully or semi-automatic; or\n".
                "    (c) barrel, frame or receiver of a handgun, rifle or shotgun contemplated in paragraph (a) or (b), and which is not a restricted firearm.\n\n".
                "(2) The Registrar may issue a licence in terms of this section to any natural person who is an occasional hunter or occasional sports person.\n\n".
                '(3) Subject to the restrictions set out in section 15(3), no person may hold more than four licences issued in terms of this section, and a person may not hold more than one licence in respect of a handgun contemplated in subsection (1)(a).',
            ],
            'condition' => ['field' => 'answers.section', 'operator' => 'contains', 'value' => '15'],
        ];
        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Section 16 of the Firearms Control Act (quoted)', 'level' => 2]];
        $out[] = [
            'kind' => 'text',
            'settings' => ['body' => "(1) A firearm in respect of which a licence may be issued in terms of this section is any —\n".
                "    (a) handgun which is not fully automatic;\n".
                "    (b) rifle or shotgun which is not fully automatic;\n".
                "    (c) any semi-automatic shotgun manufactured to fire no more than five shots in succession without having to be reloaded; or\n".
                "    (d) barrel, frame or receiver of a handgun, rifle or shotgun contemplated in paragraph (a), (b) or (c).\n\n".
                '(2) The Registrar may issue a licence in terms of this section to any natural person who is a dedicated hunter or dedicated sports person, if the application is accompanied by a sworn statement or solemn declaration from the chairperson of an accredited hunting association, or someone delegated in writing by him or her, stating that the applicant is a registered member of that association.',
            ],
            'condition' => ['field' => 'answers.section', 'operator' => 'contains', 'value' => '16'],
        ];

        // Hunting role conditional block.
        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Hunting profile and intended use', 'level' => 2]];
        $out[] = [
            'kind' => 'text',
            'settings' => ['body' => "I hunt {{answers.hunting_frequency}}. My primary hunting grounds are {{answers.hunting_locations}}. The species / terrain I hunt is: {{answers.species}}.\n\n".
                'The firearm applied for is my primary hunting firearm for this profile. The {{firearm.calibre}} {{firearm.make}} {{firearm.model}} ({{firearm.type}}, serial {{firearm.serial}}) is specifically suited to the quarry and terrain described above, and offers the ballistic performance, accuracy and reliability required for an ethical, humane one-shot kill.',
            ],
            'condition' => ['field' => 'answers.hunting_role', 'operator' => 'contains', 'value' => 'Primary'],
        ];
        $out[] = [
            'kind' => 'text',
            'settings' => ['body' => "I hunt {{answers.hunting_frequency}}. My primary hunting grounds are {{answers.hunting_locations}}. The species / terrain I hunt is: {{answers.species}}.\n\n".
                "I apply for the {{firearm.calibre}} {{firearm.make}} {{firearm.model}} ({{firearm.type}}, serial {{firearm.serial}}) as a backup handgun to be used alongside my primary hunting rifle(s).\n\n".
                "When hunting, shot placement is everything — one strives for a clean one-shot kill through the heart or lungs. Sometimes things do not go as planned, and a fast-firing backup firearm is required for the humane dispatch of wounded animals and for the safety of the hunter and those around the hunter. Nothing reloads as fast as drawing a secondary firearm.\n\n".
                'I am also involved in wing-shooting and close-range bush-pig hunting, where a 12-gauge shotgun causes extreme meat damage at close range and is not suitable for the humane dispatch of game. The {{firearm.calibre}} handgun is well-suited to these close-range follow-up situations and to the small-to-medium game encountered in bushveld conditions.',
            ],
            'condition' => ['field' => 'answers.hunting_role', 'operator' => 'contains', 'value' => 'Backup'],
        ];

        // Shared calibre commentary.
        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Why this calibre / this firearm', 'level' => 2]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => "The {{firearm.calibre}} {{firearm.make}} {{firearm.model}} offers reliable, widely-available ammunition, the magazine capacity appropriate to its role, and the ballistic performance required for the game and terrain I hunt. The bullet weight and velocity range of the {{firearm.calibre}} cartridge make it a versatile round, well-suited to the applicant's hunting profile motivated above.\n\n".
            'I currently do not own a {{firearm.calibre}} {{firearm.make}} {{firearm.model}} for this purpose, and the firearm is specifically required for the role I have motivated above.',
        ]];

        foreach ($this->commonMotivationTail(sport: false) as $block) {
            $out[] = $block;
        }

        return $out;
    }

    /**
     * The opening of every section-13/15/16 motivation book: cover,
     * applicant particulars, declaration, clean record and introduction.
     * Identical boilerplate used by every motivation type's `*Blocks()`
     * builder; admin can still edit each resulting block per type in the
     * builder.
     *
     * @return array<int, array<string, mixed>>
     */
    private function commonMotivationIntro(string $titleSuffix, string $section13Or15Or16): array
    {
        return [
            ['kind' => 'title_page', 'settings' => [
                'title' => 'Motivation for a {{firearm.calibre}} {{firearm.type}}',
                'subtitle' => '{{firearm.make}} {{firearm.model}}  |  Serial no: {{firearm.serial}}',
                'body' => "Applicant: {{profile.full_name}}\nID number: {{profile.id_number}}\nReference: {{request.reference}}\nDate: {{today}}\n\n{$titleSuffix} — prepared by Ranyati Firearm Motivations (Pty) Ltd.",
            ]],
            ['kind' => 'heading', 'settings' => ['text' => 'Applicant particulars', 'level' => 2]],
            ['kind' => 'text', 'settings' => ['body' => "Name: {{profile.full_name}}\n".
                "ID number: {{profile.id_number}}\n".
                "Residential address: {{profile.physical_address}}\n".
                "Occupation: {{profile.occupation}}\n".
                'Contact: {{profile.cell}} / {{profile.email}}',
            ]],
            ['kind' => 'heading', 'settings' => ['text' => 'Declaration', 'level' => 2]],
            ['kind' => 'text', 'settings' => ['body' => "I, the undersigned, {{profile.full_name}} (ID number {{profile.id_number}}), motivate my application for the licensing of the above-mentioned {{firearm.calibre}} {{firearm.make}} {{firearm.model}} (serial number {{firearm.serial}}) as follows:\n\n".
                'I am a {{profile.age}}-year-old South African citizen. I currently reside at {{profile.physical_address}} and I am employed as a {{profile.occupation}}.',
            ]],
            ['kind' => 'heading', 'settings' => ['text' => 'Clean record', 'level' => 2]],
            ['kind' => 'text', 'settings' => ['body' => 'I see myself as a law-abiding citizen. I have no criminal record against my name and have not been found guilty of any offences that can declare me unfit to possess a firearm as stipulated in Section 9 of the Firearms Control Act 2000 (Act No 60 of 2000). '.
                'I have kept strictly to the stipulations of the previous Act, and have never been charged or convicted of a firearm- or violence-related crime. I regard myself to be a responsible person.',
            ]],
            ['kind' => 'heading', 'settings' => ['text' => 'Introduction', 'level' => 2]],
            ['kind' => 'text', 'settings' => ['body' => 'I have successfully completed my proficiency training as required by law, and the required training unit standards. I have demonstrated the competency and understanding to safely possess, handle and use a firearm, as evidenced by the annexed Competency Certificate and SAPS Competency Certificate. The firearm applied for will be used lawfully, safely and exclusively for the '.($section13Or15Or16 === 'sport' ? 'sport-shooting' : 'hunting').' purpose set out in this motivation.',
            ]],
        ];
    }

    /**
     * The closing of every motivation book: firearm particulars table,
     * fundamental safety rules, Section 9(2) compliance checklist,
     * conclusion / signature, document insert.
     *
     * @return array<int, array<string, mixed>>
     */
    private function commonMotivationTail(bool $sport): array
    {
        $kinds = [
            'id_document', 'proof_of_residence', 'current_licences', 'proficiency_certificate', 'pftc_results',
            'saps_competency', 'safe_photos', 'membership_certificate', 'dedicated_certificate',
            $sport ? 'sport_proof' : 'hunting_proof', DocumentKinds::POWER_OF_ATTORNEY,
        ];

        return [
            ['kind' => 'heading', 'settings' => ['text' => 'The firearm applied for', 'level' => 2]],
            ['kind' => 'firearms_table', 'settings' => ['title' => 'Firearm particulars']],
            ['kind' => 'heading', 'settings' => ['text' => 'Fundamental rules for safe gun handling', 'level' => 2]],
            ['kind' => 'text', 'settings' => ['body' => "I undertake to apply the three fundamental rules of safe gun handling at all times:\n\n".
                "1. ALWAYS keep the gun pointed in a safe direction. A safe direction means that the gun is pointed so that even if it were to go off, it would not cause injury or damage. The key to this rule is to control where the muzzle is pointed at all times.\n\n".
                "2. ALWAYS keep your finger off the trigger until ready to shoot. When holding a gun, rest your finger on the trigger guard or along the side of the gun. Until you are actually ready to fire, do not touch the trigger.\n\n".
                '3. ALWAYS keep the gun unloaded until ready to use. Whenever you pick up a gun, engage the safety device if possible, remove the magazine if applicable, and open the action to inspect that the chamber is clear of ammunition.',
            ]],
            ['kind' => 'heading', 'settings' => ['text' => 'Section 9(2) compliance statement', 'level' => 2]],
            ['kind' => 'text', 'settings' => ['body' => "In my respectful submission, I am compliant with Section 9(2) of the Firearms Control Act in that:\n\n".
                "• I am older than 21 years;\n".
                "• I am a fit and proper person to possess firearms;\n".
                "• I am of stable mental condition and I am not inclined to violence;\n".
                "• I do not depend on any substance which has an intoxicating or narcotic effect;\n".
                "• I have not been convicted of any offence under or in terms of this Act or the previous Act and sentenced to a period of imprisonment without the option of a fine;\n".
                "• I have not been convicted of any offence involving the unlawful or negligent use or handling of a firearm;\n".
                "• I have not been convicted of any offence involving sexual abuse, physical abuse within a domestic relationship, fraud or dishonesty in relation to firearms legislation, abuse of drugs or alcohol, dealing in drugs, or any offence in terms of the Domestic Violence Act;\n".
                "• I have not been convicted of any offence involving sabotage, terrorism, public violence, arson, intimidation, rape, kidnapping or child stealing;\n".
                "• I have not become, and have not been declared, unfit to possess a firearm in terms of any firearms legislation; and\n".
                '• I have completed the prescribed test on the knowledge of the Act and the prescribed training and practical test regarding the safe and efficient handling of a firearm.',
            ]],
            ['kind' => 'heading', 'settings' => ['text' => 'Conclusion', 'level' => 2]],
            ['kind' => 'text', 'settings' => ['body' => "I have complied with the requirements set out in the relevant section of the FCA and have disclosed the purpose for which the firearm will be used. The Registrar is respectfully requested to apply its mind to this application and to grant a licence in terms of the Firearms Control Act 60 of 2000.\n\n".
                "Signed at _____________________________ on this ______ day of __________________________ 2026.\n\n\n".
                "____________________________________\n".
                '{{profile.full_name}}  —  ID {{profile.id_number}}',
            ]],
            ['kind' => 'document_insert', 'settings' => [
                'divider_title' => 'Supporting documents',
                'kinds' => $kinds,
            ]],
        ];
    }

    private function selfDefence(): array
    {
        return [
            'slug' => 'self-defence',
            'name' => 'Motivation: Self Defence (Handgun)',
            'category' => 'self_defence',
            'description' => 'Section 13(1) self-defence handgun application.',
            'has_motivation' => true,
            'sections' => array_merge(
                [$this->firearmsSection()],
                [[
                    'key' => 'location',
                    'title' => 'Province & location',
                    'intro' => 'Crime context is province-specific — the generated motivation references the right regional crime statistics automatically.',
                    'fields' => [
                        ['name' => 'province', 'label' => 'Province where the firearm will primarily be carried', 'type' => 'select', 'required' => true, 'options' => QuestionSchema::PROVINCES],
                        ['name' => 'policing_precinct', 'label' => 'Policing precinct / closest police station', 'type' => 'text', 'required' => true],
                    ],
                ]],
                $this->selfDefenceYesLists(),
            ),
            'requirements' => array_merge(
                $this->commonRequirements([], endorsement: false),
                [
                    ['kind' => 'case_numbers', 'label' => 'Case numbers (if any)', 'required' => false, 'has_reference_number' => true],
                    ['kind' => 'crime_clippings', 'label' => 'Newspaper clippings of local crime (if available)', 'required' => false],
                ],
            ),
            'blocks' => $this->selfDefenceBlocks(),
        ];
    }

    /**
     * The full Section 13 (self-defence) book — faithful to Ranyati's live
     * 2026 Canik MC9 L template, but PII-free: every applicant-specific
     * value is a placeholder so the same template serves every client.
     *
     * Admin can edit every single block in the application-type builder, so
     * tuning phrasing or dropping sections never requires code changes.
     *
     * @return array<int, array<string, mixed>>
     */
    private function selfDefenceBlocks(): array
    {
        $out = [];

        // Cover + applicant particulars.
        $out[] = ['kind' => 'title_page', 'settings' => [
            'title' => 'Motivation for a {{firearm.calibre}} {{firearm.type}}',
            'subtitle' => '{{firearm.make}} {{firearm.model}}  |  Serial no: {{firearm.serial}}',
            'body' => "Applicant: {{profile.full_name}}\nID number: {{profile.id_number}}\nReference: {{request.reference}}\nDate: {{today}}\n\nPrepared by Ranyati Firearm Motivations (Pty) Ltd.",
        ]];
        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Applicant particulars', 'level' => 2]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => "Name: {{profile.full_name}}\n".
            "ID number: {{profile.id_number}}\n".
            "Residential address: {{profile.physical_address}}\n".
            "Occupation: {{profile.occupation}}\n".
            'Contact: {{profile.cell}} / {{profile.email}}',
        ]];

        // Opening declaration — close paraphrase of the live Canik SD doc.
        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Declaration', 'level' => 2]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => "I, the undersigned, {{profile.full_name}} (ID number {{profile.id_number}}), motivate my application for the licensing of the above-mentioned {{firearm.calibre}} {{firearm.make}} {{firearm.model}} pistol (serial number {{firearm.serial}}) as follows:\n\n".
            'I am a {{profile.age}}-year-old South African citizen. I currently reside at {{profile.physical_address}} and I am employed as a {{profile.occupation}}.',
        ]];

        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Clean record', 'level' => 2]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => 'I see myself as a law-abiding citizen. I have no criminal record against my name and have not been found guilty of any offences that can declare me unfit to possess a firearm as stipulated in Section 9 of the Firearms Control Act 2000 (Act No 60 of 2000). '.
            'I have kept strictly to the stipulations of the previous Act, and have never been charged or convicted of a firearm- or violence-related crime. I regard myself to be a responsible person.',
        ]];

        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Basis for application', 'level' => 2]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => 'This application is for a licence to possess a firearm for self-defence in terms of section 13 of the Firearms Control Act (Act 60 of 2000). '.
            'This application is directly in line with the constitutionally entrenched right to Freedom and Security of the person in terms of Section 12 of the Constitution of the Republic of South Africa.',
        ]];

        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Special circumstances', 'level' => 2]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => "I am aware of the fact that there are numerous applications of the same nature brought on a daily basis, as well as motivations that accompany these applications. It is only logical that there will be similarities and commonalities in most of these motivations, due to the fact that applicants apply for the same firearms and calibres, live in the same areas, and have the same threats and needs in respect of their self-defence firearms.\n\n".
            "All citizens of South Africa are affected by crime in one way or another, and therefore crime has a bearing and relevance on all of us as we all suffer a risk and threat due to the circumstances of residing and working in SA. This justifies the need for a firearm for self-defence, taking into account that approximately 68 people are murdered on average every day in South Africa according to the latest crime statistics issued by the SAPS.\n\n".
            'My motivation is, however, totally relevant to my personal circumstances and needs.',
        ]];

        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Introduction', 'level' => 2]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => "As previously stated, I currently reside at {{profile.physical_address}} and I am employed as a {{profile.occupation}}.\n\n".
            "I have successfully completed my proficiency training as required by law and have successfully completed the required training unit standards. I have demonstrated the required proficiency, competency and understanding to safely possess, handle and use the following:\n\n".
            "• Demonstrate knowledge of the Firearms Control Act 60 of 2000 — Unit Standard 117705\n".
            "• Use of a handgun — Unit Standard 119649\n".
            "• SAPS Competency Certificate\n\n".
            '(See annexures: Competency Certificate, SAPS Competency Certificate.)',
        ]];

        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Purpose of the firearm', 'level' => 2]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => "Section 13(2)(a) and Section 13(2)(b) of the Firearms Control Act (Act 60 of 2000 as amended) state the following:\n\n".
            "(2) The Registrar may issue a licence under this section to any natural person who —\n".
            "    (a) needs a firearm for self-defence; and\n".
            "    (b) cannot reasonably satisfy that need by means other than the possession of a firearm.\n\n".
            'I meet both requirements, as motivated below.',
        ]];

        // Dynamic exposure sections — driven by the applicant's answers.
        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Occupational exposure', 'level' => 2]];
        $out[] = ['kind' => 'yes_list_paragraph', 'settings' => [
            'field' => 'employment_profile',
            'intro' => 'In the course of carrying out my duties as a {{profile.occupation}}:',
            'outro' => 'I rely on my own transport and routinely travel further than 10 km to attend meetings and site visits. I regularly work outside normal working hours. I am often alone in my vehicle after dark and I frequently transport electronic equipment, documents and cash — all of which materially increase my risk of becoming a victim of armed robbery or hijacking.',
        ]];

        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Personal and family exposure', 'level' => 2]];
        $out[] = ['kind' => 'yes_list_paragraph', 'settings' => [
            'field' => 'other_profile',
            'intro' => 'In addition to my occupational exposure:',
            'outro' => 'My family and I also travel together for personal and recreational purposes. We transport equipment and belongings. During previous travel I have experienced mechanical breakdowns. There is no other reasonable means to protect the lives of my family and the people travelling with me, other than by means of a licensed firearm.',
        ]];

        // Province-conditional crime context — only one block fires.
        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Crime context in {{answers.province}}', 'level' => 2]];
        foreach ($this->provinceCrimeStatsBlocks() as $block) {
            $out[] = $block;
        }

        // National SAPS Q4 2024/25 context (same for every province).
        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'National crime trends', 'level' => 2]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => "The Minister of Police has presented the latest crime statistics for South Africa, covering the fourth quarter of the 2024/25 administrative year (January to March 2026). While decreases were reported in several categories, violent and property-related crime remains a daily reality in the applicant's immediate environment.\n\n".
            "Firearms, knives and other sharp instruments remain the most frequently used murder weapons. In the quarter under review, firearms were used in the killing of 2 120 victims. The SAPS also reported the seizure of no fewer than 100 illegal firearms per week through tracing operations, stop-and-searches and other targeted law-enforcement interventions, and recorded 4 023 cases relating to the illegal possession of firearms and ammunition during the same quarter.\n\n".
            "According to Statistics South Africa's Governance, Public Safety and Justice Survey (GPSJS) 2024/25, approximately 1.1 million households were affected by housebreaking during the reporting period, and a further 213 000 households experienced home robberies. Firearms were the most commonly used weapon in armed home robberies, featuring in 71.1% of such incidents. Only 36.1% of South Africans felt safe walking alone in their neighbourhoods after dark.\n\n".
            'These statistics confirm that violent crime remains a daily reality in South Africa and that law-abiding citizens continue to face legitimate and recurring risks to their personal safety and that of their family.',
        ]];

        // Alternative means + home security.
        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Alternative means of protection', 'level' => 2]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => 'I have taken reasonable alternative measures to protect my life, my family and my property, as follows:',
        ]];
        $out[] = ['kind' => 'yes_list_paragraph', 'settings' => [
            'field' => 'home_security',
            'intro' => '',
            'as_list' => true,
        ]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => "Notwithstanding these measures, it is not practically or financially viable for me to hire a security guard to accompany me on every work- and travel-related movement. Not being able to travel freely would mean that I cannot earn a living and support my family.\n\n".
            "It is my submission that I cannot satisfy my need for self-defence in any other reasonable way than by the responsible use of a licensed firearm. My specific protection needs are:\n\n".
            "• To protect my own life\n".
            "• To protect the lives of my family\n".
            "• To protect the lives of others travelling with me\n".
            "• To protect my property\n".
            '• To protect cash and personal belongings carried on me',
        ]];

        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Last resort — Section 13(2)(b) of the FCA', 'level' => 2]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => "I therefore have to rely on the rights afforded to me as a citizen in terms of Sections 11 and 25 of the Constitution — the right to life, and the right to own property. By implication, I may protect my own life and the lives of my loved ones, and I may protect my property.\n\n".
            'I have reached a stage in my life where I have to seriously depend on these rights to self-preservation, and I can sincerely state that I have no other reasonable means of exercising them without an appropriate firearm.',
        ]];

        // The firearm itself.
        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'The firearm applied for', 'level' => 2]];
        $out[] = ['kind' => 'firearms_table', 'settings' => ['title' => 'Firearm particulars']];
        $out[] = ['kind' => 'text', 'settings' => ['body' => "The firearm applied for is a {{firearm.calibre}} {{firearm.make}} {{firearm.model}} ({{firearm.type}}), serial number {{firearm.serial}}. Ammunition in this calibre is widely and consistently available in South Africa. The pistol is accurate, comfortable to fire, and well-suited to concealed carry while travelling, and also serves as a home-defence firearm.\n\n".
            'This pistol fits my posture and intended use, and will serve the purposes motivated above very well.',
        ]];

        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Fundamental rules for safe gun handling', 'level' => 2]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => "I undertake to apply the three fundamental rules of safe gun handling at all times:\n\n".
            "1. ALWAYS keep the gun pointed in a safe direction. A safe direction means that the gun is pointed so that even if it were to go off, it would not cause injury or damage. The key to this rule is to control where the muzzle is pointed at all times.\n\n".
            "2. ALWAYS keep your finger off the trigger until ready to shoot. When holding a gun, rest your finger on the trigger guard or along the side of the gun. Until you are actually ready to fire, do not touch the trigger.\n\n".
            '3. ALWAYS keep the gun unloaded until ready to use. Whenever you pick up a gun, engage the safety device if possible, remove the magazine if applicable, and open the action to inspect that the chamber is clear of ammunition.',
        ]];

        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Section 9(2) compliance statement', 'level' => 2]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => "In my respectful submission, I am compliant with Section 9(2) of the Firearms Control Act in that:\n\n".
            "• I am older than 21 years;\n".
            "• I am a fit and proper person to possess firearms;\n".
            "• I am of stable mental condition and I am not inclined to violence;\n".
            "• I do not depend on any substance which has an intoxicating or narcotic effect;\n".
            "• I have not been convicted of any offence under or in terms of this Act or the previous Act and sentenced to a period of imprisonment without the option of a fine;\n".
            "• I have not been convicted of any offence involving the unlawful use or handling of a firearm;\n".
            "• I have not been convicted of any offence involving sexual abuse, physical abuse within a domestic relationship, fraud or dishonesty in relation to firearms legislation, abuse of drugs or alcohol, dealing in drugs, or any offence in terms of the Domestic Violence Act;\n".
            "• I have not been convicted of any offence involving the negligent handling of a firearm, nor any offence involving sabotage, terrorism, public violence, arson, intimidation, rape, kidnapping or child stealing;\n".
            "• I have not become, and have not been declared, unfit to possess a firearm in terms of any firearms legislation; and\n".
            '• I have completed the prescribed test on the knowledge of the Act, and the prescribed training and practical test regarding the safe and efficient handling of a firearm.',
        ]];

        $out[] = ['kind' => 'heading', 'settings' => ['text' => 'Conclusion', 'level' => 2]];
        $out[] = ['kind' => 'text', 'settings' => ['body' => "I have complied with the requirements as set out in Section 13 of the FCA and have disclosed the purpose for which the firearm will be used, namely self-defence. The Registrar is respectfully requested to apply its mind to this application and to consider the specific circumstances motivated above in granting a licence in terms of Section 13 of the Firearms Control Act 60 of 2000.\n\n".
            "Signed at _____________________________ on this ______ day of __________________________ 2026.\n\n\n".
            "____________________________________\n".
            '{{profile.full_name}}  —  ID {{profile.id_number}}',
        ]];

        $out[] = ['kind' => 'document_insert', 'settings' => [
            'divider_title' => 'Supporting documents',
            'kinds' => [
                'id_document', 'proof_of_residence', 'current_licences', 'proficiency_certificate', 'pftc_results',
                'saps_competency', 'safe_photos', 'case_numbers', 'crime_clippings', DocumentKinds::POWER_OF_ATTORNEY,
            ],
        ]];

        return $out;
    }

    /**
     * One text block per South African province, each gated with a condition
     * so only the one matching the applicant's answer appears in the printed
     * book. The admin can edit each block's body to tune the wording / stats
     * at any time via the application-type builder — no code changes needed.
     *
     * Gauteng's wording is lifted directly from Ranyati's live 2026 Canik
     * self-defence template, PII-removed. The remaining eight provinces are
     * starter paragraphs; Riaan can swap each one for the matching real
     * wording once he sends the sample motivation for that province.
     *
     * @return array<int, array{kind: string, settings: array, condition: array}>
     */
    private function provinceCrimeStatsBlocks(): array
    {
        $drafts = [
            'Gauteng' => "Gauteng remains the province with the highest recorded violent and property crime figures in the country, and specifically the highest number of carjackings. I live and work in this environment and routinely travel between my residence, workplace and client sites in and around {{answers.policing_precinct}}. The risk of carjacking, armed robbery, residential robbery and home invasion in my daily movements is materially elevated.\n\n".
                "SAPS Q4 2024/25 data shows Gauteng as the top-contributing province to the national totals in rape (19.1%), commercial crime (12 074 counts) and ransom-related kidnapping (approximately 75.5% of all ransom kidnapping incidents nationally). It also recorded the highest number of police-officer murders during the quarter under review, with 10 of the 22 officers killed on duty or off duty in Gauteng alone.\n\n".
                "These statistics confirm that, despite SAPS's ongoing efforts, I am materially exposed in my daily movements through this province.",
            'Western Cape' => "The Western Cape, and in particular the Cape Town metro, continues to record exceptionally high murder and gang-related assault figures. SAPS Q4 2024/25 reported that the Western Cape recorded an increase in murders despite the national decrease, and the province also contributed significantly to the national rape and commercial-crime totals.\n\n".
                "In and around {{answers.policing_precinct}} the applicant's daily movements expose him / her to elevated risks of armed robbery, gang-related violence and residential robbery.",
            'KwaZulu-Natal' => "KwaZulu-Natal remains one of South Africa's most violent provinces, with recurring mass shootings and multiple-victim murder incidents continuing to claim the lives of three or more victims at a time. Inanda in KZN was among the police stations recording the highest number of murders in SAPS Q4 2024/25.\n\n".
                'The province also contributed 19.9% to the national rape total in the reporting period. In and around {{answers.policing_precinct}} the applicant faces elevated risks of residential robbery, hijacking and violent property crime.',
            'Eastern Cape' => "The Eastern Cape consistently records disproportionately high rates of violent crime relative to its population. SAPS Q4 2024/25 reported that Kwazakele in the Eastern Cape recorded the highest number of murders of any police precinct in the country, and the province continues to experience multiple-victim murder incidents and organised-criminal activity.\n\n".
                "In and around {{answers.policing_precinct}} the applicant's daily movements expose him / her to elevated and recurring risk of violent crime.",
            'Free State' => "The Free State records high rural and small-town violent-crime figures, with armed farm attacks, home invasions and vehicle hijackings a regular occurrence. SAPS Q4 2024/25 confirmed that despite the national decrease in murder, the Free State remains one of several provinces where double-digit violent-crime incidents persist.\n\n".
                "In and around {{answers.policing_precinct}}, the applicant's circumstances place him / her at material and recurring risk.",
            'Mpumalanga' => 'Mpumalanga suffers persistently high rates of armed robbery, hijacking and residential burglary, with specific hotspots along major travel routes. The applicant lives and works in {{answers.policing_precinct}} and is routinely exposed to these risks in both professional and personal movements.',
            'Limpopo' => 'Limpopo records elevated rates of farm attacks, residential robbery and hijackings, particularly along the N1 corridor and in the rural districts the applicant frequents. The applicant lives and works in {{answers.policing_precinct}} and is routinely exposed to these risks.',
            'North West' => "The North West province records high rates of residential robbery, vehicle hijacking and violent property crime, with specific hotspots along the applicant's regular travel routes through {{answers.policing_precinct}}.",
            'Northern Cape' => "The Northern Cape, despite its lower population density, suffers from persistently high rates of armed robbery and violent assault. SAPS Q4 2024/25 specifically identified the Northern Cape as having recorded an increase in murder incidents despite its small 2% contribution to the national total, confirming that violent crime remains a reality in the applicant's immediate environment in and around {{answers.policing_precinct}}.",
        ];

        $blocks = [];
        foreach ($drafts as $province => $body) {
            $blocks[] = [
                'kind' => 'text',
                'settings' => ['body' => $body],
                'condition' => ['field' => 'answers.province', 'operator' => 'equals', 'value' => $province],
            ];
        }

        return $blocks;
    }

    private function renewalAllCategories(): array
    {
        return [
            'slug' => 'renewal-all-categories',
            'name' => 'Renewal: all categories',
            'category' => 'renewal',
            'description' => 'Renewal of an existing firearm licence (section 13, 15 or 16).',
            'has_motivation' => true,
            'sections' => array_merge(
                [
                    $this->firearmsSection('Please list the firearm(s) you are renewing.'),
                    [
                        'key' => 'section_of_act',
                        'title' => 'Section of the Act',
                        'fields' => [
                            ['name' => 'section', 'label' => 'The firearm is licensed under:', 'type' => 'radio', 'required' => true, 'options' => [
                                'Section 13 — Self defence',
                                'Section 15 — Occasional hunting / sport shooting',
                                'Section 16 — Dedicated hunting / sport shooting',
                            ]],
                            ['name' => 'closest_police_station', 'label' => 'Closest police station', 'type' => 'text', 'required' => true],
                        ],
                    ],
                    [
                        'key' => 'dedicated_status',
                        'title' => 'Dedicated status (section 15 / 16)',
                        'condition' => ['field' => 'answers.section', 'operator' => 'in', 'value' => 'Section 15 — Occasional hunting / sport shooting, Section 16 — Dedicated hunting / sport shooting'],
                        'fields' => [
                            ['name' => 'dedicated_status_type', 'label' => 'Status held', 'type' => 'radio', 'options' => ['Dedicated hunter', 'Dedicated sport shooter', 'Occasional']],
                        ],
                    ],
                ],
                array_map(
                    fn ($section) => ($section['condition'] ?? null) === null
                        ? array_merge($section, ['condition' => ['field' => 'answers.section', 'operator' => 'equals', 'value' => 'Section 13 — Self defence']])
                        : $section,
                    $this->selfDefenceYesLists(),
                ),
            ),
            'requirements' => $this->commonRequirements([
                ['kind' => 'sport_proof', 'label' => 'Sport shooting proof (for renewals of a sport firearm)', 'required' => false, 'condition' => ['field' => 'answers.section', 'operator' => 'in', 'value' => 'Section 15 — Occasional hunting / sport shooting, Section 16 — Dedicated hunting / sport shooting']],
                ['kind' => 'hunting_proof', 'label' => 'Hunting proof (for renewals of a hunting firearm)', 'required' => false, 'condition' => ['field' => 'answers.section', 'operator' => 'in', 'value' => 'Section 15 — Occasional hunting / sport shooting, Section 16 — Dedicated hunting / sport shooting']],
                ['kind' => DocumentKinds::REFERENCE, 'label' => '3× Reference letters (if competency is also being renewed)', 'required' => false, 'min_count' => 3],
            ]),
            'blocks' => $this->motivationBlocks('Renewal motivation', 'Applicant: {{profile.full_name}}', [
                'The applicant hereby applies for the renewal of the firearm licence(s) listed below in terms of the Firearms Control Act.',
                'The firearm is licensed under {{answers.section}}.',
            ]),
        ];
    }

    private function renewalNoMotivation(): array
    {
        return [
            'slug' => 'renewal-no-motivation',
            'name' => 'Renewal: no motivation required',
            'category' => 'renewal',
            'description' => 'For DFOs that no longer require a motivation on renewal — produces the document pack only.',
            'has_motivation' => false,
            'sections' => [
                $this->firearmsSection('Please list every firearm being renewed.'),
            ],
            'requirements' => array_merge(
                $this->commonRequirements([], endorsement: false),
                [
                    ['kind' => DocumentKinds::REFERENCE, 'label' => '3× Reference letters', 'required' => true, 'min_count' => 3, 'hint' => 'Download the reference template, have each referee complete and sign it in black pen.'],
                ],
            ),
            'blocks' => [
                ['kind' => 'title_page', 'settings' => ['title' => 'Firearm licence renewal — {{profile.full_name}}', 'subtitle' => 'Reference {{request.reference}}', 'body' => 'This pack contains the renewal application documents and references. No written motivation is included.']],
                ['kind' => 'document_insert', 'settings' => ['divider_title' => 'Supporting documents', 'kinds' => [
                    'id_document', 'proof_of_residence', 'current_licences', 'proficiency_certificate', 'pftc_results', 'saps_competency', 'safe_photos', 'membership_certificate', 'dedicated_certificate', DocumentKinds::REFERENCE, DocumentKinds::POWER_OF_ATTORNEY,
                ]]],
            ],
        ];
    }

    private function competency(): array
    {
        return [
            'slug' => 'competency',
            'name' => 'Competency application',
            'category' => 'competency',
            'description' => 'New, further (SAPS517a) or renewal (SAPS517g) of competency.',
            'has_motivation' => false,
            'sections' => [
                [
                    'key' => 'competency_type',
                    'title' => 'Competency application',
                    'fields' => [
                        ['name' => 'competency_type', 'label' => 'Type', 'type' => 'radio', 'required' => true, 'options' => ['New (SAPS517)', 'Further (SAPS517a)', 'Renewal (SAPS517g)']],
                    ],
                ],
            ],
            'requirements' => [
                ['kind' => DocumentKinds::POWER_OF_ATTORNEY, 'label' => 'Signed Power of Attorney', 'required' => true],
                ['kind' => 'id_document', 'label' => 'ID document', 'required' => true],
                ['kind' => 'proof_of_residence', 'label' => 'Proof of residence', 'required' => true, 'date_rule' => 'max_age_days', 'max_age_days' => 90],
                ['kind' => 'proficiency_certificate', 'label' => 'Proficiency / competency training certificate', 'required' => true],
                ['kind' => 'pftc_results', 'label' => 'PFTC Statement of Results', 'required' => true],
                ['kind' => 'saps_competency', 'label' => 'SAPS competency certificate (further / renewal only)', 'required' => false, 'has_reference_number' => true, 'condition' => ['field' => 'answers.competency_type', 'operator' => 'not_equals', 'value' => 'New (SAPS517)']],
                ['kind' => DocumentKinds::REFERENCE, 'label' => '3× Reference letters (renewal only)', 'required' => false, 'min_count' => 3, 'condition' => ['field' => 'answers.competency_type', 'operator' => 'equals', 'value' => 'Renewal (SAPS517g)']],
            ],
            'blocks' => [
                ['kind' => 'title_page', 'settings' => ['title' => 'Competency application — {{profile.full_name}}', 'subtitle' => 'Reference {{request.reference}}', 'body' => 'Competency type: {{answers.competency_type}}']],
                ['kind' => 'document_insert', 'settings' => ['divider_title' => 'Supporting documents', 'kinds' => [
                    'id_document', 'proof_of_residence', 'proficiency_certificate', 'pftc_results', 'saps_competency', DocumentKinds::REFERENCE, DocumentKinds::POWER_OF_ATTORNEY,
                ]]],
            ],
        ];
    }

    /**
     * Common framework: title page, applicant block, body paragraphs,
     * firearms table and standard annexures.
     *
     * @param  array<int, string>  $paragraphs
     */
    private function motivationBlocks(string $title, string $subtitle, array $paragraphs): array
    {
        return [
            ['kind' => 'title_page', 'settings' => ['title' => $title, 'subtitle' => $subtitle, 'body' => "Prepared by Ranyati Firearm Motivations (Pty) Ltd\nReference: {{request.reference}}\nDate: {{today}}"]],
            ['kind' => 'heading', 'settings' => ['text' => 'Applicant particulars', 'level' => 2]],
            ['kind' => 'text', 'settings' => ['body' => "Name: {{profile.full_name}}\nID number: {{profile.id_number}}\nResidential address: {{profile.physical_address}}\nOccupation: {{profile.occupation}}\nContact: {{profile.cell}} / {{profile.email}}"]],
            ['kind' => 'heading', 'settings' => ['text' => 'Motivation', 'level' => 2]],
            ['kind' => 'text', 'settings' => ['body' => implode("\n\n", $paragraphs)]],
            ['kind' => 'firearms_table', 'settings' => ['title' => 'Firearm details']],
            ['kind' => 'document_insert', 'settings' => ['divider_title' => 'Supporting documents', 'kinds' => [
                'id_document', 'proof_of_residence', 'current_licences', 'proficiency_certificate', 'pftc_results', 'saps_competency',
                'safe_photos', 'membership_certificate', 'dedicated_certificate', 'sport_proof', 'hunting_proof', 'case_numbers', 'crime_clippings',
                DocumentKinds::ENDORSEMENT, DocumentKinds::POWER_OF_ATTORNEY,
            ]]],
        ];
    }
}
