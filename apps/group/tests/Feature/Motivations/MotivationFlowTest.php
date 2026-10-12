<?php

namespace Tests\Feature\Motivations;

use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationDocument;
use App\Models\MotivationRequest;
use App\Models\User;
use App\Support\Motivations\DocumentKinds;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MotivationFlowTest extends TestCase
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
        return User::create([
            'name' => 'Reviewer',
            'email' => uniqid().'@example.com',
            'password' => 'secret12',
            'role' => User::ROLE_DEVELOPER,
            'email_verified_at' => now(),
        ]);
    }

    private function makeScenario(): array
    {
        $user = User::create([
            'name' => 'M',
            'email' => uniqid().'@example.com',
            'password' => 'secret12',
            'role' => User::ROLE_CLIENT,
            'email_verified_at' => now(),
        ]);
        ClientProfile::create([
            'user_id' => $user->id,
            'full_names' => 'Jane', 'surname' => 'Doe',
            'id_number' => '9001011234080',
            'physical_address' => '1 Test Rd',
            'occupation' => 'Clerk', 'cell' => '0821234567',
            'email' => $user->email,
        ]);
        $type = ApplicationType::create([
            'name' => 'Flow', 'slug' => 'flow-'.uniqid(),
            'category' => 'sport', 'is_active' => true, 'has_motivation' => true,
            'questions' => [],
            'document_requirements' => [
                ['kind' => 'id_document', 'label' => 'ID', 'required' => true, 'min_count' => 1, 'has_reference_number' => false],
                ['kind' => 'proof_of_residence', 'label' => 'POR', 'required' => true, 'min_count' => 1, 'date_rule' => 'max_age_days', 'max_age_days' => 90],
            ],
        ]);
        $type->blocks()->create(['sort' => 0, 'kind' => 'text', 'settings' => ['body' => 'Hi {{profile.full_name}}.']]);

        $request = $user->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_SUBMITTED,
            'answers' => [],
            'submitted_at' => now(),
        ]);

        foreach (['id_document' => now(), 'proof_of_residence' => now()->subDays(200)] as $kind => $issued) {
            MotivationDocument::create([
                'motivation_request_id' => $request->id,
                'kind' => $kind,
                'disk' => 'local',
                'path' => 'motivations/'.$request->id.'/'.$kind.'.pdf',
                'original_name' => $kind.'.pdf',
                'mime' => 'application/pdf',
                'size' => 10,
                'issued_on' => $issued,
            ]);
            Storage::disk('local')->put('motivations/'.$request->id.'/'.$kind.'.pdf', '%PDF-1.4 fake');
        }

        return [$this->admin(), $user, $type, $request];
    }

    public function test_verify_generates_the_book_and_takes_a_snapshot(): void
    {
        [$admin, , , $request] = $this->makeScenario();

        $this->actingAs($admin)
            ->post('/admin/motivation-requests/'.$request->id.'/verify')
            ->assertRedirect();

        $fresh = $request->fresh();
        $this->assertSame('Jane', $fresh->profile_snapshot['full_names']);
        $this->assertNotNull($fresh->pdf_path);
        $this->assertNotNull($fresh->blocks_snapshot);
        $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($fresh->pdf_path));
    }

    public function test_cannot_mark_ready_until_every_document_is_checked_and_dates_are_valid(): void
    {
        [$admin, , , $request] = $this->makeScenario();

        $this->actingAs($admin)->post('/admin/motivation-requests/'.$request->id.'/verify');

        // Still flagged: proof of residence is 200 days old.
        $this->actingAs($admin)
            ->post('/admin/motivation-requests/'.$request->id.'/ready')
            ->assertSessionHas('error');

        $this->assertNotSame(MotivationRequest::STATUS_READY_TO_PRINT, $request->fresh()->status);

        // Fix the POR date and mark everything checked.
        $request->fresh()->documents->each(function ($d) use ($admin) {
            $d->update(['checked_by' => $admin->id, 'checked_at' => now()]);
            if ($d->kind === 'proof_of_residence') {
                $d->update(['issued_on' => now()]);
            }
        });

        $this->actingAs($admin)
            ->post('/admin/motivation-requests/'.$request->id.'/ready')
            ->assertSessionHas('success');

        $this->assertSame(MotivationRequest::STATUS_READY_TO_PRINT, $request->fresh()->status);
    }

    public function test_request_changes_sends_the_request_back_to_the_member(): void
    {
        [$admin, , , $request] = $this->makeScenario();

        $this->actingAs($admin)
            ->post('/admin/motivation-requests/'.$request->id.'/request-changes', ['reviewer_notes' => 'Please update your address.'])
            ->assertRedirect();

        $fresh = $request->fresh();
        $this->assertSame(MotivationRequest::STATUS_CHANGES_REQUESTED, $fresh->status);
        $this->assertSame('Please update your address.', $fresh->reviewer_notes);
    }
}
