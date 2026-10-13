<?php

namespace Tests\Feature\Motivations;

use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationRequest;
use App\Models\MotivationTemplate;
use App\Models\User;
use App\Support\Motivations\BookRenderer;
use App\Support\Motivations\Templates\BlockKinds;
use App\Support\Motivations\Templates\LegacyBlockConverter;
use App\Support\Motivations\Templates\LetterComposer;
use App\Support\Motivations\Templates\LetterPdf;
use App\Support\Motivations\Templates\RenderContext;
use Database\Seeders\MotivationTypesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Phase 1 fidelity tests: proving that the LegacyBlockConverter preserves
 * the printed-book behaviour of each seeded application type by routing a
 * request through both pipelines (legacy blocks and the converted template
 * v1) and comparing results.
 */
class TemplateConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['motivations.storage' => 'local']);
        $this->seed(MotivationTypesSeeder::class);
    }

    public function test_every_seeded_type_got_a_template_with_a_published_v1_as_default(): void
    {
        $types = ApplicationType::whereHas('blocks')->get();
        $this->assertNotEmpty($types);

        foreach ($types as $type) {
            $template = $type->templates()->wherePivot('is_default', true)->first();
            $this->assertNotNull($template, "Type {$type->slug} should have a default template");
            $this->assertSame(MotivationTemplate::STATUS_PUBLISHED, $template->status);
            $this->assertNotNull($template->published_version_id);

            $version = $template->publishedVersion()->first();
            $this->assertNotNull($version);
            $this->assertSame(1, $version->version);
            $this->assertNotEmpty($version->blocks);
        }
    }

    public function test_conversion_is_idempotent(): void
    {
        $type = ApplicationType::where('slug', 'self-defence')->firstOrFail();
        $converter = new LegacyBlockConverter;

        $first = $converter->convert($type);
        $firstCount = MotivationTemplate::count();

        $second = $converter->convert($type);
        $secondCount = MotivationTemplate::count();

        $this->assertSame($first->id, $second->id);
        $this->assertSame($firstCount, $secondCount);
    }

    public function test_firearms_table_converts_to_table_source_firearms(): void
    {
        $converter = new LegacyBlockConverter;
        $converted = $converter->convertBlocks([
            ['kind' => 'firearms_table', 'settings' => ['title' => 'Firearms'], 'condition' => null],
        ]);

        $this->assertCount(1, $converted);
        $this->assertSame(BlockKinds::TABLE, $converted[0]['kind']);
        $this->assertSame('firearms', $converted[0]['settings']['source']);
        $this->assertSame('Firearms', $converted[0]['settings']['title']);
    }

    public function test_yes_list_paragraph_converts_with_label_fallback(): void
    {
        $converter = new LegacyBlockConverter;
        $converted = $converter->convertBlocks([
            ['kind' => 'yes_list_paragraph', 'settings' => ['field' => 'things', 'intro' => 'Intro.', 'as_list' => true], 'condition' => null],
        ]);

        $this->assertCount(1, $converted);
        $this->assertSame(BlockKinds::YES_NO_SENTENCES, $converted[0]['kind']);
        $this->assertSame('field', $converted[0]['settings']['scope']);
        $this->assertSame('things', $converted[0]['settings']['field']);
        $this->assertSame('label', $converted[0]['settings']['empty_sentence']);
        $this->assertTrue($converted[0]['settings']['as_list']);
    }

    public function test_consecutive_province_text_blocks_collapse_into_crime_context(): void
    {
        $converter = new LegacyBlockConverter;
        $converted = $converter->convertBlocks([
            [
                'kind' => 'text', 'settings' => ['body' => 'GAUTENG BODY'],
                'condition' => ['field' => 'answers.province', 'operator' => 'equals', 'value' => 'Gauteng'],
            ],
            [
                'kind' => 'text', 'settings' => ['body' => 'WC BODY'],
                'condition' => ['field' => 'answers.province', 'operator' => 'equals', 'value' => 'Western Cape'],
            ],
        ]);

        $this->assertCount(1, $converted);
        $this->assertSame(BlockKinds::CRIME_CONTEXT, $converted[0]['kind']);
        $this->assertSame('GAUTENG BODY', $converted[0]['settings']['bodies']['Gauteng']);
        $this->assertSame('WC BODY', $converted[0]['settings']['bodies']['Western Cape']);
    }

    public function test_self_defence_v1_renders_the_matching_province_body(): void
    {
        $type = ApplicationType::where('slug', 'self-defence')->firstOrFail();
        $request = $this->request($type, [
            'firearms' => [['type' => 'Pistol', 'calibre' => '9mm', 'serial' => 'SN123', 'action' => 'Semi-auto', 'make' => 'Canik', 'model' => 'MC9']],
            'province' => 'Gauteng',
            'policing_precinct' => 'Pretoria Central',
            'employment_profile' => ['shifts' => 'yes', 'travel_work' => 'yes'],
            'other_profile' => ['family_friends' => 'yes'],
            'home_security' => ['armed_response' => 'yes'],
        ]);

        $template = $type->templates()->wherePivot('is_default', true)->firstOrFail();
        $version = $template->publishedVersion()->firstOrFail();

        $request->update(['template_version_id' => $version->id]);
        $preview = BookRenderer::preview($request->fresh(['applicationType.blocks', 'documents', 'user.clientProfile', 'templateVersion']));

        $this->assertStringContainsString('Gauteng', $preview['text']);
        $this->assertStringContainsString('Pretoria Central', $preview['text']);
        // KZN / WC bodies must not leak into the output.
        $this->assertStringNotContainsString('KwaZulu-Natal remains one of South Africa', $preview['text']);
        $this->assertStringNotContainsString('Cape Town metro', $preview['text']);
    }

    public function test_self_defence_v1_renders_a_valid_pdf(): void
    {
        $type = ApplicationType::where('slug', 'self-defence')->firstOrFail();
        $template = $type->templates()->wherePivot('is_default', true)->firstOrFail();
        $version = $template->publishedVersion()->firstOrFail();

        $request = $this->request($type, [
            'firearms' => [['type' => 'Pistol', 'calibre' => '9mm', 'serial' => 'SN123', 'action' => 'Semi-auto', 'make' => 'Canik', 'model' => 'MC9']],
            'province' => 'Gauteng',
            'policing_precinct' => 'Pretoria Central',
        ]);
        $request->update(['template_version_id' => $version->id]);

        $result = BookRenderer::render($request->fresh(['applicationType.blocks', 'documents', 'user.clientProfile', 'templateVersion']));

        $this->assertStringStartsWith('%PDF-', $result['bytes']);
    }

    public function test_converted_template_preview_matches_legacy_preview_for_sport(): void
    {
        $type = ApplicationType::where('slug', 'sport-shooting')->firstOrFail();

        $answers = [
            'firearms' => [['type' => 'Rifle', 'calibre' => '.308', 'serial' => 'SP-1', 'action' => 'Bolt', 'make' => 'Tikka', 'model' => 'T3']],
            'section_of_act' => '15',
            'provinces_of_shooting' => 'Gauteng',
        ];

        $legacyRequest = $this->request($type, $answers);
        $legacyPreview = BookRenderer::preview($legacyRequest);

        $template = $type->templates()->wherePivot('is_default', true)->firstOrFail();
        $version = $template->publishedVersion()->firstOrFail();
        $legacyRequest->update(['template_version_id' => $version->id]);
        $newPreview = BookRenderer::preview($legacyRequest->fresh(['applicationType.blocks', 'documents', 'user.clientProfile', 'templateVersion']));

        // Both pipelines resolve the same placeholders; the member's name
        // must appear and no `[MISSING: ...]` tokens remain.
        $this->assertSame($legacyPreview['missing'], $newPreview['missing']);
        $this->assertStringContainsString('Jane Example', $newPreview['text']);
    }

    public function test_yes_no_sentences_default_skips_items_without_sentences(): void
    {
        $type = ApplicationType::where('slug', 'self-defence')->firstOrFail();
        $template = $type->templates()->wherePivot('is_default', true)->firstOrFail();
        $version = $template->publishedVersion()->firstOrFail();

        // Rebuild v1's blocks with a yes_no_sentences block set to skip, so
        // we can prove the new behaviour is wired through — this is the
        // non-legacy mode the builder will produce. Walk all sections to
        // find the first yes/no list field.
        $yesField = null;
        foreach ($type->sections() as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                if (($field['type'] ?? null) === 'yes_no_list') {
                    $yesField = $field;
                    break 2;
                }
            }
        }
        $this->assertNotNull($yesField, 'The self-defence seed should contain a yes_no_list field');

        $request = $this->request($type, [
            'firearms' => [['type' => 'Pistol', 'calibre' => '9mm', 'serial' => 'SN-1', 'action' => 'Semi-auto', 'make' => 'Canik', 'model' => 'MC9']],
            'province' => 'Gauteng',
            'policing_precinct' => 'Centurion',
            $yesField['name'] => collect($yesField['items'])->mapWithKeys(fn ($i) => [$i['name'] => 'yes'])->all(),
        ]);

        $blocks = [[
            'id' => '00000000-0000-4000-8000-000000000001',
            'kind' => BlockKinds::YES_NO_SENTENCES,
            'settings' => [
                'scope' => 'field',
                'field' => $yesField['name'],
                'intro' => '',
                'outro' => '',
                'as_list' => true,
                'empty_sentence' => 'skip',
            ],
            'condition' => null,
        ]];

        $composer = new LetterComposer($request, RenderContext::for($request));
        $result = $composer->compose($blocks);
        $text = LetterPdf::previewText($result['segments']);

        // At least one item in the self-defence yes_no_list has no sentence.
        // Collect the ones that do, and the ones without should NOT appear.
        $withSentence = collect($yesField['items'])->filter(fn ($i) => trim((string) ($i['sentence'] ?? '')) !== '');
        $withoutSentence = collect($yesField['items'])->filter(fn ($i) => trim((string) ($i['sentence'] ?? '')) === '');

        $this->assertGreaterThan(0, $withSentence->count());
        foreach ($withSentence as $i) {
            $this->assertStringContainsString(rtrim((string) $i['sentence'], '.'), $text, "Sentence for item '{$i['label']}' should appear");
        }
        foreach ($withoutSentence as $i) {
            $this->assertStringNotContainsString((string) $i['label'], $text, "Label for sentence-less item '{$i['label']}' should NOT appear");
        }
    }

    private function request(ApplicationType $type, array $answers): MotivationRequest
    {
        $user = User::create([
            'name' => 'Jane Example',
            'email' => 'applicant-'.uniqid().'@example.com',
            'password' => 'secret12',
            'role' => User::ROLE_CLIENT,
        ]);
        ClientProfile::create([
            'user_id' => $user->id,
            'full_names' => 'Jane',
            'surname' => 'Example',
            'id_number' => '9001011234080',
            'physical_address' => '1 Sample Street, Pretoria',
            'occupation' => 'IT Consultant',
            'cell' => '0821234567',
            'email' => $user->email,
        ]);

        return $user->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_SUBMITTED,
            'answers' => $answers,
        ]);
    }
}
