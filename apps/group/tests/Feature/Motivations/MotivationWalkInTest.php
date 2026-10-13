<?php

namespace Tests\Feature\Motivations;

use App\Mail\MotivationUpdate;
use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationDocument;
use App\Models\MotivationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MotivationWalkInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['motivations.storage' => 'local']);
        Mail::fake();
    }

    private function admin(?string $email = null): User
    {
        $u = User::create([
            'name' => 'Admin',
            'email' => $email ?: 'admin-'.uniqid().'@ranyati.test',
            'password' => 'secret12',
            'role' => User::ROLE_DEVELOPER,
        ]);
        $u->forceFill(['email_verified_at' => now()])->save();

        return $u;
    }

    private function clientUser(): User
    {
        $u = User::create([
            'name' => 'Client',
            'email' => 'client-'.uniqid().'@example.com',
            'password' => 'secret12',
            'role' => User::ROLE_CLIENT,
        ]);
        $u->forceFill(['email_verified_at' => now()])->save();

        return $u;
    }

    private function simpleType(): ApplicationType
    {
        return ApplicationType::firstOrCreate(
            ['slug' => 'walkin-simple'],
            [
                'name' => 'Walk-in simple',
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

    public function test_admin_can_create_a_walk_in_member_with_a_profile(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/motivation-members', [
                'name' => 'John Visitor',
                'account_email' => 'john.visitor@example.com',
                // profile particulars
                'full_names' => 'John',
                'surname' => 'Visitor',
                'id_number' => '9001011234080',
                'physical_address' => '12 Walk Rd',
                'occupation' => 'Technician',
                'cell' => '0821234567',
                'email' => 'john.visitor@example.com',
            ])
            ->assertRedirect();

        $user = User::where('email', 'john.visitor@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(User::ROLE_CLIENT, $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotNull($user->clientProfile);
        $this->assertSame('Visitor', $user->clientProfile->surname);
    }

    public function test_admin_can_start_a_motivation_for_a_walk_in_member(): void
    {
        $admin = $this->admin();
        $client = $this->clientUser();
        ClientProfile::create([
            'user_id' => $client->id,
            'full_names' => 'John', 'surname' => 'V',
            'id_number' => '9001011234080',
            'physical_address' => '1 Rd',
            'occupation' => 'Clerk',
            'cell' => '0821234567',
            'email' => $client->email,
        ]);
        $type = $this->simpleType();

        $this->actingAs($admin)
            ->post('/admin/motivation-members/'.$client->id.'/motivations', [
                'application_type_id' => $type->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('motivation_requests', [
            'user_id' => $client->id,
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_DRAFT,
        ]);
    }

    public function test_admin_cannot_start_motivation_until_profile_is_complete(): void
    {
        $admin = $this->admin();
        $client = $this->clientUser();
        $type = $this->simpleType();

        // No ClientProfile at all.
        $this->actingAs($admin)
            ->post('/admin/motivation-members/'.$client->id.'/motivations', [
                'application_type_id' => $type->id,
            ])
            ->assertRedirect(route('admin.motivation-members.profile.edit', $client));

        $this->assertDatabaseMissing('motivation_requests', [
            'user_id' => $client->id,
        ]);
    }

    public function test_admin_can_fill_answers_and_upload_documents_on_behalf(): void
    {
        $admin = $this->admin();
        $client = $this->clientUser();
        $type = $this->simpleType();
        $request = $client->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => [],
        ]);

        // Reach the edit view.
        $this->actingAs($admin)
            ->get('/admin/motivation-requests/'.$request->id.'/edit')
            ->assertOk()
            ->assertSee('On behalf of');

        // Save answers.
        $this->actingAs($admin)
            ->post('/admin/motivation-requests/'.$request->id.'/answers', [
                'answers' => ['why' => 'Sport shooting'],
            ])
            ->assertRedirect();
        $this->assertSame('Sport shooting', $request->fresh()->answers['why']);

        // Upload a document on behalf — it is marked as checked by the admin.
        $this->actingAs($admin)
            ->post('/admin/motivation-requests/'.$request->id.'/documents', [
                'kind' => 'id_document',
                'file' => UploadedFile::fake()->create('id.pdf', 10, 'application/pdf'),
            ])
            ->assertRedirect();

        $doc = MotivationDocument::where('motivation_request_id', $request->id)->first();
        $this->assertNotNull($doc);
        $this->assertSame($admin->id, $doc->uploaded_by);
        $this->assertNotNull($doc->checked_at);
    }

    public function test_admin_can_submit_on_behalf_with_a_delivery_method(): void
    {
        $admin = $this->admin();
        $client = $this->clientUser();
        ClientProfile::create([
            'user_id' => $client->id,
            'full_names' => 'John', 'surname' => 'V',
            'id_number' => '9001011234080',
            'physical_address' => '1 Rd',
            'occupation' => 'Clerk',
            'cell' => '0821234567',
            'email' => $client->email,
        ]);
        $type = $this->simpleType();
        $request = $client->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => ['why' => 'Sport'],
        ]);
        MotivationDocument::create([
            'motivation_request_id' => $request->id,
            'kind' => 'id_document',
            'disk' => 'local',
            'path' => 'motivations/'.$request->id.'/id.pdf',
            'original_name' => 'id.pdf',
            'mime' => 'application/pdf',
            'size' => 10,
        ]);

        $this->actingAs($admin)
            ->post('/admin/motivation-requests/'.$request->id.'/submit-on-behalf', [
                'delivery_method' => MotivationRequest::DELIVERY_COLLECT,
            ])
            ->assertRedirect();

        $fresh = $request->fresh();
        $this->assertSame(MotivationRequest::STATUS_SUBMITTED, $fresh->status);
        $this->assertSame(MotivationRequest::DELIVERY_COLLECT, $fresh->delivery_method);

        // Internal mail always; member mail only when notify_member checked.
        Mail::assertSent(MotivationUpdate::class, fn ($m) => $m->stage === MotivationUpdate::STAGE_NEW_SUBMISSION_INTERNAL);
        Mail::assertNotSent(MotivationUpdate::class, fn ($m) => $m->stage === MotivationUpdate::STAGE_SUBMITTED);
    }

    public function test_admin_submit_on_behalf_notifies_member_when_asked(): void
    {
        $admin = $this->admin();
        $client = $this->clientUser();
        $type = $this->simpleType();
        $request = $client->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => ['why' => 'Sport'],
        ]);
        MotivationDocument::create([
            'motivation_request_id' => $request->id,
            'kind' => 'id_document',
            'disk' => 'local',
            'path' => 'motivations/'.$request->id.'/id.pdf',
            'original_name' => 'id.pdf',
            'mime' => 'application/pdf',
            'size' => 10,
        ]);

        $this->actingAs($admin)
            ->post('/admin/motivation-requests/'.$request->id.'/submit-on-behalf', [
                'delivery_method' => MotivationRequest::DELIVERY_COURIER,
                'notify_member' => '1',
            ])
            ->assertRedirect();

        Mail::assertSent(MotivationUpdate::class, fn ($m) => $m->stage === MotivationUpdate::STAGE_SUBMITTED);
    }

    public function test_a_client_role_user_cannot_reach_the_admin_walkin_routes(): void
    {
        $client = $this->clientUser();

        // Admin middleware redirects non-admins to the admin login page.
        $this->actingAs($client)->get('/admin/motivation-members')->assertRedirect(route('admin.login'));
        $this->actingAs($client)->get('/admin/motivation-members/create')->assertRedirect(route('admin.login'));
    }
}
