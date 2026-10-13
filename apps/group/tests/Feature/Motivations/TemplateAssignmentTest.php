<?php

namespace Tests\Feature\Motivations;

use App\Models\AdminActivityLog;
use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationRequest;
use App\Models\MotivationTemplate;
use App\Models\User;
use App\Support\Motivations\Templates\TemplateSelector;
use App\Support\Motivations\Templates\TemplateVersionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TemplateAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => 'secret12', 'role' => User::ROLE_ADMIN],
        );
    }

    private function makeType(): ApplicationType
    {
        return ApplicationType::create([
            'name' => 'Sport Section 15',
            'slug' => 'sport-s15',
            'category' => 'sport',
            'is_active' => true,
            'has_motivation' => true,
            'questions' => [[
                'key' => 'main', 'title' => 'Main', 'fields' => [
                    ['name' => 'section', 'label' => 'Section', 'type' => 'select',
                        'options' => ['s15', 's16'],
                    ],
                ],
            ]],
            'document_requirements' => [],
        ]);
    }

    private function makeRequest(ApplicationType $type, array $answers = []): MotivationRequest
    {
        $user = User::create([
            'name' => 'Jane',
            'email' => uniqid().'@example.com',
            'password' => 'secret12',
            'role' => User::ROLE_CLIENT,
        ]);
        ClientProfile::create([
            'user_id' => $user->id,
            'full_names' => 'Jane',
            'surname' => 'Doe',
            'id_number' => '9001011234080',
            'physical_address' => '1 Test Rd',
            'occupation' => 'Pharmacist',
            'cell' => '0821234567',
            'email' => $user->email,
        ]);

        return $user->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_SUBMITTED,
            'answers' => $answers,
        ]);
    }

    private function publishedTemplate(string $name, string $body): MotivationTemplate
    {
        $template = MotivationTemplate::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'category' => 'sport',
            'status' => MotivationTemplate::STATUS_DRAFT,
            'created_by' => $this->admin()->id,
            'updated_by' => $this->admin()->id,
        ]);
        $manager = app(TemplateVersionManager::class);
        $manager->ensureDraft($template, $this->admin()->id);
        $manager->saveDraft($template, [
            ['kind' => 'text', 'settings' => ['body' => $body]],
        ], []);
        $manager->publish($template, $this->admin()->id);

        return $template->fresh();
    }

    public function test_selector_returns_null_when_no_templates_attached(): void
    {
        $r = $this->makeRequest($this->makeType());
        $this->assertNull(TemplateSelector::for($r));
    }

    public function test_selector_returns_the_default_template_when_no_conditions_match(): void
    {
        $type = $this->makeType();
        $t1 = $this->publishedTemplate('Default', 'Default body.');
        $t2 = $this->publishedTemplate('Section 15 only', 'S15 body.');

        $type->templates()->sync([
            $t1->id => ['is_default' => true, 'sort' => 0, 'condition' => null],
            $t2->id => [
                'is_default' => false,
                'sort' => 1,
                'condition' => json_encode(['field' => 'answers.section', 'operator' => 'equals', 'value' => 's15']),
            ],
        ]);

        $r = $this->makeRequest($type->fresh(), ['section' => 's16']);
        $picked = TemplateSelector::for($r);
        $this->assertNotNull($picked);
        $this->assertSame($t1->publishedVersion->id, $picked->id);
    }

    public function test_selector_prefers_the_matching_conditional_template_over_the_default(): void
    {
        $type = $this->makeType();
        $defaultT = $this->publishedTemplate('Default', 'Default body.');
        $s15T = $this->publishedTemplate('Section 15', 'S15 body.');

        $type->templates()->sync([
            $defaultT->id => ['is_default' => true, 'sort' => 0, 'condition' => null],
            $s15T->id => [
                'is_default' => false,
                'sort' => 1,
                'condition' => json_encode(['field' => 'answers.section', 'operator' => 'equals', 'value' => 's15']),
            ],
        ]);

        $r = $this->makeRequest($type->fresh(), ['section' => 's15']);
        $picked = TemplateSelector::for($r);
        $this->assertSame($s15T->publishedVersion->id, $picked->id);
    }

    public function test_selector_skips_templates_without_published_versions(): void
    {
        $type = $this->makeType();

        // Draft-only template with a matching condition — should be skipped.
        $draft = MotivationTemplate::create([
            'name' => 'Draft', 'slug' => 'draft',
            'category' => 'sport', 'status' => MotivationTemplate::STATUS_DRAFT,
            'created_by' => $this->admin()->id, 'updated_by' => $this->admin()->id,
        ]);
        app(TemplateVersionManager::class)->ensureDraft($draft, $this->admin()->id);

        $published = $this->publishedTemplate('Fallback', 'Fallback body.');

        $type->templates()->sync([
            $draft->id => [
                'is_default' => false, 'sort' => 0,
                'condition' => json_encode(['field' => 'answers.section', 'operator' => 'equals', 'value' => 's15']),
            ],
            $published->id => ['is_default' => true, 'sort' => 1, 'condition' => null],
        ]);

        $r = $this->makeRequest($type->fresh(), ['section' => 's15']);
        $picked = TemplateSelector::for($r);
        $this->assertSame($published->publishedVersion->id, $picked->id);
    }

    public function test_assigning_a_template_from_the_type_editor_syncs_the_pivot(): void
    {
        $type = $this->makeType();
        $t1 = $this->publishedTemplate('T1', 'T1 body.');
        $t2 = $this->publishedTemplate('T2', 'T2 body.');

        $payload = [
            'name' => $type->name,
            'slug' => $type->slug,
            'category' => $type->category,
            'has_motivation' => '1',
            'is_active' => '1',
            'sort' => (string) ($type->sort ?? 0),
            'questions_json' => json_encode($type->questions),
            'requirements_json' => json_encode($type->document_requirements ?? []),
            'blocks_json' => json_encode([]),
            'templates_json' => json_encode([
                ['template_id' => $t2->id, 'is_default' => false, 'sort' => 0,
                    'condition' => ['field' => 'answers.section', 'operator' => 'equals', 'value' => 's15']],
                ['template_id' => $t1->id, 'is_default' => true, 'sort' => 1, 'condition' => null],
            ]),
        ];

        $this->actingAs($this->admin())
            ->put('/admin/application-types/'.$type->id, $payload)
            ->assertRedirect();

        $type->refresh();
        $attached = $type->templates()->orderBy('application_type_template.sort')->get();
        $this->assertCount(2, $attached);
        $this->assertSame($t2->id, $attached[0]->id);
        $this->assertFalse((bool) $attached[0]->pivot->is_default);
        $this->assertSame($t1->id, $attached[1]->id);
        $this->assertTrue((bool) $attached[1]->pivot->is_default);

        // An activity log entry should mention the assignment.
        $this->assertTrue(
            AdminActivityLog::where('action', 'template.assigned')->exists(),
            'Expected a template.assigned admin activity log entry.',
        );
    }

    public function test_type_editor_edit_page_renders_the_templates_tab(): void
    {
        $type = $this->makeType();
        $t1 = $this->publishedTemplate('Visible template', 'body');

        $this->actingAs($this->admin())
            ->get('/admin/application-types/'.$type->id.'/edit')
            ->assertOk()
            ->assertSee('Templates')
            ->assertSee($t1->name);
    }
}
