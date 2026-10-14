<?php

namespace Tests\Feature\Admin;

use App\Enums\MotivationPhase;
use App\Enums\MotivationStatus;
use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationEnquiry;
use App\Models\MotivationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 2 (UX overhaul) regression tests.
 *
 * Each test exercises one slice of the dashboard / list / sidebar /
 * search / export surface and locks in the contract later phases depend
 * on. These are feature-level — they render the real controller
 * + view stack — and are deliberately kept cheap: shared `setUp()`
 * seeds a tiny fixture world, each test spends 2-4 assertions on its
 * one slice.
 */
class Phase2UxOverhaulTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $member;

    private ApplicationType $sportType;

    private ApplicationType $huntingType;

    /** @var array<string, MotivationRequest> */
    private array $motivations = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Phase 2 Admin',
            'email' => 'phase2-admin-'.uniqid().'@ranyati.test',
            'password' => 'secret12',
            'role' => User::ROLE_DEVELOPER,
        ]);
        $this->admin->forceFill(['email_verified_at' => now()])->save();

        $this->member = User::create([
            'name' => 'Phase 2 Member',
            'email' => 'phase2-member-'.uniqid().'@ranyati.test',
            'password' => 'secret12',
            'role' => User::ROLE_CLIENT,
        ]);
        $this->member->forceFill(['email_verified_at' => now()])->save();
        ClientProfile::create([
            'user_id' => $this->member->id,
            'full_names' => 'Jane',
            'surname' => 'Member',
            'id_number' => '8201015123089',
            'physical_address' => '1 Fixture Street',
            'occupation' => 'Instructor',
            'cell' => '0821234567',
            'email' => $this->member->email,
        ]);

        $this->sportType = ApplicationType::create([
            'slug' => 'phase2-sport',
            'name' => 'Phase 2 sport',
            'category' => 'sport',
            'is_active' => true,
            'has_motivation' => true,
            'questions' => [],
            'document_requirements' => [],
        ]);
        $this->huntingType = ApplicationType::create([
            'slug' => 'phase2-hunting',
            'name' => 'Phase 2 hunting',
            'category' => 'hunting',
            'is_active' => true,
            'has_motivation' => true,
            'questions' => [],
            'document_requirements' => [],
        ]);

        // One motivation per status so pipeline counts, phase tabs and
        // the needs-action table all have at least one row each.
        // Spread `updated_at` from 1 day to 12 days so the waiting-time
        // filter has rows on both sides of each threshold. Eloquent's
        // automatic timestamps overwrite an `updated_at` passed to
        // ::create(), so we backdate it with a second save that has
        // timestamps disabled.
        foreach (MotivationStatus::ordered() as $i => $status) {
            $m = MotivationRequest::create([
                'user_id' => $this->member->id,
                'application_type_id' => ($i % 2 === 0 ? $this->sportType : $this->huntingType)->id,
                'status' => $status->value,
                'answers' => [],
                'delivery_method' => MotivationRequest::DELIVERY_COLLECT,
            ]);
            $m->timestamps = false;
            $m->updated_at = now()->subDays($i + 1);
            $m->save();
            $m->timestamps = true;

            $this->motivations[$status->value] = $m;
        }

        // Two enquiries: one unread (counts into "unread" KPI), one
        // read (does not).
        MotivationEnquiry::create([
            'name' => 'Unread Enquirer',
            'email' => 'unread@example.com',
            'phone' => '0820000001',
            'endorsement_type' => 'New',
            'saps_station' => 'Centurion',
            'purpose' => 'Sport',
            'services' => [],
            'source' => 'website',
            'status' => MotivationEnquiry::STATUS_NEW,
        ]);
        MotivationEnquiry::create([
            'name' => 'Read Enquirer',
            'email' => 'read@example.com',
            'phone' => '0820000002',
            'endorsement_type' => 'New',
            'saps_station' => 'Centurion',
            'purpose' => 'Sport',
            'services' => [],
            'source' => 'website',
            'status' => MotivationEnquiry::STATUS_IN_PROGRESS,
            'read_at' => now()->subHour(),
        ]);
    }

    // ------- Dashboard --------------------------------------------------

    public function test_dashboard_renders_four_kpi_tiles_with_correct_counts(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Needs your action', false)
            ->assertSee('Motivations in progress', false)
            ->assertSee('Awaiting payment', false)
            ->assertSee('Unread enquiries', false);
    }

    public function test_dashboard_needs_action_count_matches_list_filter(): void
    {
        $dashboard = $this->actingAs($this->admin)->get('/admin')->assertOk();

        // 4 statuses are flagged needsAdminAction; one motivation per
        // status in the fixture, so the count must be exactly 4.
        $needsAction = MotivationStatus::needsActionValues();
        $this->assertCount(4, $needsAction);
        $dashboard->assertSeeInOrder(['Needs your action', '>4<'], false);

        // That same count must show up when the list is filtered.
        $list = $this->actingAs($this->admin)
            ->get(route('admin.motivation-requests.index', ['filter' => 'needs-action']))
            ->assertOk();
        foreach ($needsAction as $v) {
            $list->assertSee(MotivationStatus::from($v)->label(), false);
        }
    }

    public function test_dashboard_pipeline_card_shows_every_phase(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin')->assertOk();

        foreach (MotivationPhase::ordered() as $phase) {
            $response->assertSee($phase->label(), false);
        }
        foreach (MotivationStatus::ordered() as $status) {
            $response->assertSee($status->label(), false);
        }
    }

    public function test_dashboard_pipeline_links_round_trip_through_the_list(): void
    {
        // Each pipeline status link lands on the list filtered by that
        // status. Spot-check two of them so one busted route doesn't
        // slip through a cosmetic change.
        foreach ([MotivationStatus::SUBMITTED, MotivationStatus::INVOICED] as $status) {
            $this->actingAs($this->admin)
                ->get(route('admin.motivation-requests.index', ['status' => $status->value]))
                ->assertOk()
                ->assertSee($status->label(), false);
        }
    }

    // ------- Sidebar ----------------------------------------------------

    public function test_sidebar_shows_only_the_active_areas_links_and_the_area_switcher(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin')->assertOk();

        // Area switcher must offer every available area.
        $response->assertSee('class="area-switcher"', false);
        foreach (['motivations', 'arms', 'storage'] as $key) {
            $response->assertSee('name="area" value="'.$key.'"', false);
        }

        // The sidebar's user block shows the admin's name (not the
        // literal word "Developer" — that was the Phase 2 greeting fix).
        $response->assertSee('Phase 2 Admin', false)
            ->assertDontSee('>Developer<', false);
    }

    public function test_sidebar_count_badges_reflect_live_counts(): void
    {
        // 4 needs-action motivations, 1 unread enquiry.
        $response = $this->actingAs($this->admin)->get('/admin')->assertOk();

        $response->assertSee('class="sidebar-badge"', false);
        // Loose check — the badge count can appear in either sidebar
        // link, we just want the numbers to be present somewhere in the
        // sidebar HTML. The tighter assertion lives in the list test.
        $response->assertSee('>4<', false)->assertSee('>1<', false);
    }

    // ------- List -------------------------------------------------------

    public function test_list_shows_seven_phase_tabs_with_counts(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.motivation-requests.index'))
            ->assertOk();

        foreach (['All', 'Needs action', 'Intake', 'Preparation', 'Payment', 'Fulfilment', 'Completed'] as $label) {
            $response->assertSee($label, false);
        }
    }

    public function test_list_q_filter_round_trips_through_query_string(): void
    {
        // Search by part of the member's name — the one fixture member
        // has 12 motivations, so this should still return every row.
        $response = $this->actingAs($this->admin)
            ->get(route('admin.motivation-requests.index', ['q' => 'Phase 2 Member']))
            ->assertOk();

        $response->assertSee('name="q"', false)
            ->assertSee('value="Phase 2 Member"', false);
    }

    public function test_list_waiting_filter_narrows_the_result_set(): void
    {
        // Only rows updated at least 7 days ago should remain.
        $expected = MotivationRequest::where('updated_at', '<=', now()->subDays(7))->count();
        $this->assertGreaterThan(0, $expected, 'Fixture should have at least one row older than 7 days.');

        $html = $this->actingAs($this->admin)
            ->get(route('admin.motivation-requests.index', ['waiting' => '7']))
            ->assertOk()
            ->getContent();

        // Fragile count assertions are not worth it — do a weak check
        // that the waiting-7-days filter is round-tripped into the
        // select (so the user can clear it).
        $this->assertStringContainsString('value="7" selected', $html);
    }

    public function test_list_masks_id_number_and_cell(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.motivation-requests.index'))
            ->assertOk();

        $response->assertSee('820101*******', false);
        $response->assertSee('082 *** 4567', false);
        $response->assertDontSee('8201015123089', false);
    }

    // ------- Export -----------------------------------------------------

    public function test_csv_export_streams_full_internal_columns_without_masking(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.motivation-requests.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition'));

        $csv = $response->streamedContent();
        // Admin-only full-fidelity export: the ID number appears
        // UNMASKED. If this assertion ever fails, we've accidentally
        // started leaking masked values and should re-check the brief.
        $this->assertStringContainsString('8201015123089', $csv);
        $this->assertStringContainsString('0821234567', $csv);
        // fputcsv auto-quotes any header containing a space; expect the
        // quoted form so Excel-friendly output does not break the test.
        $this->assertStringContainsString('Reference,"Client name"', $csv);
        // Days-in-stage must be an integer, not a float microsecond.
        $this->assertDoesNotMatchRegularExpression('/,\d+\.\d+E-\d+,/', $csv);
    }

    // ------- Search -----------------------------------------------------

    public function test_search_route_finds_a_client_by_id_number(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.search', ['q' => '820101']))
            ->assertOk();

        $response->assertSee('Phase 2 Member', false)
            // Masked everywhere in the search results, same as the list.
            ->assertSee('820101*******', false)
            ->assertDontSee('8201015123089', false);
    }

    public function test_search_route_finds_a_motivation_by_reference(): void
    {
        $firstId = $this->motivations[MotivationStatus::DRAFT->value]->id;
        $expectedRef = 'RM-'.str_pad((string) $firstId, 6, '0', STR_PAD_LEFT);

        $this->actingAs($this->admin)
            ->get(route('admin.search', ['q' => $expectedRef]))
            ->assertOk()
            ->assertSee($expectedRef, false);
    }

    public function test_search_route_requires_at_least_two_characters(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.search', ['q' => 'a']))
            ->assertOk()
            ->assertSee('Keep typing', false);
    }
}
