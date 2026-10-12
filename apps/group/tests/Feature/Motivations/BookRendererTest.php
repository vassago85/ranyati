<?php

namespace Tests\Feature\Motivations;

use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationDocument;
use App\Models\MotivationRequest;
use App\Models\User;
use App\Support\Motivations\BookRenderer;
use App\Support\Motivations\DocumentKinds;
use App\Support\Motivations\PdfMerger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookRendererTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['motivations.storage' => 'local']);
    }

    private function makeType(array $blocks = []): ApplicationType
    {
        $type = ApplicationType::create([
            'name' => 'Test type',
            'slug' => 'test-'.uniqid(),
            'category' => 'sport',
            'is_active' => true,
            'has_motivation' => true,
            'questions' => [[
                'key' => 'main', 'title' => 'Main', 'fields' => [
                    ['name' => 'colour', 'label' => 'Favourite colour', 'type' => 'text', 'required' => true],
                    ['name' => 'firearms', 'label' => 'Firearms', 'type' => 'firearms_table'],
                ],
            ]],
            'document_requirements' => [
                ['kind' => 'id_document', 'label' => 'ID', 'required' => true, 'min_count' => 1],
            ],
        ]);

        foreach ($blocks ?: [
            ['kind' => 'heading', 'settings' => ['text' => 'Motivation', 'level' => 1]],
            ['kind' => 'text', 'settings' => ['body' => 'The applicant {{profile.full_name}} likes {{answers.colour}}.']],
        ] as $i => $block) {
            $type->blocks()->create([
                'sort' => $i,
                'kind' => $block['kind'],
                'settings' => $block['settings'],
                'condition' => $block['condition'] ?? null,
            ]);
        }

        return $type;
    }

    private function makeRequest(ApplicationType $type, array $answers = ['colour' => 'red']): MotivationRequest
    {
        $user = User::create(['name' => 'Jane', 'email' => uniqid().'@example.com', 'password' => 'secret12', 'role' => User::ROLE_CLIENT]);
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

    public function test_placeholders_are_substituted(): void
    {
        $request = $this->makeRequest($this->makeType());
        $result = BookRenderer::render($request);

        $this->assertIsString($result['bytes']);
        $this->assertStringStartsWith('%PDF-', $result['bytes']);
        $this->assertSame([], $result['missing']);
    }

    public function test_missing_placeholders_are_reported(): void
    {
        $type = $this->makeType([
            ['kind' => 'text', 'settings' => ['body' => 'Hello {{answers.unknown_field}}.']],
        ]);
        $request = $this->makeRequest($type, ['colour' => 'blue']);

        $result = BookRenderer::render($request);
        $this->assertContains('answers.unknown_field', $result['missing']);
    }

    public function test_block_condition_hides_block(): void
    {
        $type = $this->makeType([
            ['kind' => 'text', 'settings' => ['body' => 'Should NOT appear'],
                'condition' => ['field' => 'answers.colour', 'operator' => 'equals', 'value' => 'green']],
            ['kind' => 'text', 'settings' => ['body' => 'Should appear']],
        ]);
        $request = $this->makeRequest($type, ['colour' => 'red']);

        $preview = BookRenderer::preview($request)['text'];
        $this->assertStringContainsString('Should appear', $preview);
        $this->assertStringNotContainsString('Should NOT appear', $preview);
    }

    public function test_document_insert_falls_back_to_placeholder_when_no_upload(): void
    {
        $type = $this->makeType([
            ['kind' => 'document_insert', 'settings' => ['divider_title' => 'ID copy', 'kinds' => ['id_document']]],
        ]);
        $request = $this->makeRequest($type);

        $preview = BookRenderer::preview($request)['text'];
        $this->assertStringContainsString('awaiting upload', $preview);
    }

    public function test_province_block_only_renders_the_matching_crime_stats_paragraph(): void
    {
        // Mirrors the Self-Defence seed: one text block per province, each
        // gated to that province. Only the matching paragraph should land
        // in the printed book — the client confirmed province-only variation.
        $type = $this->makeType([
            ['kind' => 'heading', 'settings' => ['text' => 'Crime context in {{answers.province}}', 'level' => 2]],
            [
                'kind' => 'text', 'settings' => ['body' => 'GAUTENG CRIME CONTEXT GOES HERE'],
                'condition' => ['field' => 'answers.province', 'operator' => 'equals', 'value' => 'Gauteng'],
            ],
            [
                'kind' => 'text', 'settings' => ['body' => 'WESTERN CAPE CRIME CONTEXT GOES HERE'],
                'condition' => ['field' => 'answers.province', 'operator' => 'equals', 'value' => 'Western Cape'],
            ],
            [
                'kind' => 'text', 'settings' => ['body' => 'KZN CRIME CONTEXT GOES HERE'],
                'condition' => ['field' => 'answers.province', 'operator' => 'equals', 'value' => 'KwaZulu-Natal'],
            ],
        ]);
        $request = $this->makeRequest($type, ['colour' => 'red', 'province' => 'Gauteng']);

        $preview = BookRenderer::preview($request)['text'];
        $this->assertStringContainsString('Crime context in Gauteng', $preview);
        $this->assertStringContainsString('GAUTENG CRIME CONTEXT GOES HERE', $preview);
        $this->assertStringNotContainsString('WESTERN CAPE CRIME CONTEXT GOES HERE', $preview);
        $this->assertStringNotContainsString('KZN CRIME CONTEXT GOES HERE', $preview);
    }

    public function test_merger_rewrites_placeholder_for_unparseable_bytes(): void
    {
        $pdf = PdfMerger::merge([
            ['type' => 'pdf', 'bytes' => 'not a pdf', 'label' => 'ID copy'],
        ]);
        $this->assertStringStartsWith('%PDF-', $pdf);
    }

    public function test_image_upload_embeds_as_page(): void
    {
        $type = $this->makeType([
            ['kind' => 'document_insert', 'settings' => ['divider_title' => 'ID', 'kinds' => ['id_document']]],
        ]);
        $request = $this->makeRequest($type);
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        Storage::disk('local')->put('motivations/1/id-x.png', $png);
        MotivationDocument::create([
            'motivation_request_id' => $request->id,
            'kind' => 'id_document',
            'disk' => 'local',
            'path' => 'motivations/1/id-x.png',
            'original_name' => 'id.png',
            'mime' => 'image/png',
            'size' => strlen($png),
        ]);

        $pdf = BookRenderer::render($request->fresh(['documents', 'applicationType.blocks', 'user.clientProfile']))['bytes'];
        $this->assertStringStartsWith('%PDF-', $pdf);
    }
}
