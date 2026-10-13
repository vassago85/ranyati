<?php

namespace Tests\Feature\Motivations;

use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationRequest;
use App\Models\MotivationTemplate;
use App\Models\User;
use App\Support\Motivations\Templates\BlockKinds;
use App\Support\Motivations\Templates\LetterComposer;
use App\Support\Motivations\Templates\LetterPdf;
use App\Support\Motivations\Templates\OnePageSelfDefenceTemplate;
use App\Support\Motivations\Templates\RenderContext;
use App\Support\Motivations\Templates\TemplateSelector;
use Database\Seeders\MotivationTypesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OnePageSelfDefenceTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['motivations.storage' => 'local']);
        $this->seed(MotivationTypesSeeder::class);
    }

    public function test_sa_id_is_spaced_the_way_the_printed_header_uses(): void
    {
        $this->assertSame('770912 5034 08 1', ClientProfile::formatSaIdNumber('7709125034081'));
        $this->assertSame('770912 5034 08 1', ClientProfile::formatSaIdNumber('770912 5034 08 1'));
    }

    public function test_seeder_publishes_the_one_pager_as_the_self_defence_default(): void
    {
        $type = ApplicationType::where('slug', 'self-defence')->firstOrFail();
        $template = $type->templates()->wherePivot('is_default', true)->firstOrFail();

        $this->assertSame(OnePageSelfDefenceTemplate::SLUG, $template->slug);
        $this->assertSame(MotivationTemplate::STATUS_PUBLISHED, $template->status);
        $this->assertNotNull($template->publishedVersion);
    }

    public function test_one_pager_has_no_page_breaks_title_page_or_document_inserts(): void
    {
        $forbidden = [
            BlockKinds::PAGE_BREAK,
            BlockKinds::TITLE_PAGE,
            BlockKinds::DOCUMENT_INSERT,
        ];

        foreach (OnePageSelfDefenceTemplate::blocks() as $block) {
            $this->assertNotContains($block['kind'], $forbidden, $block['kind'].' would push the letter past one page');
        }
    }

    public function test_second_seed_does_not_create_another_published_version(): void
    {
        $first = MotivationTemplate::where('slug', OnePageSelfDefenceTemplate::SLUG)->firstOrFail();
        $versionId = $first->published_version_id;
        $count = $first->versions()->count();

        OnePageSelfDefenceTemplate::seed();

        $again = $first->fresh();
        $this->assertSame($versionId, $again->published_version_id);
        $this->assertSame($count, $again->versions()->count());
    }

    public function test_botha_sample_fills_name_spaced_id_work_and_firearm(): void
    {
        $type = ApplicationType::where('slug', 'self-defence')->firstOrFail();
        $request = $this->bothaRequest($type);

        $version = TemplateSelector::for($request);
        $this->assertNotNull($version);
        $this->assertSame(OnePageSelfDefenceTemplate::SLUG, $version->template->slug);

        $result = (new LetterComposer($request, RenderContext::for(
            $request,
            RenderContext::firearm(1, $request->answers['firearms'][0]),
        )))->compose((array) $version->blocks);

        $text = LetterPdf::previewText($result['segments']);
        $settings = LetterPdf::resolveHeaderFooter((array) $version->settings, RenderContext::for($request));

        $this->assertSame(1, count(array_filter($result['segments'], fn ($s) => ($s['type'] ?? '') === 'html')));
        $this->assertStringContainsString('PETRUS JOHANNES BOTHA', $text);
        $this->assertStringContainsString('770912 5034 08 1', $text);
        $this->assertStringContainsString('196 Riverton Avenue', $text);
        $this->assertStringContainsString('Hay Bot Projects', $text);
        $this->assertStringContainsString('Building Contractor', $text);
        $this->assertStringContainsString('self-employed', $text);
        $this->assertStringContainsString('R254902', $text);
        $this->assertStringContainsString('Canik', $text);
        $this->assertStringContainsString('MC9L', $text);
        $this->assertStringContainsString('PETRUS JOHANNES BOTHA ID: 770912 5034 08 1 Initial_________', $settings['header']['text']);
        $this->assertStringNotContainsString('Supporting documents', $text);
        $this->assertStringNotContainsString('Lieutenant-General', $text);
    }

    public function test_one_pager_renders_a_pdf(): void
    {
        $type = ApplicationType::where('slug', 'self-defence')->firstOrFail();
        $request = $this->bothaRequest($type);
        $version = TemplateSelector::for($request);

        $context = RenderContext::for($request, RenderContext::firearm(1, $request->answers['firearms'][0]));
        $result = (new LetterComposer($request, $context))->compose((array) $version->blocks);
        $pdf = LetterPdf::render($result['segments'], (array) $version->settings, $context);

        $this->assertStringStartsWith('%PDF', $pdf);
    }

    private function bothaRequest(ApplicationType $type): MotivationRequest
    {
        $user = User::create([
            'name' => 'Petrus Botha',
            'email' => uniqid().'@example.com',
            'password' => 'secret12',
            'role' => User::ROLE_CLIENT,
        ]);
        ClientProfile::create([
            'user_id' => $user->id,
            'full_names' => 'PETRUS JOHANNES',
            'surname' => 'BOTHA',
            'id_number' => '7709125034081',
            'physical_address' => '196 Riverton Avenue, Clubview, Centurion',
            'employer' => 'Hay Bot Projects cc',
            'self_employed' => true,
            'occupation' => 'Building Contractor',
            'cell' => '0820000000',
            'email' => $user->email,
        ]);

        return $user->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_SUBMITTED,
            'answers' => [
                'firearms' => [[
                    'type' => 'Pistol',
                    'calibre' => '9mm Parabellum',
                    'serial' => 'R254902',
                    'action' => 'Semi-auto',
                    'make' => 'Canik',
                    'model' => 'METE MC9L',
                ]],
                'province' => 'Gauteng',
                'policing_precinct' => 'Clubview',
                'employer_specialises' => 'We specialise in the construction and building sectors.',
                'employment_profile' => [
                    'travel_work' => 'yes',
                    'after_hours' => 'yes',
                    'site_visits' => 'yes',
                    'travel_alone' => 'yes',
                    'transport_laptop' => 'yes',
                    'cash_business' => 'yes',
                ],
                'other_profile' => [
                    'family_travel' => 'yes',
                    'victim' => 'yes',
                ],
                'home_security' => [
                    'armed_response' => 'yes',
                ],
            ],
        ]);
    }
}
