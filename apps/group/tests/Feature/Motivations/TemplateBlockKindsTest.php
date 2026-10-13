<?php

namespace Tests\Feature\Motivations;

use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationRequest;
use App\Models\TemplateAsset;
use App\Models\User;
use App\Support\Motivations\Templates\LetterComposer;
use App\Support\Motivations\Templates\LetterPdf;
use App\Support\Motivations\Templates\RenderContext;
use App\Support\Motivations\Templates\RichText;
use App\Support\Motivations\Templates\TemplateSchema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Phase 3 — exercise every new block kind + rich text sanitiser + header/
 * footer settings end to end. We compose segments directly so these tests
 * stay fast; LetterPdf is only invoked for the header/footer check, which
 * is where the DomPDF-side wiring matters.
 */
class TemplateBlockKindsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        config(['motivations.storage' => 'local']);
    }

    private function makeType(): ApplicationType
    {
        return ApplicationType::create([
            'name' => 'Composer type',
            'slug' => 'composer-type',
            'category' => 'sport',
            'is_active' => true,
            'has_motivation' => true,
            'questions' => [[
                'key' => 'main', 'title' => 'Main', 'fields' => [
                    ['name' => 'colour', 'label' => 'Favourite colour', 'type' => 'text', 'required' => true],
                    ['name' => 'province', 'label' => 'Province', 'type' => 'select',
                        'options' => [
                            ['value' => 'Gauteng', 'label' => 'Gauteng'],
                            ['value' => 'Western Cape', 'label' => 'Western Cape'],
                        ],
                    ],
                    [
                        'name' => 'activities', 'label' => 'Activities', 'type' => 'yes_no_list',
                        'items' => [
                            ['name' => 'ipsc', 'label' => 'IPSC', 'sentence' => 'I shoot IPSC regularly.'],
                            ['name' => 'idpa', 'label' => 'IDPA', 'sentence' => ''],
                            ['name' => 'other', 'label' => 'Other'],
                        ],
                    ],
                    ['name' => 'firearms', 'label' => 'Firearms', 'type' => 'firearms_table'],
                ],
            ]],
            'document_requirements' => [
                ['kind' => 'id_document', 'label' => 'ID', 'required' => true, 'min_count' => 1],
            ],
        ]);
    }

    private function makeRequest(ApplicationType $type, array $answers): MotivationRequest
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

    private function compose(MotivationRequest $request, array $blocks): array
    {
        $context = RenderContext::for($request);
        $composer = new LetterComposer($request, $context);

        return $composer->compose(TemplateSchema::normalizeBlocks($blocks));
    }

    private function joined(array $segments): string
    {
        return collect($segments)->pluck('html')->filter()->implode("\n");
    }

    public function test_title_page_renders_as_its_own_segment_and_forces_a_break(): void
    {
        $r = $this->makeRequest($this->makeType(), ['colour' => 'red']);
        $result = $this->compose($r, [
            ['kind' => 'title_page', 'settings' => [
                'title' => 'Firearm Motivation', 'subtitle' => 'For {{profile.full_name}}', 'body' => 'Prepared on {{today}}',
            ]],
            ['kind' => 'text', 'settings' => ['body' => 'The letter itself.']],
        ]);

        $this->assertCount(2, $result['segments']);
        $this->assertStringContainsString('title-page', $result['segments'][0]['html']);
        $this->assertStringContainsString('Firearm Motivation', $result['segments'][0]['html']);
        $this->assertStringContainsString('page-break', $result['segments'][0]['html']);
        $this->assertStringContainsString('Jane Doe', $result['segments'][0]['html']);
    }

    public function test_rich_text_sanitises_scripts_and_keeps_safe_tags(): void
    {
        $r = $this->makeRequest($this->makeType(), ['colour' => 'red']);

        $dangerous = '<p>Hello <strong>world</strong><script>alert(1)</script><a href="javascript:evil()">bad</a> <a href="https://nrapa.co.za">good</a></p>';
        $result = $this->compose($r, [
            ['kind' => 'rich_text', 'settings' => ['html' => $dangerous]],
        ]);

        $html = $this->joined($result['segments']);
        $this->assertStringContainsString('<strong>world</strong>', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('https://nrapa.co.za', $html);
    }

    public function test_image_block_embeds_an_asset_as_data_uri(): void
    {
        $r = $this->makeRequest($this->makeType(), []);
        $asset = $this->seedAsset();

        $result = $this->compose($r, [
            ['kind' => 'image', 'settings' => [
                'asset_id' => $asset->id,
                'width' => 50,
                'align' => 'center',
                'caption' => 'The club logo',
            ]],
        ]);

        $html = $this->joined($result['segments']);
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringContainsString('The club logo', $html);
    }

    public function test_table_firearms_uses_the_motivation_firearms_table(): void
    {
        $type = $this->makeType();
        $r = $this->makeRequest($type, [
            'firearms' => [
                ['make' => 'Canik', 'model' => 'TP9', 'serial' => 'C001', 'calibre' => '9mm', 'action' => 'Semi-auto'],
                ['make' => 'Glock', 'model' => '19', 'serial' => 'G002', 'calibre' => '9mm', 'action' => 'Semi-auto'],
            ],
        ]);

        $result = $this->compose($r, [
            ['kind' => 'table', 'settings' => ['source' => 'firearms']],
        ]);

        $html = $this->joined($result['segments']);
        $this->assertStringContainsString('C001', $html);
        $this->assertStringContainsString('G002', $html);
        $this->assertStringContainsString('<table', $html);
    }

    public function test_yes_no_sentences_includes_items_with_sentences_and_falls_back_to_label(): void
    {
        $type = $this->makeType();
        $r = $this->makeRequest($type, [
            'activities' => ['ipsc' => 'yes', 'idpa' => 'yes', 'other' => 'no'],
        ]);

        $result = $this->compose($r, [
            ['kind' => 'yes_no_sentences', 'settings' => [
                'field' => 'activities', 'intro' => 'I compete in:', 'empty_sentence' => 'label',
            ]],
        ]);

        $html = $this->joined($result['segments']);
        $this->assertStringContainsString('I compete in:', $html);
        $this->assertStringContainsString('I shoot IPSC regularly.', $html);
        // IDPA has no sentence; `empty_sentence=label` should surface the label.
        $this->assertStringContainsString('IDPA', $html);
    }

    public function test_two_column_lays_children_out_side_by_side(): void
    {
        $r = $this->makeRequest($this->makeType(), []);

        $result = $this->compose($r, [
            ['kind' => 'two_column', 'settings' => [
                'left' => [['kind' => 'text', 'settings' => ['body' => 'Left copy.']]],
                'right' => [['kind' => 'text', 'settings' => ['body' => 'Right copy.']]],
                'left_width' => 60,
            ]],
        ]);

        $html = $this->joined($result['segments']);
        $this->assertStringContainsString('Left copy', $html);
        $this->assertStringContainsString('Right copy', $html);
        $this->assertMatchesRegularExpression('/width:\s*60%/i', $html);
    }

    public function test_signature_block_renders_name_and_date(): void
    {
        $r = $this->makeRequest($this->makeType(), []);

        $result = $this->compose($r, [
            ['kind' => 'signature', 'settings' => [
                'name' => '{{profile.full_name}}',
                'id_number' => '{{profile.id_number}}',
                'date' => '{{today}}',
                'place' => 'Johannesburg',
            ]],
        ]);

        $html = $this->joined($result['segments']);
        $this->assertStringContainsString('Jane Doe', $html);
        $this->assertStringContainsString('9001011234080', $html);
        $this->assertStringContainsString('Johannesburg', $html);
    }

    public function test_crime_context_picks_the_matching_province_body(): void
    {
        $type = $this->makeType();
        $r = $this->makeRequest($type, ['province' => 'Gauteng']);

        $result = $this->compose($r, [
            ['kind' => 'crime_context', 'settings' => [
                'province_field' => 'answers.province',
                'bodies' => [
                    'Gauteng' => 'Gauteng has a high crime rate.',
                    'Western Cape' => 'Western Cape has a different pattern.',
                ],
                'fallback_body' => 'National figures vary.',
            ]],
        ]);

        $html = $this->joined($result['segments']);
        $this->assertStringContainsString('Gauteng has a high crime rate.', $html);
        $this->assertStringNotContainsString('Western Cape', $html);
    }

    public function test_crime_context_falls_back_when_province_is_unknown(): void
    {
        $r = $this->makeRequest($this->makeType(), ['province' => 'Mars']);

        $result = $this->compose($r, [
            ['kind' => 'crime_context', 'settings' => [
                'province_field' => 'answers.province',
                'bodies' => ['Gauteng' => 'G'],
                'fallback_body' => 'National figures.',
            ]],
        ]);

        $this->assertStringContainsString('National figures.', $this->joined($result['segments']));
    }

    public function test_conditional_group_gates_children(): void
    {
        $r = $this->makeRequest($this->makeType(), ['colour' => 'red']);

        $result = $this->compose($r, [
            ['kind' => 'conditional_group',
                'condition' => ['field' => 'answers.colour', 'operator' => 'equals', 'value' => 'red'],
                'settings' => ['children' => [
                    ['kind' => 'text', 'settings' => ['body' => 'Red-only paragraph.']],
                ]],
            ],
            ['kind' => 'conditional_group',
                'condition' => ['field' => 'answers.colour', 'operator' => 'equals', 'value' => 'blue'],
                'settings' => ['children' => [
                    ['kind' => 'text', 'settings' => ['body' => 'Blue-only paragraph.']],
                ]],
            ],
        ]);

        $html = $this->joined($result['segments']);
        $this->assertStringContainsString('Red-only paragraph.', $html);
        $this->assertStringNotContainsString('Blue-only paragraph.', $html);
    }

    public function test_divider_and_spacer_render_without_text(): void
    {
        $r = $this->makeRequest($this->makeType(), []);

        $result = $this->compose($r, [
            ['kind' => 'text', 'settings' => ['body' => 'Before.']],
            ['kind' => 'divider', 'settings' => ['style' => 'dashed']],
            ['kind' => 'spacer', 'settings' => ['size' => 20]],
            ['kind' => 'text', 'settings' => ['body' => 'After.']],
        ]);

        $html = $this->joined($result['segments']);
        $this->assertStringContainsString('dashed', $html);
        $this->assertStringContainsString('height:20pt', $html);
        $this->assertStringContainsString('Before.', $html);
        $this->assertStringContainsString('After.', $html);
    }

    public function test_rich_text_sanitiser_keeps_allowlisted_styles(): void
    {
        $html = RichText::sanitize('<p style="text-align:center;color:red;background:black;">A</p>'
            .'<span style="font-weight:bold">B</span>');
        // Allowed: text-align, color (but not background).
        $this->assertStringContainsString('text-align', $html);
        $this->assertStringContainsString('color', $html);
        $this->assertStringNotContainsString('background', $html);
    }

    public function test_running_header_resolves_placeholders_against_request_context(): void
    {
        $r = $this->makeRequest($this->makeType(), ['colour' => 'red']);

        $context = RenderContext::for($r);
        $resolved = LetterPdf::resolveHeaderFooter([
            'header' => [
                'enabled' => true,
                'text' => '{{profile.full_name}} ID: {{profile.id_number}} Initial_________',
                'different_first_page' => false,
            ],
            'footer' => [
                'enabled' => true,
                'text' => 'Page footer for {{profile.full_name}}',
                'show_page_numbers' => true,
                'different_first_page' => false,
            ],
            'page' => ['size' => 'A4', 'orientation' => 'portrait', 'margin_top' => 20, 'margin_right' => 18, 'margin_bottom' => 20, 'margin_left' => 18, 'font_family' => 'DejaVu Sans', 'font_size' => 11],
        ], $context);

        $this->assertSame('Jane Doe ID: 9001011234080 Initial_________', $resolved['header']['text']);
        $this->assertSame('Page footer for Jane Doe', $resolved['footer']['text']);
    }

    public function test_running_header_escapes_resolved_values_but_keeps_admin_typed_html(): void
    {
        $type = $this->makeType();
        $user = User::create([
            'name' => 'Jane',
            'email' => uniqid().'@example.com',
            'password' => 'secret12',
            'role' => User::ROLE_CLIENT,
        ]);
        // A name with HTML-breaking characters must not leak into the layout.
        ClientProfile::create([
            'user_id' => $user->id,
            'full_names' => 'A<B>',
            'surname' => 'D"E',
            'id_number' => '9001011234080',
            'physical_address' => '1 Test Rd',
            'occupation' => 'Pharmacist',
            'cell' => '0821234567',
            'email' => $user->email,
        ]);
        $r = $user->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_SUBMITTED,
            'answers' => ['colour' => 'red'],
        ]);

        $resolved = LetterPdf::resolveHeaderFooter([
            'header' => [
                'enabled' => true,
                'text' => '<strong>{{profile.full_name}}</strong> ID: {{profile.id_number}}',
                'different_first_page' => false,
            ],
            'footer' => ['enabled' => false, 'text' => '', 'show_page_numbers' => false, 'different_first_page' => false],
            'page' => ['size' => 'A4', 'orientation' => 'portrait', 'margin_top' => 20, 'margin_right' => 18, 'margin_bottom' => 20, 'margin_left' => 18, 'font_family' => 'DejaVu Sans', 'font_size' => 11],
        ], RenderContext::for($r));

        $this->assertStringContainsString('<strong>', $resolved['header']['text']);
        $this->assertStringContainsString('A&lt;B&gt; D&quot;E', $resolved['header']['text']);
        $this->assertStringNotContainsString('<B>', $resolved['header']['text']);
    }

    public function test_pdf_render_honours_header_and_footer_text_and_page_numbers(): void
    {
        $r = $this->makeRequest($this->makeType(), ['colour' => 'red']);

        $context = RenderContext::for($r);
        $composer = new LetterComposer($r, $context);
        $segments = $composer->compose(TemplateSchema::normalizeBlocks([
            ['kind' => 'text', 'settings' => ['body' => 'Hello.']],
        ]))['segments'];

        $settings = [
            'page' => ['size' => 'A4', 'orientation' => 'portrait', 'margin_top' => 20, 'margin_right' => 18, 'margin_bottom' => 20, 'margin_left' => 18, 'font_family' => 'DejaVu Sans', 'font_size' => 11],
            'header' => ['enabled' => true, 'text' => 'My header line', 'different_first_page' => false],
            'footer' => ['enabled' => true, 'text' => 'My footer line', 'show_page_numbers' => true, 'different_first_page' => false],
            'letter_mode' => 'per_firearm',
        ];
        $pdf = LetterPdf::render($segments, $settings);

        $this->assertStringStartsWith('%PDF', $pdf);
        // Preview text is a plain summary of the HTML segments.
        $text = LetterPdf::previewText($segments);
        $this->assertStringContainsString('Hello.', $text);
    }

    private function seedAsset(): TemplateAsset
    {
        $file = UploadedFile::fake()->image('logo.png', 200, 100);
        $path = $file->store('motivation-assets', 'public');

        return TemplateAsset::create([
            'name' => 'Logo',
            'category' => 'logo',
            'path' => $path,
            'mime' => 'image/png',
            'width' => 200,
            'height' => 100,
            'size' => 1024,
        ]);
    }
}
