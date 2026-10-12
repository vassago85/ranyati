<?php

namespace Tests\Feature\Motivations;

use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationDocument;
use App\Models\MotivationRequest;
use App\Models\User;
use App\Support\Motivations\DocumentKinds;
use App\Support\Motivations\PreviousMotivationData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MotivationReuseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['motivations.storage' => 'local']);
    }

    private function user(): User
    {
        $u = User::create([
            'name' => 'Returning',
            'email' => 'returning-'.uniqid().'@example.com',
            'password' => 'secret12',
            'role' => User::ROLE_CLIENT,
        ]);
        $u->forceFill(['email_verified_at' => now()])->save();
        ClientProfile::create([
            'user_id' => $u->id,
            'full_names' => 'Jane', 'surname' => 'Doe',
            'id_number' => '9001011234080',
            'physical_address' => '1 Test Rd',
            'occupation' => 'Clerk', 'cell' => '0821234567',
            'email' => $u->email,
        ]);

        return $u;
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

    private function sportType(): ApplicationType
    {
        return ApplicationType::firstOrCreate(
            ['slug' => 'sport-reuse'],
            [
                'name' => 'Sport reuse',
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
                    ['kind' => 'proof_of_residence', 'label' => 'POR', 'required' => true, 'min_count' => 1, 'date_rule' => 'max_age_days', 'max_age_days' => 90],
                ],
            ],
        );
    }

    /**
     * Two types that both have a firearms_table, but ask different other
     * questions. Used to prove firearms carry across types while non-firearm
     * answers only carry same-type.
     */
    private function selfDefenceType(): ApplicationType
    {
        return ApplicationType::firstOrCreate(
            ['slug' => 'sd-reuse'],
            [
                'name' => 'Self-defence reuse',
                'category' => 'self_defence',
                'is_active' => true,
                'has_motivation' => true,
                'questions' => [[
                    'key' => 'main', 'title' => 'Main', 'fields' => [
                        ['name' => 'firearms', 'label' => 'Firearms', 'type' => 'firearms_table', 'required' => true],
                        ['name' => 'province', 'label' => 'Province', 'type' => 'text', 'required' => true],
                    ],
                ]],
                'document_requirements' => [
                    ['kind' => 'id_document', 'label' => 'ID', 'required' => true, 'min_count' => 1],
                ],
            ],
        );
    }

    private function huntingType(): ApplicationType
    {
        return ApplicationType::firstOrCreate(
            ['slug' => 'hunt-reuse'],
            [
                'name' => 'Hunting reuse',
                'category' => 'hunting',
                'is_active' => true,
                'has_motivation' => true,
                'questions' => [[
                    'key' => 'main', 'title' => 'Main', 'fields' => [
                        ['name' => 'firearms', 'label' => 'Firearms', 'type' => 'firearms_table', 'required' => true],
                        ['name' => 'species', 'label' => 'Species', 'type' => 'textarea', 'required' => true],
                    ],
                ]],
                'document_requirements' => [
                    ['kind' => 'id_document', 'label' => 'ID', 'required' => true, 'min_count' => 1],
                ],
            ],
        );
    }

    private function makeDoc(MotivationRequest $r, string $kind, ?string $issued = null, string $contents = '%PDF-1.4 body'): MotivationDocument
    {
        $path = 'motivations/'.$r->id.'/'.$kind.'-'.uniqid().'.pdf';
        Storage::disk('local')->put($path, $contents);

        return MotivationDocument::create([
            'motivation_request_id' => $r->id,
            'kind' => $kind,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $kind.'.pdf',
            'mime' => 'application/pdf',
            'size' => strlen($contents),
            'issued_on' => $issued,
            'uploaded_by' => $r->user_id,
        ]);
    }

    public function test_reuse_copies_answers_and_still_valid_documents_but_not_stale_ones(): void
    {
        $user = $this->user();
        $type = $this->sportType();

        $previous = $user->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_PRINTED,
            'answers' => ['why' => 'I compete in IPSC'],
            'submitted_at' => now()->subMonths(6),
        ]);
        $id = $this->makeDoc($previous, 'id_document', now()->subMonths(5)->toDateString(), '%PDF ID');
        $porStale = $this->makeDoc($previous, 'proof_of_residence', now()->subDays(120)->toDateString(), '%PDF POR');
        $this->makeDoc($previous, DocumentKinds::POWER_OF_ATTORNEY, null, '%PDF POA');
        $this->makeDoc($previous, DocumentKinds::ENDORSEMENT, null, '%PDF END');
        $this->makeDoc($previous, DocumentKinds::INVOICE, null, '%PDF INV');

        $target = $user->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => [],
        ]);

        $summary = PreviousMotivationData::applyTo($target);

        $target->refresh();
        $this->assertTrue($summary['answers_reused']);
        $this->assertSame('I compete in IPSC', $target->answers['why']);

        $this->assertEqualsCanonicalizing(['ID document'], $summary['documents_reused']);
        $this->assertContains('POR', $summary['stale_kinds']);

        $this->assertSame(1, $target->documents()->where('kind', 'id_document')->count());
        $this->assertSame(0, $target->documents()->where('kind', 'proof_of_residence')->count());
        $this->assertSame(0, $target->documents()->where('kind', DocumentKinds::POWER_OF_ATTORNEY)->count());
        $this->assertSame(0, $target->documents()->where('kind', DocumentKinds::ENDORSEMENT)->count());
        $this->assertSame(0, $target->documents()->where('kind', DocumentKinds::INVOICE)->count());
    }

    public function test_reused_document_is_a_fresh_file_not_a_shared_path(): void
    {
        $user = $this->user();
        $type = $this->sportType();

        $previous = $user->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_PRINTED,
            'answers' => [],
            'submitted_at' => now()->subMonths(2),
        ]);
        $source = $this->makeDoc($previous, 'id_document', now()->subMonths(2)->toDateString(), '%PDF original');

        $target = $user->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => [],
        ]);
        PreviousMotivationData::applyTo($target);

        $copy = $target->fresh()->documents()->where('kind', 'id_document')->first();
        $this->assertNotNull($copy);
        $this->assertNotSame($source->path, $copy->path);
        $this->assertSame('%PDF original', Storage::disk('local')->get($copy->path));

        // Deleting the copy does not nuke the original on disk.
        $copy->delete();
        $this->assertTrue(Storage::disk('local')->exists($source->path));
    }

    public function test_member_create_route_opts_into_reuse(): void
    {
        $user = $this->user();
        $type = $this->sportType();
        $previous = $user->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_PRINTED,
            'answers' => ['why' => 'Reuse me'],
            'submitted_at' => now()->subMonth(),
        ]);
        $this->makeDoc($previous, 'id_document', now()->subMonth()->toDateString());

        $this->withServerVariables(['HTTP_HOST' => 'motivations.ranyati.test'])
            ->actingAs($user)
            ->post('/account/motivations', [
                'application_type_id' => $type->id,
                'reuse_previous' => '1',
            ])
            ->assertRedirect();

        $target = MotivationRequest::where('user_id', $user->id)
            ->where('id', '!=', $previous->id)
            ->first();
        $this->assertNotNull($target);
        $this->assertSame('Reuse me', $target->answers['why']);
        $this->assertSame(1, $target->documents()->where('kind', 'id_document')->count());
    }

    public function test_admin_walkin_start_motivation_can_reuse_previous(): void
    {
        $admin = $this->admin();
        $user = $this->user();
        $type = $this->sportType();
        $previous = $user->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_PRINTED,
            'answers' => ['why' => 'Walk-in reuse'],
            'submitted_at' => now()->subMonth(),
        ]);
        $this->makeDoc($previous, 'id_document', now()->subMonth()->toDateString());

        $this->actingAs($admin)
            ->post('/admin/motivation-members/'.$user->id.'/motivations', [
                'application_type_id' => $type->id,
                'reuse_previous' => '1',
            ])
            ->assertRedirect();

        $target = MotivationRequest::where('user_id', $user->id)
            ->where('id', '!=', $previous->id)
            ->first();
        $this->assertSame('Walk-in reuse', $target->answers['why']);
        $this->assertSame(1, $target->documents()->count());
    }

    public function test_without_reuse_flag_nothing_is_carried_over(): void
    {
        $user = $this->user();
        $type = $this->sportType();
        $previous = $user->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_PRINTED,
            'answers' => ['why' => 'Not for carry'],
            'submitted_at' => now()->subMonth(),
        ]);
        $this->makeDoc($previous, 'id_document', now()->subMonth()->toDateString());

        $this->withServerVariables(['HTTP_HOST' => 'motivations.ranyati.test'])
            ->actingAs($user)
            ->post('/account/motivations', [
                'application_type_id' => $type->id,
                // No reuse_previous.
            ])
            ->assertRedirect();

        $target = MotivationRequest::where('user_id', $user->id)
            ->where('id', '!=', $previous->id)
            ->first();
        $this->assertSame([], $target->answers);
        $this->assertSame(0, $target->documents()->count());
    }

    public function test_firearms_table_carries_across_different_application_types(): void
    {
        // The same 9mm handgun is motivated under self-defence, then under
        // hunting. The hunting draft should auto-fill the firearms table
        // from the self-defence motivation, but the hunting-specific
        // "species" field should stay empty.
        $user = $this->user();
        $sdType = $this->selfDefenceType();
        $huntType = $this->huntingType();

        $firearmRows = [[
            'type' => 'Handgun',
            'calibre' => '9mm',
            'serial' => 'SD-9MM-123',
        ]];

        $user->motivationRequests()->create([
            'application_type_id' => $sdType->id,
            'status' => MotivationRequest::STATUS_PRINTED,
            'answers' => ['firearms' => $firearmRows, 'province' => 'Gauteng'],
            'submitted_at' => now()->subMonths(2),
        ]);

        $huntDraft = $user->motivationRequests()->create([
            'application_type_id' => $huntType->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => [],
        ]);

        $summary = PreviousMotivationData::applyTo($huntDraft);

        $huntDraft->refresh();
        $this->assertTrue($summary['firearms_reused']);
        $this->assertFalse($summary['answers_reused'], 'Non-firearm answers must not cross application types.');
        $this->assertSame($firearmRows, $huntDraft->answers['firearms']);
        // Province is a self-defence-only field — it must NOT appear on the
        // hunting draft just because a prior SD motivation had one.
        $this->assertArrayNotHasKey('province', $huntDraft->answers);
        $this->assertArrayNotHasKey('species', $huntDraft->answers);
    }

    public function test_non_firearm_answers_do_not_leak_across_types(): void
    {
        // Prior hunting motivation stored "species" and firearms. Starting
        // a self-defence draft after that should pull firearms across, but
        // leave "species" behind — self-defence doesn't use it.
        $user = $this->user();
        $sdType = $this->selfDefenceType();
        $huntType = $this->huntingType();

        $firearmRows = [[
            'type' => 'Rifle',
            'calibre' => '.308',
            'serial' => 'RIFLE-308',
        ]];

        $user->motivationRequests()->create([
            'application_type_id' => $huntType->id,
            'status' => MotivationRequest::STATUS_PRINTED,
            'answers' => ['firearms' => $firearmRows, 'species' => 'Kudu, blesbok, impala'],
            'submitted_at' => now()->subMonth(),
        ]);

        $sdDraft = $user->motivationRequests()->create([
            'application_type_id' => $sdType->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => [],
        ]);

        $summary = PreviousMotivationData::applyTo($sdDraft);

        $sdDraft->refresh();
        $this->assertTrue($summary['firearms_reused']);
        $this->assertFalse($summary['answers_reused']);
        $this->assertSame($firearmRows, $sdDraft->answers['firearms']);
        $this->assertArrayNotHasKey('species', $sdDraft->answers);
    }

    public function test_same_type_answers_still_win_over_cross_type_firearms(): void
    {
        // If the member has both a prior same-type motivation *and* a
        // different-type motivation, the same-type answers (which include
        // their own firearms list) should take precedence — we don't
        // overwrite them with a different-type firearm list.
        $user = $this->user();
        $sdType = $this->selfDefenceType();
        $huntType = $this->huntingType();

        $recentSdFirearms = [[
            'type' => 'Handgun',
            'calibre' => '9mm',
            'serial' => 'LATEST-SD',
        ]];
        $oldHuntFirearms = [[
            'type' => 'Rifle',
            'calibre' => '.308',
            'serial' => 'OLD-HUNT',
        ]];

        // The hunting motivation is more recent, but we're building a
        // self-defence draft — the earlier SAME-TYPE SD answers must win.
        $user->motivationRequests()->create([
            'application_type_id' => $sdType->id,
            'status' => MotivationRequest::STATUS_PRINTED,
            'answers' => ['firearms' => $recentSdFirearms, 'province' => 'Gauteng'],
            'submitted_at' => now()->subMonths(2),
        ]);
        $user->motivationRequests()->create([
            'application_type_id' => $huntType->id,
            'status' => MotivationRequest::STATUS_PRINTED,
            'answers' => ['firearms' => $oldHuntFirearms, 'species' => 'Kudu'],
            'submitted_at' => now()->subMonth(),
        ]);

        $sdDraft = $user->motivationRequests()->create([
            'application_type_id' => $sdType->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => [],
        ]);

        PreviousMotivationData::applyTo($sdDraft);

        $sdDraft->refresh();
        $this->assertSame($recentSdFirearms, $sdDraft->answers['firearms'], 'Same-type firearms must win over cross-type firearms.');
        $this->assertSame('Gauteng', $sdDraft->answers['province']);
    }
}
