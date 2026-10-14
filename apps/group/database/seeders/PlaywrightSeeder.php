<?php

namespace Database\Seeders;

use App\Enums\MotivationStatus;
use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\FirearmApplication;
use App\Models\MotivationEnquiry;
use App\Models\MotivationRequest;
use App\Models\MotivationTemplate;
use App\Models\MotivationTemplateVersion;
use App\Models\User;
use App\Support\Motivations\Templates\BlockKinds;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

/**
 * Fixture set for the Playwright Phase 1 regression spec.
 *
 * Every test in `playwright/phase1.spec.ts` relies on specific pieces of
 * state existing in the DB: an admin that can log in, at least one
 * enquiry so the admin/enquiries table isn't rendered as the empty
 * state, a motivation request at the Verified status with no generated
 * letters (bug 2), a FirearmApplication with a reference that is too
 * short to batch (bug 3), and a client-role user so motivation-members
 * has something to show.
 *
 * Keep this seeder self-contained — don't `->call()` into the real
 * production seeders, because the Playwright DB intentionally stays
 * small so suites run fast and the fixture state is predictable.
 */
class PlaywrightSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'playwright-admin@ranyati.test';

    public const ADMIN_PASSWORD = 'playwright-admin-pass';

    public const MEMBER_EMAIL = 'playwright-member@ranyati.test';

    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => 'Admin McTest',
                'password' => self::ADMIN_PASSWORD,
                'role' => User::ROLE_DEVELOPER,
            ],
        );
        // Explicitly mark verified — the login flow in this app doesn't
        // gate on email_verified_at for admins, but we set it anyway so
        // the fixture matches a real production admin.
        $admin->forceFill(['email_verified_at' => now()])->save();

        $member = User::updateOrCreate(
            ['email' => self::MEMBER_EMAIL],
            [
                'name' => 'Playwright Member',
                'password' => 'playwright-member-pass',
                'role' => User::ROLE_CLIENT,
            ],
        );
        $member->forceFill(['email_verified_at' => now()])->save();

        ClientProfile::updateOrCreate(
            ['user_id' => $member->id],
            [
                'full_names' => 'Playwright',
                'surname' => 'Member',
                'id_number' => '9001011234080',
                'physical_address' => '1 Playwright Street, Pretoria',
                'occupation' => 'Firearms instructor',
                'cell' => '0821234567',
                'email' => $member->email,
            ],
        );

        // The simplest application type the show page can render — a
        // single-section questionnaire with one required field and one
        // required document kind. Big enough to exercise the Documents
        // empty-state (bug 6) and the Generate/Regenerate button toggle
        // (bug 2); small enough that the fixture doesn't drag in the
        // full MotivationTypesSeeder.
        $type = ApplicationType::updateOrCreate(
            ['slug' => 'playwright-type'],
            [
                'name' => 'Playwright application type',
                'category' => 'sport',
                'is_active' => true,
                'has_motivation' => true,
                'questions' => [[
                    'key' => 'main',
                    'title' => 'Main',
                    'fields' => [
                        ['name' => 'why', 'label' => 'Why', 'type' => 'text', 'required' => true],
                    ],
                ]],
                'document_requirements' => [
                    ['kind' => 'id_document', 'label' => 'ID', 'required' => true, 'min_count' => 1],
                ],
            ],
        );

        // Verified request with no generated book / letters — this is
        // the state where bug 2 was most visible (the only action
        // available was "Regenerate motivations" even though nothing
        // had been generated).
        MotivationRequest::updateOrCreate(
            [
                'user_id' => $member->id,
                'application_type_id' => $type->id,
            ],
            [
                'status' => MotivationRequest::STATUS_VERIFIED,
                'answers' => ['why' => 'For target shooting at an accredited range.'],
                'delivery_method' => MotivationRequest::DELIVERY_COLLECT,
                'submitted_at' => now()->subDays(5),
                'verified_at' => now()->subDay(),
                'verified_by' => $admin->id,
            ],
        );

        // Public enquiry so admin/enquiries has at least one row — the
        // bug 1 fix (dropping the View column, wrapping in .table-wrap)
        // only shows up once there's a row to render.
        MotivationEnquiry::updateOrCreate(
            ['email' => 'enquirer@example.com'],
            [
                'name' => 'Jane Enquirer With An Unusually Long Name That Should Clamp',
                'phone' => '0821112222',
                'endorsement_type' => 'New application',
                'saps_station' => 'Pretoria North',
                'purpose' => 'Sport shooting',
                'services' => [],
                'source' => 'website',
                'status' => MotivationEnquiry::STATUS_NEW,
            ],
        );

        // Short-reference tracked application: digits-only strip is
        // "AB" → length 2, which the Batches query (LENGTH ≥ 4) drops.
        // Phase 1 fix surfaces this as an "Unbatched" row.
        FirearmApplication::updateOrCreate(
            [
                'user_id' => $member->id,
                'reference_number' => 'AB',
                'serial_number' => 'PLAYWRIGHT-SN-1',
            ],
            [
                'label' => 'Short-ref sample (Playwright)',
                'monitoring_enabled' => true,
            ],
        );

        $this->seedPhase2Fixtures($admin, $type);
        $this->seedCanvasTemplate($admin, $type);
    }

    /**
     * A 12-section canvas template used by the builder Playwright spec.
     * Headings are NATO-phonetic so the drag-from-3-to-10 test can
     * assert order by readable name rather than UUID.
     */
    private function seedCanvasTemplate(User $admin, ApplicationType $type): void
    {
        $names = ['Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo', 'Foxtrot', 'Golf', 'Hotel', 'India', 'Juliet', 'Kilo', 'Lima'];
        $blocks = [];
        foreach ($names as $name) {
            $body = $name === 'Alpha'
                ? '<p>Applicant {{profile.full_name}} lives at {{profile.physical_address}}.</p>'
                : '<p>Body of '.$name.'.</p>';
            $blocks[] = [
                'id' => (string) Str::uuid(),
                'kind' => BlockKinds::SECTION,
                'settings' => [
                    'heading' => $name,
                    'level' => 2,
                    'html' => $body,
                ],
                'condition' => null,
            ];
        }

        $template = MotivationTemplate::updateOrCreate(
            ['slug' => 'playwright-canvas'],
            [
                'name' => 'Playwright canvas',
                'category' => 'sport',
                'description' => 'Seeded fixture for the canvas builder Playwright spec.',
                'status' => MotivationTemplate::STATUS_DRAFT,
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ],
        );

        $draft = $template->draftVersion();
        if ($draft) {
            $draft->update([
                'blocks' => $blocks,
                'settings' => MotivationTemplateVersion::defaultSettings(),
                'updated_by' => $admin->id,
            ]);
        } else {
            MotivationTemplateVersion::create([
                'template_id' => $template->id,
                'version' => 1,
                'blocks' => $blocks,
                'settings' => MotivationTemplateVersion::defaultSettings(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]);
        }

        $type->templates()->syncWithoutDetaching([
            $template->id => ['is_default' => true, 'sort' => 0, 'condition' => null],
        ]);
    }

    /**
     * Phase 2 adds the dashboard, the 7-phase list and the global search.
     * Each of those surfaces needs enough variety to tell apart: at least
     * one motivation per phase, a handful of "needs admin action" rows,
     * a completed-in-last-30-days row (for the pipeline card's special
     * case), and a couple more enquiries so the unread/read KPIs read
     * sensibly.
     */
    private function seedPhase2Fixtures(User $admin, ApplicationType $type): void
    {
        // Second application type so the list's type filter has
        // something non-trivial to pick between.
        $secondaryType = ApplicationType::updateOrCreate(
            ['slug' => 'playwright-type-hunting'],
            [
                'name' => 'Playwright hunting type',
                'category' => 'hunting',
                'is_active' => true,
                'has_motivation' => true,
                'questions' => [[
                    'key' => 'main',
                    'title' => 'Main',
                    'fields' => [
                        ['name' => 'why', 'label' => 'Why', 'type' => 'text', 'required' => true],
                    ],
                ]],
                'document_requirements' => [],
            ],
        );

        // Spread a motivation across every status except VERIFIED (which
        // the Phase 1 fixture already covers). The specific statuses
        // chosen give the dashboard pipeline card at least one row per
        // phase and the Needs-action table 3-4 rows.
        $statusesToSeed = [
            MotivationStatus::DRAFT->value => ['days' => 1, 'type' => $type->id],
            MotivationStatus::SUBMITTED->value => ['days' => 2, 'type' => $type->id],
            MotivationStatus::CHANGES_REQUESTED->value => ['days' => 4, 'type' => $type->id],
            MotivationStatus::AWAITING_ENDORSEMENT->value => ['days' => 6, 'type' => $type->id],
            MotivationStatus::READY_TO_PRINT->value => ['days' => 1, 'type' => $secondaryType->id],
            MotivationStatus::PRINTED->value => ['days' => 2, 'type' => $secondaryType->id],
            MotivationStatus::INVOICED->value => ['days' => 3, 'type' => $secondaryType->id],
            MotivationStatus::PAID->value => ['days' => 7, 'type' => $secondaryType->id],
            MotivationStatus::READY_FOR_COLLECTION->value => ['days' => 1, 'type' => $type->id],
            MotivationStatus::SHIPPED->value => ['days' => 2, 'type' => $type->id],
            MotivationStatus::COMPLETED->value => ['days' => 10, 'type' => $type->id],
        ];

        $index = 1;
        foreach ($statusesToSeed as $status => $meta) {
            $email = 'playwright-'.strtolower(str_replace('_', '-', $status)).'@ranyati.test';
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Member '.ucwords(str_replace('_', ' ', $status)),
                    'password' => 'playwright-pass',
                    'role' => User::ROLE_CLIENT,
                ],
            );
            $user->forceFill(['email_verified_at' => now()])->save();

            ClientProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_names' => 'Member',
                    'surname' => ucwords(str_replace('_', ' ', $status)),
                    'id_number' => '85010100000'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                    'physical_address' => "{$index} Fixture Street, Pretoria",
                    'occupation' => 'Fixture occupation',
                    'cell' => '082100'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                    'email' => $email,
                ],
            );

            MotivationRequest::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'application_type_id' => $meta['type'],
                ],
                [
                    'status' => $status,
                    'answers' => ['why' => 'Seeded Phase 2 fixture for status '.$status.'.'],
                    'delivery_method' => MotivationRequest::DELIVERY_COLLECT,
                    'submitted_at' => now()->subDays($meta['days'] + 1),
                    // updated_at drives the "in stage" age — set it so
                    // we end up with a mix of grey / amber / red
                    // waiting pills in the dashboard table.
                    'updated_at' => now()->subDays($meta['days']),
                ],
            );
            $index++;
        }

        // Second enquiry — read, so the unread/read split on the
        // dashboard KPI tile has at least one of each.
        MotivationEnquiry::updateOrCreate(
            ['email' => 'returning-visitor@example.com'],
            [
                'name' => 'Returning Visitor',
                'phone' => '0823334444',
                'endorsement_type' => 'Renewal',
                'saps_station' => 'Centurion',
                'purpose' => 'Hunting',
                'services' => [],
                'source' => 'website',
                'status' => MotivationEnquiry::STATUS_IN_PROGRESS,
                'read_at' => now()->subDay(),
            ],
        );
    }
}
