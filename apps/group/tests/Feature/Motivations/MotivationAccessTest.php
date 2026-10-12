<?php

namespace Tests\Feature\Motivations;

use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MotivationAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['motivations.storage' => 'local']);
    }

    private function member(string $email = 'member@example.com'): User
    {
        $user = User::create([
            'name' => 'Member',
            'email' => $email,
            'password' => 'secret12',
            'role' => User::ROLE_CLIENT,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        ClientProfile::create([
            'user_id' => $user->id,
            'full_names' => 'Member',
            'surname' => 'Smith',
            'id_number' => '9001011234080',
            'physical_address' => '1 Test Rd',
            'occupation' => 'Clerk',
            'cell' => '0821234567',
            'email' => $email,
        ]);

        return $user;
    }

    private function type(): ApplicationType
    {
        return ApplicationType::firstOrCreate(
            ['slug' => 'simple'],
            [
                'name' => 'Simple',
                'category' => 'sport',
                'is_active' => true,
                'has_motivation' => true,
                'questions' => [],
                'document_requirements' => [
                    ['kind' => 'id_document', 'label' => 'ID', 'required' => true, 'min_count' => 1],
                ],
            ],
        );
    }

    private function withMotivationsHost(): self
    {
        return $this->withHeaders(['HOST' => 'motivations.ranyati.test'])
            ->withServerVariables(['HTTP_HOST' => 'motivations.ranyati.test']);
    }

    public function test_member_cannot_see_another_members_request(): void
    {
        $a = $this->member('a@example.com');
        $b = $this->member('b@example.com');
        $request = $a->motivationRequests()->create([
            'application_type_id' => $this->type()->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => [],
        ]);

        $this->withMotivationsHost()
            ->actingAs($b)
            ->get('/account/motivations/'.$request->id)
            ->assertNotFound();
    }

    public function test_members_cannot_download_the_book(): void
    {
        // No account-side route exposes the book — only admins can get it.
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('account.motivations.book'));
    }

    public function test_submit_blocks_when_required_answers_or_documents_are_missing(): void
    {
        $user = $this->member();
        $type = ApplicationType::create([
            'name' => 'With required',
            'slug' => 'with-required',
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
        ]);
        $request = $user->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => [],
        ]);

        $this->withMotivationsHost()
            ->actingAs($user)
            ->post('/account/motivations/'.$request->id.'/submit', [
                'delivery_method' => MotivationRequest::DELIVERY_COLLECT,
            ])
            ->assertSessionHasErrors(['answers', 'documents']);

        $this->assertSame(MotivationRequest::STATUS_DRAFT, $request->fresh()->status);
    }

    public function test_member_can_upload_document_to_private_disk(): void
    {
        $user = $this->member();
        $request = $user->motivationRequests()->create([
            'application_type_id' => $this->type()->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => [],
        ]);

        $file = UploadedFile::fake()->create('id.pdf', 20, 'application/pdf');

        $this->withMotivationsHost()
            ->actingAs($user)
            ->post('/account/motivations/'.$request->id.'/documents', [
                'kind' => 'id_document',
                'file' => $file,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('motivation_documents', [
            'motivation_request_id' => $request->id,
            'kind' => 'id_document',
            'disk' => 'local',
        ]);
    }
}
