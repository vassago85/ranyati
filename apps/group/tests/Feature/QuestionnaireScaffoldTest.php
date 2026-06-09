<?php

namespace Tests\Feature;

use App\Models\QuestionnaireResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionnaireScaffoldTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Test Admin',
            'email' => 'test-admin@example.com',
            'password' => 'password123',
            'role' => User::ROLE_DEVELOPER,
        ]);
    }

    public function test_guest_is_redirected_from_questionnaires(): void
    {
        $this->get('/admin/questionnaires')->assertRedirect('/admin/login');
    }

    public function test_admin_can_open_fill_form(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/questionnaires/self-defence-handgun/fill')
            ->assertOk()
            ->assertSee('Self-Defence Handgun Questionnaire')
            ->assertSee('personal safety')
            ->assertSee('declaration', false);
    }

    public function test_unknown_questionnaire_404s(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/questionnaires/does-not-exist/fill')
            ->assertNotFound();
    }

    public function test_validation_blocks_incomplete_submission(): void
    {
        $this->actingAs($this->admin())
            ->from('/admin/questionnaires/self-defence-handgun/fill')
            ->post('/admin/questionnaires/self-defence-handgun', ['full_name' => 'Only a name'])
            ->assertRedirect('/admin/questionnaires/self-defence-handgun/fill')
            ->assertSessionHasErrors(['declaration', 'threat_assessment']);

        $this->assertDatabaseCount('questionnaire_responses', 0);
    }

    public function test_admin_can_submit_and_view_response(): void
    {
        $admin = $this->admin();

        $payload = [
            'full_name' => 'Jane Doe',
            'id_number' => '9001011234080',
            'email' => 'jane@example.com',
            'phone' => '0821234567',
            'occupation' => 'Pharmacist',
            'physical_address' => '12 Test Road, Pretoria',
            'firearm_type' => 'Pistol',
            'has_competency' => 'Yes',
            'previously_refused' => 'No',
            'threat_assessment' => 'Late-night cash handling at an isolated premises.',
            'why_self_defence' => 'Repeated armed robberies in the area.',
            'why_handgun' => 'Concealable and effective deterrent.',
            'carry_storage' => 'Carried on person, stored in a safe at home.',
            'declaration' => '1',
        ];

        $response = $this->actingAs($admin)
            ->post('/admin/questionnaires/self-defence-handgun', $payload);

        $this->assertDatabaseCount('questionnaire_responses', 1);

        $saved = QuestionnaireResponse::first();
        $this->assertSame('Jane Doe', $saved->applicant_name);
        $this->assertSame('jane@example.com', $saved->applicant_email);
        $this->assertSame('self-defence-handgun', $saved->questionnaire_key);
        $this->assertTrue($saved->answers['declaration']);
        $this->assertSame('Pistol', $saved->answers['firearm_type']);
        $this->assertSame($admin->id, $saved->submitted_by);

        $response->assertRedirect("/admin/questionnaires/responses/{$saved->id}");

        $this->actingAs($admin)
            ->get("/admin/questionnaires/responses/{$saved->id}")
            ->assertOk()
            ->assertSee('Jane Doe')
            ->assertSee('Late-night cash handling');
    }
}
