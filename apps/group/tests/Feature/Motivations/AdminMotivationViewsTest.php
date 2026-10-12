<?php

namespace Tests\Feature\Motivations;

use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * View-render regression suite for the admin motivation UI.
 *
 * Our Feature\Motivations tests already cover every state transition at
 * the controller level, but they POST to action endpoints — they never
 * GET `/admin/motivation-requests/{id}` or `/admin/motivation-requests/{id}/edit`
 * to render the Blade templates themselves. A real Blade syntax error
 * (unterminated `@php(...)` form, missing `@endif`, etc.) can therefore
 * slip through the controller tests and crash the admin UI in production.
 *
 * This test harness just GETs the admin pages at every status in the
 * lifecycle and asserts a 200 response, so any Blade regression surfaces
 * immediately in CI.
 */
class AdminMotivationViewsTest extends TestCase
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
        $u = User::create([
            'name' => 'Admin',
            'email' => 'admin-'.uniqid().'@ranyati.test',
            'password' => 'secret12',
            'role' => User::ROLE_DEVELOPER,
        ]);
        $u->forceFill(['email_verified_at' => now()])->save();

        return $u;
    }

    private function member(): User
    {
        $u = User::create([
            'name' => 'Member',
            'email' => 'member-'.uniqid().'@example.com',
            'password' => 'secret12',
            'role' => User::ROLE_CLIENT,
        ]);
        $u->forceFill(['email_verified_at' => now()])->save();
        ClientProfile::create([
            'user_id' => $u->id,
            'full_names' => 'Member',
            'surname' => 'Example',
            'id_number' => '9001011234080',
            'physical_address' => '1 Test Rd',
            'occupation' => 'Developer',
            'cell' => '0821234567',
            'email' => $u->email,
        ]);

        return $u;
    }

    private function simpleType(): ApplicationType
    {
        return ApplicationType::firstOrCreate(
            ['slug' => 'admin-view-regression'],
            [
                'name' => 'Regression type',
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
     * @return array<string, array{0: string}>
     */
    public static function allStatuses(): array
    {
        return [
            'draft' => [MotivationRequest::STATUS_DRAFT],
            'submitted' => [MotivationRequest::STATUS_SUBMITTED],
            'changes_requested' => [MotivationRequest::STATUS_CHANGES_REQUESTED],
            'verified' => [MotivationRequest::STATUS_VERIFIED],
            'awaiting_endorsement' => [MotivationRequest::STATUS_AWAITING_ENDORSEMENT],
            'ready_to_print' => [MotivationRequest::STATUS_READY_TO_PRINT],
            'printed' => [MotivationRequest::STATUS_PRINTED],
            'invoiced' => [MotivationRequest::STATUS_INVOICED],
            'paid' => [MotivationRequest::STATUS_PAID],
            'ready_for_collection' => [MotivationRequest::STATUS_READY_FOR_COLLECTION],
            'shipped' => [MotivationRequest::STATUS_SHIPPED],
            'completed' => [MotivationRequest::STATUS_COMPLETED],
        ];
    }

    #[DataProvider('allStatuses')]
    public function test_admin_show_page_renders_at_every_status(string $status): void
    {
        $admin = $this->admin();
        $member = $this->member();
        $type = $this->simpleType();

        $motivation = $member->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => $status,
            'answers' => ['why' => 'because'],
            'delivery_method' => MotivationRequest::DELIVERY_COLLECT,
            'invoice_total' => 450.00,
            'payment_method' => 'eft',
            'payment_reference' => 'FNB123',
            'courier' => 'Courier Guy',
            'waybill_number' => 'CG-9999',
            'submitted_at' => now()->subDays(5),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.motivation-requests.show', $motivation));

        $response->assertOk();
        $response->assertSee('RM-'); // Reference always rendered
        $response->assertSee('Member'); // Member card heading
        $response->assertSee('Status'); // Status card heading
        $response->assertSee('Documents'); // Documents card heading
        $response->assertSee('Actions'); // Actions card heading
    }

    public function test_admin_edit_on_behalf_page_renders(): void
    {
        $admin = $this->admin();
        $member = $this->member();
        $type = $this->simpleType();

        $motivation = $member->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => [],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.motivation-requests.edit', $motivation))
            ->assertOk()
            ->assertSee('On behalf of');
    }

    public function test_admin_fulfilment_card_appears_once_invoice_eligible(): void
    {
        // Specifically guards the fulfilment UI, which was invisible for
        // weeks because of a Blade syntax error in the show page.
        $admin = $this->admin();
        $member = $this->member();
        $type = $this->simpleType();

        $motivation = $member->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_PRINTED,
            'answers' => ['why' => 'because'],
            'delivery_method' => MotivationRequest::DELIVERY_COLLECT,
            'submitted_at' => now()->subDays(3),
            'printed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.motivation-requests.show', $motivation))
            ->assertOk()
            ->assertSee('Fulfilment')
            ->assertSee('Upload invoice')
            ->assertSee('Record payment');
    }

    /**
     * Guards against the Blade `@php(short)` + `@php...@endphp` (block)
     * mix bug which collapsed the member profile pages — the non-greedy
     * `storePhpBlocks` regex grabs from the short form to the first
     * `@endphp` and swallows every directive in between into one
     * malformed `<?php(... ?>` block.
     */
    public function test_admin_member_pages_render(): void
    {
        $admin = $this->admin();
        $member = $this->member();

        $this->actingAs($admin)
            ->get(route('admin.motivation-members.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.motivation-members.show', $member))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.motivation-members.profile.edit', $member))
            ->assertOk()
            ->assertSee('Personal particulars');

        $this->actingAs($admin)
            ->get(route('admin.motivation-members.create'))
            ->assertOk()
            ->assertSee('Personal particulars');
    }

    public function test_member_motivation_pages_render(): void
    {
        $member = $this->member();
        $type = $this->simpleType();

        $this->actingAs($member)
            ->get(route('account.motivations.index'))
            ->assertOk();

        $this->actingAs($member)
            ->get(route('account.profile.edit'))
            ->assertOk();

        $this->actingAs($member)
            ->get(route('account.motivations.create'))
            ->assertOk();

        $motivation = $member->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => [],
        ]);

        $this->actingAs($member)
            ->get(route('account.motivations.edit', $motivation))
            ->assertOk();

        $this->actingAs($member)
            ->get(route('account.motivations.show', $motivation))
            ->assertOk();
    }
}
