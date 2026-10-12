<?php

namespace Tests\Feature\Motivations;

use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationRequest;
use App\Models\User;
use App\Support\Motivations\BookRenderer;
use Database\Seeders\MotivationTypesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Guards the content shipped with the Self-Defence, Sport-Shooting and
 * Hunting application-type seeds. These are built from real client
 * motivations (PII removed) so we want end-to-end proof that:
 *
 *   • every placeholder resolves (no `[MISSING: ...]` tokens),
 *   • the right section-quote block fires for 13 / 15 / 16,
 *   • the right discipline / hunting-role sub-block fires,
 *   • province-specific crime-stats blocks are gated correctly, and
 *   • cross-section wording does NOT leak into the printed book.
 */
class SeededMotivationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['motivations.storage' => 'local']);
        $this->seed(MotivationTypesSeeder::class);
    }

    private function applicant(): User
    {
        $u = User::create([
            'name' => 'Jane Example',
            'email' => 'applicant-'.uniqid().'@example.com',
            'password' => 'secret12',
            'role' => User::ROLE_CLIENT,
        ]);
        ClientProfile::create([
            'user_id' => $u->id,
            'full_names' => 'Jane',
            'surname' => 'Example',
            'id_number' => '9001011234080',
            'physical_address' => '1 Sample Street, Pretoria',
            'occupation' => 'IT Consultant',
            'cell' => '0821234567',
            'email' => $u->email,
        ]);

        return $u;
    }

    private function render(ApplicationType $type, array $answers): array
    {
        $request = $this->applicant()->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => $answers,
        ]);

        return BookRenderer::preview($request);
    }

    private function sampleFirearm(): array
    {
        return [[
            'type' => 'Pistol',
            'calibre' => '9mm',
            'make' => 'Canik',
            'model' => 'METE MC9 L',
            'serial' => 'TEST-SERIAL',
        ]];
    }

    public function test_self_defence_renders_canik_wording_with_gauteng_block_only(): void
    {
        $type = ApplicationType::where('slug', 'self-defence')->firstOrFail();

        $preview = $this->render($type, [
            'firearms' => $this->sampleFirearm(),
            'province' => 'Gauteng',
            'policing_precinct' => 'Lyttelton',
            'employment_profile' => ['travel_other_cities' => 'yes', 'work_shifts' => 'yes'],
            'other_profile' => ['travel_family' => 'yes'],
            'home_security' => ['electric_fencing' => 'yes', 'armed_response' => 'yes'],
        ]);

        $text = $preview['text'];
        $this->assertSame([], $preview['missing'], 'Every placeholder in the SD seed must resolve.');

        // Core content from the live Canik SD doc (PII-free).
        $this->assertStringContainsString('I, the undersigned, Jane Example', $text);
        $this->assertStringContainsString('9mm Canik METE MC9 L', $text);
        $this->assertStringContainsString('for self-defence in terms of section 13', $text);
        $this->assertStringContainsString('Fundamental rules for safe gun handling', $text);
        $this->assertStringContainsString('Section 9(2) compliance statement', $text);

        // Only Gauteng's crime-context block must appear.
        $this->assertStringContainsString('Gauteng remains the province with the highest recorded', $text);
        $this->assertStringNotContainsString('Western Cape, and in particular the Cape Town metro', $text);
        $this->assertStringNotContainsString('Northern Cape', $text);
    }

    public function test_self_defence_switches_crime_context_when_province_changes(): void
    {
        $type = ApplicationType::where('slug', 'self-defence')->firstOrFail();

        $preview = $this->render($type, [
            'firearms' => $this->sampleFirearm(),
            'province' => 'KwaZulu-Natal',
            'policing_precinct' => 'Durban Central',
            'employment_profile' => ['travel_other_cities' => 'yes'],
            'other_profile' => ['travel_family' => 'yes'],
            'home_security' => ['armed_response' => 'yes'],
        ]);

        $text = $preview['text'];
        $this->assertSame([], $preview['missing']);
        $this->assertStringContainsString('KwaZulu-Natal remains one of South Africa', $text);
        $this->assertStringNotContainsString('Gauteng remains the province with the highest recorded', $text);
    }

    public function test_sport_section_15_occasional_ipsc_production(): void
    {
        $type = ApplicationType::where('slug', 'sport-shooting')->firstOrFail();

        $preview = $this->render($type, [
            'firearms' => $this->sampleFirearm(),
            'section' => '15 — Occasional sports person',
            'discipline' => 'IPSC Production',
        ]);

        $text = $preview['text'];
        $this->assertSame([], $preview['missing']);
        $this->assertStringContainsString('occasional sport shooting in terms of section 15', $text);
        $this->assertStringNotContainsString('dedicated sport shooting in terms of section 16', $text);
        $this->assertStringContainsString('IPSC Production, Minor-power-factor class', $text);
        $this->assertStringNotContainsString('SADPA Ultra Compact division', $text);
    }

    public function test_sport_section_16_dedicated_sadpa_ultra_compact(): void
    {
        $type = ApplicationType::where('slug', 'sport-shooting')->firstOrFail();

        $preview = $this->render($type, [
            'firearms' => $this->sampleFirearm(),
            'section' => '16 — Dedicated sports person',
            'discipline' => 'SADPA Ultra Compact',
            'association_name' => 'SADPA',
            'association_number' => 'SADPA-1234',
        ]);

        $text = $preview['text'];
        $this->assertSame([], $preview['missing']);
        $this->assertStringContainsString('dedicated sport shooting in terms of section 16', $text);
        $this->assertStringNotContainsString('occasional sport shooting in terms of section 15', $text);
        $this->assertStringContainsString('SADPA Ultra Compact division', $text);
        $this->assertStringNotContainsString('IPSC Production, Minor-power-factor class', $text);
        $this->assertStringContainsString('active member of SADPA', $text);
    }

    public function test_hunting_section_15_backup_handgun(): void
    {
        $type = ApplicationType::where('slug', 'hunting')->firstOrFail();

        $preview = $this->render($type, [
            'firearms' => $this->sampleFirearm(),
            'section' => '15 — Occasional hunter',
            'hunting_role' => 'Backup — close-range follow-up / dangerous game',
            'hunting_frequency' => 'twice a year',
            'hunting_locations' => 'Limpopo bushveld',
            'species' => 'kudu, bushpig',
        ]);

        $text = $preview['text'];
        $this->assertSame([], $preview['missing']);
        $this->assertStringContainsString('occasional hunting in terms of section 15', $text);
        $this->assertStringNotContainsString('dedicated hunting in terms of section 16', $text);
        $this->assertStringContainsString('backup handgun to be used alongside my primary hunting rifle', $text);
        $this->assertStringNotContainsString('primary hunting firearm for this profile', $text);
    }

    public function test_hunting_section_16_dedicated_primary(): void
    {
        $type = ApplicationType::where('slug', 'hunting')->firstOrFail();

        $preview = $this->render($type, [
            'firearms' => $this->sampleFirearm(),
            'section' => '16 — Dedicated hunter',
            'hunting_role' => 'Primary — long range / stalking',
            'hunting_frequency' => 'monthly',
            'hunting_locations' => 'KZN midlands',
            'species' => 'bushbuck, nyala',
            'association_name' => 'NRAPA',
            'association_number' => 'NRAPA-99',
        ]);

        $text = $preview['text'];
        $this->assertSame([], $preview['missing']);
        $this->assertStringContainsString('dedicated hunting in terms of section 16', $text);
        $this->assertStringNotContainsString('occasional hunting in terms of section 15', $text);
        $this->assertStringContainsString('primary hunting firearm for this profile', $text);
        $this->assertStringNotContainsString('backup handgun to be used alongside', $text);
        $this->assertStringContainsString('active member of NRAPA', $text);
    }

    public function test_every_seeded_motivation_type_renders_with_no_missing_placeholders(): void
    {
        // Belt-and-braces: for every active motivation type seeded, make
        // sure a minimally-filled draft renders without any `[MISSING: …]`
        // tokens. Guards against drift in future seeder edits.
        $types = ApplicationType::where('is_active', true)->where('has_motivation', true)->get();
        $this->assertNotEmpty($types, 'Seeder must produce at least one motivation type.');

        foreach ($types as $type) {
            $answers = [
                'firearms' => $this->sampleFirearm(),
                // Super-set of all conditional fields we use — unknown
                // ones are harmless.
                'province' => 'Gauteng',
                'policing_precinct' => 'Lyttelton',
                'section' => '16 — Dedicated sports person',
                'discipline' => 'IPSC Production',
                'association_name' => 'SAPSA',
                'association_number' => 'SAPSA-9999',
                'hunting_role' => 'Primary — long range / stalking',
                'hunting_frequency' => 'monthly',
                'hunting_locations' => 'Limpopo',
                'species' => 'kudu',
                'employment_profile' => ['travel_other_cities' => 'yes'],
                'other_profile' => ['travel_family' => 'yes'],
                'home_security' => ['armed_response' => 'yes'],
            ];
            $preview = $this->render($type, $answers);
            $this->assertSame(
                [], $preview['missing'],
                "Type {$type->slug} rendered with missing placeholders: ".implode(', ', $preview['missing']),
            );
            $this->assertNotEmpty($preview['text'], "Type {$type->slug} rendered empty text.");
        }
    }
}
