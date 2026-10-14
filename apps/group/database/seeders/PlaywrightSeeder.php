<?php

namespace Database\Seeders;

use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\FirearmApplication;
use App\Models\MotivationEnquiry;
use App\Models\MotivationRequest;
use App\Models\User;
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
    }
}
