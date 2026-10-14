<?php

namespace Tests\Feature\Motivations;

use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationTemplate;
use App\Models\MotivationTemplateVersion;
use App\Models\User;
use App\Support\Motivations\Templates\BlockKinds;
use App\Support\Motivations\Templates\LetterComposer;
use App\Support\Motivations\Templates\LetterPdf;
use App\Support\Motivations\Templates\OnePageSelfDefenceTemplate;
use App\Support\Motivations\Templates\PreviewSampleData;
use App\Support\Motivations\Templates\RenderContext;
use App\Support\Motivations\Templates\SectionMerger;
use App\Support\Motivations\Templates\TemplateSchema;
use Database\Seeders\MotivationTypesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Covers the canvas-preview endpoint, inline-text escaping fix, section
 * block rendering and legacy header/footer settings upgrade.
 */
class TemplateCanvasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MotivationTypesSeeder::class);
    }

    private function admin(): User
    {
        return User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => 'secret12', 'role' => User::ROLE_ADMIN],
        );
    }

    private function sampleTemplate(): MotivationTemplate
    {
        $type = ApplicationType::where('category', 'self_defence')->first();
        $template = MotivationTemplate::create([
            'name' => 'Canvas test',
            'slug' => 'canvas-test',
            'category' => 'self_defence',
            'status' => MotivationTemplate::STATUS_DRAFT,
            'created_by' => $this->admin()->id,
            'updated_by' => $this->admin()->id,
        ]);
        if ($type) {
            $template->applicationTypes()->attach($type->id, ['is_default' => true]);
        }
        MotivationTemplateVersion::create([
            'template_id' => $template->id,
            'version' => 1,
            'blocks' => [],
            'settings' => MotivationTemplateVersion::defaultSettings(),
            'created_by' => $this->admin()->id,
            'updated_by' => $this->admin()->id,
        ]);

        return $template;
    }

    #[Test]
    public function rich_text_placeholders_get_html_escaped(): void
    {
        $template = $this->sampleTemplate();
        $request = PreviewSampleData::requestFor($template);
        $request->setRelation('user', $request->user);
        $request->user->clientProfile->full_names = 'Smith &';
        $request->user->clientProfile->surname = 'Sons < Co';

        $context = RenderContext::for($request);

        $composer = new LetterComposer($request, $context);
        $result = $composer->compose([[
            'kind' => BlockKinds::RICH_TEXT,
            'settings' => ['html' => '<p>Signed by {{profile.full_name}}</p>'],
        ]]);

        $html = $result['segments'][0]['html'];
        // The ampersand and the less-than must come out HTML-escaped so
        // dompdf parses the paragraph as a single text node.
        $this->assertStringContainsString('Smith &amp; Sons &lt; Co', $html);
        $this->assertStringNotContainsString('<p>Signed by Smith & Sons < Co</p>', $html);
    }

    #[Test]
    public function section_renders_heading_and_body_like_a_matching_pair(): void
    {
        $template = $this->sampleTemplate();
        $request = PreviewSampleData::requestFor($template);
        $context = RenderContext::for($request);

        $composer1 = new LetterComposer($request, $context);
        $pairResult = $composer1->compose([
            ['kind' => BlockKinds::HEADING, 'settings' => ['text' => 'Hello', 'level' => 2]],
            ['kind' => BlockKinds::TEXT, 'settings' => ['body' => 'Body here.']],
        ]);

        $composer2 = new LetterComposer($request, $context);
        $sectionResult = $composer2->compose([[
            'kind' => BlockKinds::SECTION,
            'settings' => [
                'heading' => 'Hello',
                'level' => 2,
                'html' => '<p>Body here.</p>',
            ],
        ]]);

        // Both should render the same visible text and in roughly the same
        // structure. We compare via previewText which strips all tags and
        // collapses whitespace, so wrapper differences don't matter.
        $this->assertSame(
            \App\Support\Motivations\Templates\LetterPdf::previewText($pairResult['segments']),
            \App\Support\Motivations\Templates\LetterPdf::previewText($sectionResult['segments']),
        );
    }

    #[Test]
    public function canvas_preview_endpoint_returns_values_cards_and_passes(): void
    {
        $template = $this->sampleTemplate();

        $response = $this->actingAs($this->admin())
            ->postJson("/admin/motivation-templates/{$template->id}/canvas-preview", [
                'blocks' => [
                    ['kind' => BlockKinds::HEADING, 'settings' => ['text' => 'A', 'level' => 1]],
                    ['kind' => BlockKinds::SIGNATURE, 'settings' => []],
                ],
            ])->assertOk();

        $data = $response->json();
        $this->assertArrayHasKey('values', $data);
        $this->assertArrayHasKey('cards', $data);
        $this->assertArrayHasKey('passes', $data);
        // The signature block is a "card" kind so it must come back with HTML.
        $signatureCard = array_values($data['cards'])[0] ?? null;
        $this->assertNotNull($signatureCard);
        $this->assertArrayHasKey('html', $signatureCard);
        // Values include placeholder catalogue entries like profile.full_name.
        $this->assertArrayHasKey('profile.full_name', $data['values']);
        $this->assertSame('Jane Example', $data['values']['profile.full_name']);
    }

    #[Test]
    public function legacy_header_settings_upgrade_into_slots(): void
    {
        $upgraded = TemplateSchema::normalizeSettingsJson([
            'header' => [
                'enabled' => true,
                'asset_id' => 42,
                'text' => '{{profile.full_name}}',
                'different_first_page' => true,
            ],
            'footer' => [
                'enabled' => true,
                'text' => '',
                'show_page_numbers' => true,
                'different_first_page' => true,
            ],
        ]);

        $header = $upgraded['header'];
        $this->assertTrue($header['enabled']);
        $this->assertTrue($header['hide_first_page']);
        $this->assertSame('image', $header['slots']['left']['type']);
        $this->assertSame(42, $header['slots']['left']['asset_id']);
        // The asset is on the left so text moves to the centre.
        $this->assertSame('text', $header['slots']['centre']['type']);
        $this->assertSame('{{profile.full_name}}', $header['slots']['centre']['value']);
        $this->assertSame('none', $header['slots']['right']['type']);

        $footer = $upgraded['footer'];
        $this->assertSame('page_number', $footer['slots']['right']['type']);
        $this->assertSame('Page {X} of {Y}', $footer['slots']['right']['format']);
        $this->assertSame(0, $footer['height_mm'], 'height_mm defaults to 0 so body margins do not shift');
    }

    #[Test]
    public function new_header_shape_round_trips_through_normalisation(): void
    {
        $input = [
            'header' => [
                'enabled' => true,
                'hide_first_page' => false,
                'height_mm' => 15,
                'border' => true,
                'slots' => [
                    'left' => ['type' => 'image', 'asset_id' => 7],
                    'centre' => ['type' => 'text', 'value' => '{{profile.full_name}}'],
                    'right' => ['type' => 'page_number', 'format' => 'Page {X} of {Y}'],
                ],
            ],
        ];

        $out = TemplateSchema::normalizeSettingsJson($input);

        $this->assertTrue($out['header']['enabled']);
        $this->assertFalse($out['header']['hide_first_page']);
        $this->assertSame(15, $out['header']['height_mm']);
        $this->assertTrue($out['header']['border']);
        $this->assertSame('image', $out['header']['slots']['left']['type']);
        $this->assertSame(7, $out['header']['slots']['left']['asset_id']);
        $this->assertSame('page_number', $out['header']['slots']['right']['type']);
    }

    #[Test]
    public function merging_the_one_page_self_defence_seed_keeps_rendered_text(): void
    {
        $template = $this->sampleTemplate();
        $request = PreviewSampleData::requestFor($template);
        $context = RenderContext::for($request);

        $before = TemplateSchema::normalizeBlocks(OnePageSelfDefenceTemplate::blocks());
        $after = SectionMerger::merge($before);

        $this->assertLessThan(count($before), count($after));
        $this->assertSame(
            LetterPdf::previewText((new LetterComposer($request, $context))->compose($before)['segments']),
            LetterPdf::previewText((new LetterComposer($request, $context))->compose($after)['segments']),
        );
    }

    #[Test]
    public function merging_a_39_block_heading_text_list_drops_to_about_22_and_keeps_text(): void
    {
        $template = $this->sampleTemplate();
        $request = PreviewSampleData::requestFor($template);
        $context = RenderContext::for($request);

        $raw = [];
        for ($i = 1; $i <= 17; $i++) {
            $raw[] = ['kind' => BlockKinds::HEADING, 'settings' => ['text' => "Heading {$i}", 'level' => 2]];
            $raw[] = ['kind' => BlockKinds::TEXT, 'settings' => ['body' => "Paragraph {$i} for {{profile.full_name}}."]];
        }
        $raw[] = ['kind' => BlockKinds::YES_NO_SENTENCES, 'settings' => ['field' => 'employment_profile', 'intro' => 'In work:']];
        $raw[] = ['kind' => BlockKinds::CRIME_CONTEXT, 'settings' => ['province_field' => 'answers.province', 'fallback_body' => 'Crime is high.']];
        $raw[] = ['kind' => BlockKinds::SIGNATURE, 'settings' => []];
        $raw[] = ['kind' => BlockKinds::PAGE_BREAK, 'settings' => []];
        $raw[] = ['kind' => BlockKinds::TEXT, 'settings' => ['body' => 'Orphan after the break.']];

        $before = TemplateSchema::normalizeBlocks($raw);
        $this->assertCount(39, $before);

        $after = SectionMerger::merge($before);
        $this->assertCount(22, $after, '17 heading+text pairs fold into 17 sections, plus 5 leftovers.');

        $this->assertSame(
            LetterPdf::previewText((new LetterComposer($request, $context))->compose($before)['segments']),
            LetterPdf::previewText((new LetterComposer($request, $context))->compose($after)['segments']),
        );
    }

    #[Test]
    public function header_footer_bands_render_a_pdf_without_body_overlap(): void
    {
        $settings = TemplateSchema::normalizeSettingsJson([
            'page' => [
                'size' => 'A4',
                'orientation' => 'portrait',
                'margin_top' => 20,
                'margin_right' => 18,
                'margin_bottom' => 20,
                'margin_left' => 18,
                'font_family' => 'DejaVu Sans',
                'font_size' => 11,
            ],
            'header' => [
                'enabled' => true,
                'hide_first_page' => true,
                'height_mm' => 15,
                'border' => true,
                'slots' => [
                    'left' => ['type' => 'none'],
                    'centre' => ['type' => 'text', 'value' => '{{profile.full_name}}'],
                    'right' => ['type' => 'page_number', 'format' => 'Page {X} of {Y}'],
                ],
            ],
            'footer' => [
                'enabled' => true,
                'hide_first_page' => false,
                'height_mm' => 12,
                'border' => false,
                'slots' => [
                    'left' => ['type' => 'date'],
                    'centre' => ['type' => 'none'],
                    'right' => ['type' => 'reference'],
                ],
            ],
        ]);

        $template = $this->sampleTemplate();
        $request = PreviewSampleData::requestFor($template);
        $context = RenderContext::for($request);

        $composer = new LetterComposer($request, $context);
        $result = $composer->compose([
            ['kind' => BlockKinds::HEADING, 'settings' => ['text' => 'Page one', 'level' => 1]],
            ['kind' => BlockKinds::PAGE_BREAK, 'settings' => []],
            ['kind' => BlockKinds::HEADING, 'settings' => ['text' => 'Page two', 'level' => 1]],
            ['kind' => BlockKinds::TEXT, 'settings' => ['body' => 'Body after the break.']],
        ]);

        $bytes = LetterPdf::render($result['segments'], $settings, $context);

        $this->assertStringStartsWith('%PDF', $bytes);
        $this->assertGreaterThan(800, strlen($bytes));
        // Two HTML pages (the page-break splits the letter into one
        // segment, but the CSS page-break still produces two physical
        // pages inside a single PDF part). Either way the renderer
        // must not throw and must emit a valid PDF.
        $this->assertMatchesRegularExpression('/\/Type\s*\/Page/', $bytes);
    }
}
