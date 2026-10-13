<?php

namespace Tests\Feature\Motivations;

use App\Models\AdminActivityLog;
use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationLetter;
use App\Models\MotivationRequest;
use App\Models\MotivationTemplate;
use App\Models\User;
use App\Support\Motivations\DocumentKinds;
use App\Support\Motivations\Templates\LetterGenerator;
use App\Support\Motivations\Templates\TemplateVersionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MotivationLetterGenerationTest extends TestCase
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
        return User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => 'secret12', 'role' => User::ROLE_ADMIN],
        );
    }

    private function typeWithTemplate(array $blocks, string $letterMode = 'per_firearm'): ApplicationType
    {
        $type = ApplicationType::create([
            'name' => 'Sport', 'slug' => 'sport-'.uniqid(),
            'category' => 'sport', 'is_active' => true, 'has_motivation' => true,
            'questions' => [[
                'key' => 'main', 'title' => 'Main', 'fields' => [
                    ['name' => 'colour', 'label' => 'Colour', 'type' => 'text', 'required' => true],
                    ['name' => 'firearms', 'label' => 'Firearms', 'type' => 'firearms_table'],
                ],
            ]],
            'document_requirements' => [],
        ]);

        $template = MotivationTemplate::create([
            'name' => 'Letter', 'slug' => 'letter-'.uniqid(),
            'category' => 'sport', 'status' => MotivationTemplate::STATUS_DRAFT,
            'created_by' => $this->admin()->id, 'updated_by' => $this->admin()->id,
        ]);

        $manager = app(TemplateVersionManager::class);
        $manager->ensureDraft($template, $this->admin()->id);
        $manager->saveDraft($template, $blocks, ['letter_mode' => $letterMode]);
        $manager->publish($template, $this->admin()->id);

        $type->templates()->sync([
            $template->id => ['is_default' => true, 'sort' => 0, 'condition' => null],
        ]);

        return $type;
    }

    private function makeRequest(ApplicationType $type, array $answers): MotivationRequest
    {
        $user = User::create([
            'name' => 'Jane', 'email' => uniqid().'@example.com',
            'password' => 'secret12', 'role' => User::ROLE_CLIENT,
        ]);
        ClientProfile::create([
            'user_id' => $user->id, 'full_names' => 'Jane', 'surname' => 'Doe',
            'id_number' => '9001011234080', 'physical_address' => '1 Test Rd',
            'occupation' => 'Pharmacist', 'cell' => '0821234567', 'email' => $user->email,
        ]);

        $r = $user->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_SUBMITTED,
            'answers' => $answers,
        ]);

        // Add a reference document to satisfy ad-hoc verify() rails.
        $r->documents()->create([
            'kind' => DocumentKinds::REFERENCE,
            'disk' => 'local', 'path' => 'motivations/test-ref.pdf',
            'original_name' => 'ref.pdf', 'mime' => 'application/pdf', 'size' => 100,
            'uploaded_by' => $user->id,
        ]);

        return $r->fresh();
    }

    public function test_generator_produces_one_letter_per_firearm_row(): void
    {
        $type = $this->typeWithTemplate([
            ['kind' => 'heading', 'settings' => ['text' => 'Firearm {{firearm.serial}}', 'level' => 1]],
            ['kind' => 'text', 'settings' => ['body' => '{{profile.full_name}} wants {{firearm.make}} {{firearm.model}}.']],
        ]);
        $r = $this->makeRequest($type, [
            'firearms' => [
                ['make' => 'Canik', 'model' => 'TP9', 'serial' => 'C001', 'calibre' => '9mm', 'action' => 'Semi-auto'],
                ['make' => 'Glock', 'model' => '19', 'serial' => 'G002', 'calibre' => '9mm', 'action' => 'Semi-auto'],
            ],
        ]);

        $result = (new LetterGenerator($this->admin()->id))->generate($r);

        $this->assertCount(2, $result['letters']);
        $this->assertSame(1, $result['letters'][0]->firearm_index);
        $this->assertSame('C001', $result['letters'][0]->firearm_serial);
        $this->assertSame(2, $result['letters'][1]->firearm_index);
        $this->assertSame('G002', $result['letters'][1]->firearm_serial);
        $this->assertNotNull($result['letters'][0]->pdf_path);
        $this->assertSame(MotivationLetter::GEN_DONE, $result['letters'][0]->generation_status);

        // Activity log entry was written.
        $this->assertTrue(AdminActivityLog::where('action', 'letter.generated')->exists());
    }

    public function test_generator_produces_one_combined_letter_when_mode_is_combined(): void
    {
        $type = $this->typeWithTemplate([
            ['kind' => 'text', 'settings' => ['body' => 'Combined letter.']],
        ], letterMode: 'combined');
        $r = $this->makeRequest($type, [
            'firearms' => [
                ['make' => 'Canik', 'model' => 'TP9', 'serial' => 'C001', 'calibre' => '9mm', 'action' => 'Semi-auto'],
                ['make' => 'Glock', 'model' => '19', 'serial' => 'G002', 'calibre' => '9mm', 'action' => 'Semi-auto'],
            ],
        ]);

        $result = (new LetterGenerator($this->admin()->id))->generate($r);

        $this->assertCount(1, $result['letters']);
        $this->assertNull($result['letters'][0]->firearm_index);
    }

    public function test_regenerating_replaces_drafts_but_keeps_finals(): void
    {
        $type = $this->typeWithTemplate([
            ['kind' => 'text', 'settings' => ['body' => 'Hello {{profile.full_name}}.']],
        ], letterMode: 'combined');
        $r = $this->makeRequest($type, []);

        $gen = new LetterGenerator($this->admin()->id);
        $gen->generate($r);
        $letter = $r->fresh()->letters->first();
        $letter->update(['status' => MotivationLetter::STATUS_FINAL, 'finalised_at' => now()]);

        $gen->generate($r->fresh());

        $letters = $r->fresh()->letters;
        $this->assertSame(2, $letters->count(), 'Final letter is kept, a new draft is added.');
        $this->assertSame(1, $letters->where('status', 'final')->count());
        $this->assertSame(1, $letters->where('status', 'draft')->count());
    }

    public function test_verify_triggers_the_new_letter_pipeline(): void
    {
        $type = $this->typeWithTemplate([
            ['kind' => 'text', 'settings' => ['body' => 'Firearm {{firearm.serial}}.']],
        ]);
        $r = $this->makeRequest($type, [
            'colour' => 'red',
            'firearms' => [
                ['make' => 'Canik', 'model' => 'TP9', 'serial' => 'C001', 'calibre' => '9mm', 'action' => 'Semi-auto'],
            ],
        ]);

        $this->actingAs($this->admin())
            ->post("/admin/motivation-requests/{$r->id}/verify")
            ->assertRedirect();

        $r->refresh();
        $this->assertTrue(in_array($r->status, [MotivationRequest::STATUS_VERIFIED, MotivationRequest::STATUS_AWAITING_ENDORSEMENT], true));
        $this->assertCount(1, $r->letters);
        $this->assertNotNull($r->letters->first()->pdf_path);
    }

    public function test_mark_ready_requires_every_letter_finalised_in_template_mode(): void
    {
        $type = $this->typeWithTemplate([
            ['kind' => 'text', 'settings' => ['body' => 'Hello.']],
        ], letterMode: 'combined');
        $r = $this->makeRequest($type, []);
        $r->update(['status' => MotivationRequest::STATUS_VERIFIED]);
        (new LetterGenerator($this->admin()->id))->generate($r);

        $this->actingAs($this->admin())
            ->post("/admin/motivation-requests/{$r->id}/ready")
            ->assertRedirect()
            ->assertSessionHas('error');

        // Finalise the letter, now it should flip.
        $r->fresh()->letters->first()->update([
            'status' => MotivationLetter::STATUS_FINAL,
            'finalised_at' => now(),
        ]);
        // Mark its document checked so the uncheck guard rail passes too.
        $r->fresh()->documents->each(fn ($d) => $d->update(['checked_at' => now(), 'checked_by' => $this->admin()->id]));

        $this->actingAs($this->admin())
            ->post("/admin/motivation-requests/{$r->id}/ready")
            ->assertRedirect();
        $this->assertSame(MotivationRequest::STATUS_READY_TO_PRINT, $r->fresh()->status);
    }

    public function test_letter_actions_open_download_regenerate_finalise(): void
    {
        $type = $this->typeWithTemplate([
            ['kind' => 'text', 'settings' => ['body' => 'Hello.']],
        ], letterMode: 'combined');
        $r = $this->makeRequest($type, []);
        (new LetterGenerator($this->admin()->id))->generate($r);
        $letter = $r->fresh()->letters->first();

        // Open (inline)
        $this->actingAs($this->admin())
            ->get("/admin/motivation-requests/{$r->id}/letters/{$letter->id}")
            ->assertOk();

        // Download (attachment)
        $this->actingAs($this->admin())
            ->get("/admin/motivation-requests/{$r->id}/letters/{$letter->id}/download")
            ->assertOk();

        // Regenerate — this scraps the old draft and creates a fresh one;
        // then grab the new letter id for the finalise step.
        $this->actingAs($this->admin())
            ->post("/admin/motivation-requests/{$r->id}/letters/{$letter->id}/regenerate")
            ->assertRedirect();
        $this->assertTrue(AdminActivityLog::where('action', 'letter.regenerated')->exists());

        $fresh = $r->fresh()->letters->first();
        $this->actingAs($this->admin())
            ->post("/admin/motivation-requests/{$r->id}/letters/{$fresh->id}/finalise")
            ->assertRedirect();
        $this->assertTrue(AdminActivityLog::where('action', 'letter.finalised')->exists());
    }

    public function test_final_letter_cannot_be_regenerated_or_deleted(): void
    {
        $type = $this->typeWithTemplate([
            ['kind' => 'text', 'settings' => ['body' => 'Hello.']],
        ], letterMode: 'combined');
        $r = $this->makeRequest($type, []);
        (new LetterGenerator($this->admin()->id))->generate($r);
        $letter = $r->fresh()->letters->first();
        $letter->update(['status' => MotivationLetter::STATUS_FINAL, 'finalised_at' => now()]);

        $this->actingAs($this->admin())
            ->post("/admin/motivation-requests/{$r->id}/letters/{$letter->id}/regenerate")
            ->assertForbidden();

        $this->actingAs($this->admin())
            ->delete("/admin/motivation-requests/{$r->id}/letters/{$letter->id}")
            ->assertForbidden();
    }

    public function test_update_text_rewrites_a_block_with_sanitised_rich_text(): void
    {
        $type = $this->typeWithTemplate([
            ['kind' => 'text', 'settings' => ['body' => 'Default copy.']],
        ], letterMode: 'combined');
        $r = $this->makeRequest($type, []);
        (new LetterGenerator($this->admin()->id))->generate($r);
        $letter = $r->fresh()->letters->first();
        $templateVersion = $letter->templateVersion;
        $blockId = $templateVersion->blocks[0]['id'];

        $this->actingAs($this->admin())
            ->post("/admin/motivation-requests/{$r->id}/letters/{$letter->id}/text", [
                'block_overrides' => [
                    $blockId => '<p>Custom <strong>copy</strong><script>bad()</script></p>',
                ],
            ])->assertRedirect();

        $letter->refresh();
        $this->assertArrayHasKey($blockId, $letter->snapshot['overrides']);
        $this->assertStringNotContainsString('<script', $letter->snapshot['overrides'][$blockId]);
        $this->assertStringContainsString('<strong>copy</strong>', $letter->snapshot['overrides'][$blockId]);
        $this->assertTrue(AdminActivityLog::where('action', 'letter.edited')->exists());
    }

    public function test_status_endpoint_returns_letter_summary(): void
    {
        $type = $this->typeWithTemplate([
            ['kind' => 'text', 'settings' => ['body' => 'Hi.']],
        ], letterMode: 'combined');
        $r = $this->makeRequest($type, []);
        (new LetterGenerator($this->admin()->id))->generate($r);

        $this->actingAs($this->admin())
            ->getJson("/admin/motivation-requests/{$r->id}/letters/status")
            ->assertOk()
            ->assertJsonPath('any_running', false)
            ->assertJsonPath('letters.0.has_pdf', true);
    }
}
