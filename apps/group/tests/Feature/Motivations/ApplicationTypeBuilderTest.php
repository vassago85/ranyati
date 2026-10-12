<?php

namespace Tests\Feature\Motivations;

use App\Models\ApplicationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationTypeBuilderTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret12',
            'role' => User::ROLE_DEVELOPER,
        ]);
    }

    public function test_builder_saves_sections_requirements_and_blocks_in_order(): void
    {
        $admin = $this->admin();

        $payload = [
            'name' => 'Sport',
            'slug' => 'sport-ipsc',
            'category' => 'sport',
            'description' => 'IPSC',
            'has_motivation' => '1',
            'is_active' => '1',
            'sort' => '5',
            'questions_json' => json_encode([
                [
                    'key' => 'main',
                    'title' => 'Main',
                    'fields' => [
                        ['name' => 'why', 'label' => 'Why', 'type' => 'text', 'required' => true],
                    ],
                ],
            ]),
            'requirements_json' => json_encode([
                ['kind' => 'id_document', 'label' => 'ID', 'required' => true, 'min_count' => 1],
            ]),
            'blocks_json' => json_encode([
                ['kind' => 'heading', 'settings' => ['text' => 'A', 'level' => 1]],
                ['kind' => 'text', 'settings' => ['body' => 'B']],
                ['kind' => 'document_insert', 'settings' => ['divider_title' => 'ID', 'kinds' => ['id_document']]],
            ]),
        ];

        $this->actingAs($admin)
            ->post('/admin/application-types', $payload)
            ->assertRedirect();

        $type = ApplicationType::where('slug', 'sport-ipsc')->first();
        $this->assertNotNull($type);
        $this->assertCount(3, $type->blocks);
        $this->assertSame(['heading', 'text', 'document_insert'], $type->blocks->pluck('kind')->all());
        $this->assertSame('Main', $type->sections()[0]['title']);
        $this->assertSame('ID', $type->requirements()[0]['label']);
    }

    public function test_clone_copies_blocks(): void
    {
        $admin = $this->admin();
        $type = ApplicationType::create([
            'name' => 'Source',
            'slug' => 'source',
            'category' => 'sport',
            'is_active' => true,
            'has_motivation' => true,
            'questions' => [],
            'document_requirements' => [],
        ]);
        $type->blocks()->create(['sort' => 0, 'kind' => 'text', 'settings' => ['body' => 'Hello']]);

        $this->actingAs($admin)
            ->post('/admin/application-types/'.$type->id.'/clone')
            ->assertRedirect();

        $this->assertSame(2, ApplicationType::count());
        $clone = ApplicationType::where('id', '!=', $type->id)->first();
        $this->assertSame(1, $clone->blocks()->count());
    }
}
