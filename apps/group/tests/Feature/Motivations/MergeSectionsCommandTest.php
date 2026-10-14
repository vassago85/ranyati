<?php

namespace Tests\Feature\Motivations;

use App\Models\MotivationTemplate;
use App\Models\User;
use App\Support\Motivations\Templates\BlockKinds;
use App\Support\Motivations\Templates\TemplateVersionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MergeSectionsCommandTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => 'secret12', 'role' => User::ROLE_ADMIN],
        );
    }

    private function templateWithPairs(): MotivationTemplate
    {
        $admin = $this->admin();
        $template = MotivationTemplate::create([
            'name' => 'Merge me',
            'slug' => 'merge-me',
            'category' => 'sport',
            'status' => MotivationTemplate::STATUS_DRAFT,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        app(TemplateVersionManager::class)->saveDraft($template, [
            ['kind' => BlockKinds::HEADING, 'settings' => ['text' => 'One', 'level' => 2]],
            ['kind' => BlockKinds::TEXT, 'settings' => ['body' => 'Body one.']],
            ['kind' => BlockKinds::HEADING, 'settings' => ['text' => 'Two', 'level' => 2]],
            ['kind' => BlockKinds::TEXT, 'settings' => ['body' => 'Body two.']],
        ], []);

        return $template->fresh();
    }

    #[Test]
    public function dry_run_does_not_write_the_draft(): void
    {
        $template = $this->templateWithPairs();

        $this->artisan('motivation-templates:merge-sections', [
            '--template' => 'merge-me',
            '--dry-run' => true,
        ])->assertSuccessful();

        $draft = $template->fresh()->draftVersion();
        $this->assertCount(4, $draft->blocks);
        $this->assertSame(BlockKinds::HEADING, $draft->blocks[0]['kind']);
    }

    #[Test]
    public function write_mode_folds_pairs_into_the_draft(): void
    {
        $template = $this->templateWithPairs();

        $this->artisan('motivation-templates:merge-sections', [
            '--template' => 'merge-me',
        ])->assertSuccessful();

        $draft = $template->fresh()->draftVersion();
        $this->assertCount(2, $draft->blocks);
        $this->assertSame(BlockKinds::SECTION, $draft->blocks[0]['kind']);
        $this->assertSame('One', $draft->blocks[0]['settings']['heading']);
        $this->assertStringContainsString('Body one.', $draft->blocks[0]['settings']['html']);
        $this->assertNull($draft->published_at, 'Published versions stay untouched.');
    }
}
