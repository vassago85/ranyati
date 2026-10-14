<?php

namespace Tests\Feature\Motivations;

use App\Models\MotivationTemplate;
use App\Models\User;
use App\Support\Motivations\Templates\TemplateVersionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TemplateBuilderTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = User::ROLE_ADMIN, string $email = 'admin@example.com'): User
    {
        return User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Admin', 'password' => 'secret12', 'role' => $role],
        );
    }

    private function client(): User
    {
        return User::firstOrCreate(
            ['email' => 'client@example.com'],
            ['name' => 'Client', 'password' => 'secret12', 'role' => User::ROLE_CLIENT],
        );
    }

    public function test_admin_can_create_and_edit_a_template(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/motivation-templates', [
                'name' => 'My letter',
                'slug' => 'my-letter',
                'category' => 'sport',
                'description' => 'Example',
            ])
            ->assertRedirect();

        $template = MotivationTemplate::first();
        $this->assertSame('my-letter', $template->slug);
        $this->assertSame('draft', $template->status);
        $this->assertSame(1, $template->versions()->count(), 'A draft v1 should be created automatically.');

        $this->actingAs($this->admin())
            ->get('/admin/motivation-templates/'.$template->id.'/edit')
            ->assertOk()
            ->assertSee('templateBuilder(', false)
            ->assertSee('Page setup', false);
    }

    public function test_non_admin_cannot_see_or_create_templates(): void
    {
        // The admin middleware kicks clients out before the policy fires, so
        // the response is a redirect to login/the member area rather than 403.
        $this->actingAs($this->client())
            ->get('/admin/motivation-templates')
            ->assertStatus(302);

        $this->actingAs($this->client())
            ->post('/admin/motivation-templates', [
                'name' => 'Nope',
                'category' => 'sport',
            ])
            ->assertStatus(302);
        $this->assertSame(0, MotivationTemplate::count());
    }

    public function test_autosave_updates_the_draft_version(): void
    {
        $template = $this->freshTemplate();

        $payload = [
            'blocks' => [
                ['kind' => 'heading', 'settings' => ['text' => 'Hello', 'level' => 1]],
                ['kind' => 'text', 'settings' => ['body' => 'Body text here.']],
            ],
            'settings' => ['page' => ['size' => 'A4']],
        ];

        $this->actingAs($this->admin())
            ->postJson('/admin/motivation-templates/'.$template->id.'/autosave', $payload)
            ->assertOk()
            ->assertJsonStructure(['draft_id', 'version', 'blocks', 'settings', 'saved_at']);

        $draft = $template->fresh()->draftVersion();
        $this->assertCount(2, $draft->blocks);
        $this->assertSame('Hello', $draft->blocks[0]['settings']['text']);
    }

    public function test_publish_freezes_the_version_and_starts_a_new_draft(): void
    {
        $template = $this->freshTemplate();

        $blocks = [['kind' => 'text', 'settings' => ['body' => 'Published copy.']]];

        $this->actingAs($this->admin())
            ->post('/admin/motivation-templates/'.$template->id.'/publish', [
                'blocks' => $blocks,
                'settings' => [],
            ])
            ->assertRedirect();

        $template->refresh();
        $this->assertSame('published', $template->status);
        $this->assertNotNull($template->published_version_id);

        $published = $template->publishedVersion()->first();
        $this->assertNotNull($published);
        $this->assertNotNull($published->published_at);
        $this->assertSame('Published copy.', $published->blocks[0]['settings']['body']);
    }

    public function test_restore_brings_an_old_version_back_as_a_draft(): void
    {
        $template = $this->freshTemplate();
        $manager = app(TemplateVersionManager::class);

        $manager->saveDraft($template, [['kind' => 'text', 'settings' => ['body' => 'v1 body']]], []);
        $v1 = $manager->publish($template, $this->admin()->id);

        // Second edit cycle
        $manager->saveDraft($template, [['kind' => 'text', 'settings' => ['body' => 'v2 body']]], []);
        $v2 = $manager->publish($template, $this->admin()->id);

        $this->actingAs($this->admin())
            ->post("/admin/motivation-templates/{$template->id}/versions/{$v1->id}/restore")
            ->assertRedirect();

        $draft = $template->fresh()->draftVersion();
        $this->assertSame('v1 body', $draft->blocks[0]['settings']['body']);
        $this->assertGreaterThan($v2->version, $draft->version);
    }

    public function test_versions_endpoint_returns_history_with_diffs(): void
    {
        $template = $this->freshTemplate();
        $manager = app(TemplateVersionManager::class);
        $manager->saveDraft($template, [['kind' => 'text', 'settings' => ['body' => 'first']]], []);
        $manager->publish($template, $this->admin()->id);
        $manager->saveDraft($template, [
            ['kind' => 'text', 'settings' => ['body' => 'first']],
            ['kind' => 'heading', 'settings' => ['text' => 'added', 'level' => 2]],
        ], []);

        $response = $this->actingAs($this->admin())
            ->get("/admin/motivation-templates/{$template->id}/versions")
            ->assertOk();

        $data = $response->json();
        $this->assertNotEmpty($data['versions']);
        // Most recent version should have one addition recorded.
        $latest = $data['versions'][0];
        $this->assertSame(1, count($latest['diff']['added']));
    }

    public function test_preview_returns_a_pdf_url_and_the_pdf_streams_back(): void
    {
        $template = $this->freshTemplate();

        $response = $this->actingAs($this->admin())
            ->postJson("/admin/motivation-templates/{$template->id}/preview", [
                'blocks' => [
                    ['kind' => 'heading', 'settings' => ['text' => 'Preview heading', 'level' => 1]],
                    ['kind' => 'text', 'settings' => ['body' => 'Hello {{profile.full_name}}.']],
                ],
                'settings' => [],
            ])->assertOk()
            ->assertJsonStructure(['token', 'url', 'missing', 'warnings']);

        $url = $response->json('url');
        $token = $response->json('token');
        $this->assertStringContainsString('/preview/', $url);

        // The PDF must live on the local disk, not in the cache. The
        // production cache is MySQL, and a serialized PDF is not valid
        // UTF-8, so Cache::put of the raw bytes 500s on Render.
        $this->assertTrue(Storage::disk('local')->exists('template-previews/'.$token.'.pdf'));
        $this->assertNull(Cache::get('template-preview:'.$token));

        $pdf = $this->actingAs($this->admin())->get($url);
        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    public function test_duplicate_creates_a_new_draft_copy(): void
    {
        $template = $this->freshTemplate();
        $manager = app(TemplateVersionManager::class);
        $manager->saveDraft($template, [['kind' => 'text', 'settings' => ['body' => 'Original']]], []);
        $manager->publish($template, $this->admin()->id);

        $this->actingAs($this->admin())
            ->post("/admin/motivation-templates/{$template->id}/duplicate")
            ->assertRedirect();

        $this->assertSame(2, MotivationTemplate::count());
        $copy = MotivationTemplate::where('id', '!=', $template->id)->first();
        $this->assertSame('draft', $copy->status);
        $this->assertStringContainsString('(copy)', $copy->name);
        $this->assertSame(1, $copy->versions()->count());
    }

    public function test_archive_and_unarchive_flip_the_status(): void
    {
        $template = $this->freshTemplate();

        $this->actingAs($this->admin())
            ->post("/admin/motivation-templates/{$template->id}/archive")
            ->assertRedirect();
        $this->assertSame('archived', $template->fresh()->status);

        $this->actingAs($this->admin())
            ->post("/admin/motivation-templates/{$template->id}/unarchive")
            ->assertRedirect();
        $this->assertSame('draft', $template->fresh()->status);
    }

    public function test_assets_can_be_uploaded_and_listed_as_json(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png', 400, 200);

        $this->actingAs($this->admin())
            ->postJson('/admin/motivation-templates/assets', [
                'name' => 'Club logo',
                'alt' => 'Club crest',
                'category' => 'logo',
                'file' => $file,
            ])->assertOk()
            ->assertJsonPath('asset.name', 'Club logo')
            ->assertJsonPath('asset.category', 'logo');

        $this->actingAs($this->admin())
            ->getJson('/admin/motivation-templates/assets')
            ->assertOk()
            ->assertJsonPath('assets.0.name', 'Club logo');
    }

    public function test_assets_page_renders_for_admin(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/motivation-templates/assets')
            ->assertOk()
            ->assertSee('Template assets');
    }

    private function freshTemplate(): MotivationTemplate
    {
        $admin = $this->admin();
        $template = MotivationTemplate::create([
            'name' => 'Fresh',
            'slug' => 'fresh',
            'category' => 'sport',
            'status' => MotivationTemplate::STATUS_DRAFT,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
        // Mirror what the controller does on create() — kick off the draft.
        app(TemplateVersionManager::class)->ensureDraft($template, $admin->id);

        return $template->refresh();
    }
}
