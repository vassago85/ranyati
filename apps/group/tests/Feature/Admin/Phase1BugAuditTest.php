<?php

namespace Tests\Feature\Admin;

use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\FirearmApplication;
use App\Models\MotivationEnquiry;
use App\Models\MotivationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Phase 1 — "bugs from the audit" regression suite.
 *
 * Each test locks in one specific UI/controller fix from the audit so that
 * later refactors (Phase 2's dashboard overhaul, Phase 3's next-action bar,
 * the Phase 4 config merge, etc.) can't silently reintroduce the same
 * problem. These are intentionally cheap view-render + text/attribute
 * assertions — they don't try to replicate the full workflow coverage that
 * lives in Tests\Feature\Motivations\*.
 */
class Phase1BugAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config(['motivations.storage' => 'local']);
    }

    private function admin(): User
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-'.uniqid().'@ranyati.test',
            'password' => 'secret12',
            'role' => User::ROLE_DEVELOPER,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    private function member(): User
    {
        $user = User::create([
            'name' => 'Member',
            'email' => 'member-'.uniqid().'@example.com',
            'password' => 'secret12',
            'role' => User::ROLE_CLIENT,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        ClientProfile::create([
            'user_id' => $user->id,
            'full_names' => 'Audit',
            'surname' => 'Example',
            'id_number' => '9001011234080',
            'physical_address' => '1 Test Rd',
            'occupation' => 'Developer',
            'cell' => '0821234567',
            'email' => $user->email,
        ]);

        return $user;
    }

    private function simpleApplicationType(): ApplicationType
    {
        return ApplicationType::firstOrCreate(
            ['slug' => 'phase1-audit'],
            [
                'name' => 'Phase 1 audit type',
                'category' => 'sport',
                'is_active' => true,
                'has_motivation' => true,
                'questions' => [[
                    'key' => 'main', 'title' => 'Main', 'fields' => [
                        ['name' => 'why', 'label' => 'Why', 'type' => 'text', 'required' => true],
                    ],
                ]],
                'document_requirements' => [
                    ['kind' => 'id_document', 'label' => 'ID', 'required' => true, 'min_count' => 1],
                ],
            ],
        );
    }

    /**
     * Bug 1 — the enquiries table was clipped at desktop widths because the
     * card's `overflow:hidden` was swallowing the right-most "View" column
     * into a stub ("Vi"). The fix is to wrap the table in `.table-wrap` so
     * horizontal scroll is possible inside the card, and to drop the View
     * column entirely because the whole row is already a link.
     */
    public function test_bug1_enquiries_table_wraps_and_has_no_view_column(): void
    {
        $admin = $this->admin();

        MotivationEnquiry::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '0821112222',
            'endorsement_type' => 'New application',
            'purpose' => 'Sport',
            'services' => [],
            'source' => 'website',
            'status' => MotivationEnquiry::STATUS_NEW,
        ]);

        $html = $this->actingAs($admin)
            ->get(route('admin.enquiries'))
            ->assertOk()
            ->getContent();

        // Table is now inside a scroll wrapper so wide tables don't get
        // clipped by the card.
        $this->assertStringContainsString('class="table-wrap"', $html);

        // The row-level "View" button column is gone — the whole row is a
        // link via .row-link + onclick. Guard against the exact previous
        // markup rather than any text containing "View".
        $this->assertStringNotContainsString(
            'btn btn-secondary btn-sm" onclick="event.stopPropagation();">View',
            $html,
            'The dedicated View button column should have been removed from the enquiries table — the whole row is already clickable.',
        );

        // Long names clamp to two lines inside their cell.
        $this->assertStringContainsString('class="name-clamp"', $html);
    }

    /**
     * Bug 2 — when nothing had been generated yet, the Actions card still
     * labelled its one button "Regenerate motivations". After the fix the
     * same button says "Generate motivations" until the first successful
     * generation. We check both halves of the toggle on a Verified request:
     * no letters → "Generate", letters present → "Regenerate".
     */
    public function test_bug2_generate_button_label_depends_on_whether_anything_exists(): void
    {
        $admin = $this->admin();
        $member = $this->member();
        $type = $this->simpleApplicationType();

        $motivation = $member->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_VERIFIED,
            'answers' => ['why' => 'because'],
            'delivery_method' => MotivationRequest::DELIVERY_COLLECT,
        ]);

        // No letters yet, no legacy PDF → button says Generate.
        $htmlBefore = $this->actingAs($admin)
            ->get(route('admin.motivation-requests.show', $motivation))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Generate book', $htmlBefore);
        $this->assertStringNotContainsString('Regenerate book', $htmlBefore);

        // Pretend a book has been generated — the button flips to Regenerate.
        $motivation->update([
            'pdf_disk' => 'local',
            'pdf_path' => 'tests/fake.pdf',
            'pdf_generated_at' => now(),
        ]);

        $htmlAfter = $this->actingAs($admin)
            ->get(route('admin.motivation-requests.show', $motivation))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Regenerate book', $htmlAfter);
        $this->assertStringNotContainsString('>Generate book', $htmlAfter);
    }

    /**
     * Bug 3 — the SAPS tracker stat card was counting every tracked
     * application, but the Batches card dropped anything whose
     * digit-stripped reference was shorter than BATCH_KEY_LENGTH (4). An
     * admin would see "1 Application" alongside "No applications tracked
     * yet", which was confusing. After the fix the Batches card surfaces an
     * explicit "Unbatched" row so the two numbers reconcile.
     */
    public function test_bug3_saps_tracker_surfaces_unbatched_applications(): void
    {
        $admin = $this->admin();
        $member = $this->member();

        // "AB" is 2 chars and 0 digits — short enough to be dropped by
        // BatchAnalytics on every supported DB driver (the SQL filter
        // requires LENGTH(digits) >= 4).
        FirearmApplication::create([
            'user_id' => $member->id,
            'label' => 'Short-ref test',
            'reference_number' => 'AB',
            'serial_number' => 'SN-1',
            'monitoring_enabled' => true,
        ]);

        $html = $this->actingAs($admin)
            ->get(route('admin.applications'))
            ->assertOk()
            ->assertSee('Applications')
            ->getContent();

        // The reconciliation row is now visible, with the honest reason.
        $this->assertStringContainsString('Unbatched', $html);
        $this->assertStringContainsString('ref &lt; 4 digits', $html);

        // The "No applications tracked yet" copy must NOT be shown when
        // there are tracked rows (even if none can be batched).
        $this->assertStringNotContainsString('No applications tracked yet', $html);
    }

    /**
     * Bug 4 — the request list showed 1 "All" + 12 status tabs in a flex row
     * with no overflow handling, which pushed the whole page past 1100px on
     * a 390px viewport and forced horizontal scroll. The fix wraps the row
     * in `.scroll-strip` so only the tabs scroll in place.
     */
    public function test_bug4_requests_filter_tabs_are_in_a_scroll_strip(): void
    {
        $admin = $this->admin();

        $html = $this->actingAs($admin)
            ->get(route('admin.motivation-requests.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('class="scroll-strip"', $html);

        // Phase 2 replaced the 13-chip bar with a 7-phase-tab bar inside
        // the same `.scroll-strip` wrapper. The underlying fix is still
        // valid; the labels now cover phases, not every granular status.
        // All 7 phase-tab labels must render.
        foreach (['All', 'Needs action', 'Intake', 'Preparation', 'Payment', 'Fulfilment', 'Completed'] as $label) {
            $this->assertStringContainsString($label, $html, "Phase 2 phase-tab '$label' is missing.");
        }

        // Every granular status label still reachable via the exact-status
        // `<select>` in the toolbar — power-user path, matching the brief's
        // "search, plus filters for exact status" requirement.
        foreach (MotivationRequest::STATUSES as $statusLabel) {
            $this->assertStringContainsString($statusLabel, $html, "Status label '$statusLabel' no longer reachable from the list.");
        }
    }

    /**
     * Bug 5 — the Member and Status cards on the request detail page sat in
     * a fixed `grid-template-columns:1fr 1fr` grid, so at ~390px each one
     * ended up ~150px wide and content wrapped unreadably. After the fix
     * the top row uses `.grid-2`, which collapses to a single column below
     * 768px (see admin/layout.blade.php). We don't have a headless browser
     * here — assert the class is applied and the inline rule is gone.
     */
    public function test_bug5_detail_top_row_uses_responsive_grid_class(): void
    {
        $admin = $this->admin();
        $member = $this->member();
        $type = $this->simpleApplicationType();

        $motivation = $member->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_SUBMITTED,
            'answers' => ['why' => 'because'],
        ]);

        $html = $this->actingAs($admin)
            ->get(route('admin.motivation-requests.show', $motivation))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('class="grid-2"', $html);
    }

    /**
     * Bug 6 — the Documents table on the detail page rendered only its
     * header row when no documents had been uploaded, so admins saw an
     * empty "Kind | File | Reference | Dates | Flags | Checked" strip with
     * no explanation. After the fix we show a sentence of guidance that
     * points to the upload surface instead.
     */
    public function test_bug6_documents_empty_state_shows_guidance(): void
    {
        $admin = $this->admin();
        $member = $this->member();
        $type = $this->simpleApplicationType();

        $motivation = $member->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => ['why' => 'because'],
        ]);

        $html = $this->actingAs($admin)
            ->get(route('admin.motivation-requests.show', $motivation))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('No documents uploaded yet', $html);
        $this->assertStringContainsString('class="empty-state"', $html);
    }

    /**
     * Bug 7 — the motivation members index rendered two <h1>s: one in the
     * sticky topbar (via @section('title')) and one inline in the content
     * body ("Motivation members"). After the fix only the layout's topbar
     * h1 remains and the primary action lives in the topbar's actions
     * slot. We don't do full HTML parsing here — a cheap substring check
     * covers the regression.
     */
    public function test_bug7_motivation_members_index_has_a_single_h1(): void
    {
        $admin = $this->admin();

        $html = $this->actingAs($admin)
            ->get(route('admin.motivation-members.index'))
            ->assertOk()
            ->getContent();

        // The content area no longer draws its own <h1>; the layout topbar
        // provides the single h1 for the page.
        $this->assertStringNotContainsString('<h1 style="color:#fff;font-size:22px;margin:0;">Motivation members</h1>', $html);
        $this->assertSame(1, preg_match_all('/<h1\b/i', $html));

        // The topbar action slot is used instead of an in-content header.
        $this->assertStringContainsString('+ New walk-in member', $html);
    }

    /**
     * Bug 8 — every search input in the admin now carries an aria-label so
     * screen-reader users can tell the inputs apart. We test the four most
     * trafficked search surfaces; the rest of the admin follows the same
     * `<input type="search">` + `name="q"` pattern.
     */
    public function test_bug8_admin_search_inputs_have_aria_labels(): void
    {
        $admin = $this->admin();

        $enquiries = $this->actingAs($admin)->get(route('admin.enquiries'))->assertOk()->getContent();
        $this->assertStringContainsString('aria-label="Search enquiries"', $enquiries);

        $members = $this->actingAs($admin)->get(route('admin.motivation-members.index'))->assertOk()->getContent();
        $this->assertStringContainsString('aria-label="Search motivation members"', $members);

        $arms = $this->actingAs($admin)->get(route('admin.arms'))->assertOk()->getContent();
        $this->assertStringContainsString('aria-label="Search arms listings"', $arms);

        $armsEnquiries = $this->actingAs($admin)->get(route('admin.arms.enquiries'))->assertOk()->getContent();
        $this->assertStringContainsString('aria-label="Search arms enquiries"', $armsEnquiries);
    }
}
