<?php

namespace Tests\Feature\Storage;

use App\Models\RegisterBook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The intake forms render their firearm rows from an Alpine x-for, so the
 * whole repeater is invisible if the x-data expression fails to parse. That
 * happened once already: the firearm-type map was printed with {!! !!} inside
 * a double-quoted attribute, so its first JSON quote closed the attribute and
 * no firearm fields appeared at all. These tests pin the markup contract.
 */
class IntakeFormRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RegisterBook::updateOrCreate(['code' => 'D01'], [
            'type' => 'deceased_estate', 'pages' => 101, 'positions_per_page' => 26, 'status' => 'open',
        ]);
        RegisterBook::updateOrCreate(['code' => 'S01'], [
            'type' => 'self_storage', 'pages' => 101, 'positions_per_page' => 26, 'status' => 'open',
        ]);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Intake Admin',
            'email' => 'intake-admin@example.com',
            'password' => 'password123',
            'role' => User::ROLE_DEVELOPER,
        ]);
    }

    public static function intakeFormProvider(): array
    {
        return [
            'deceased estate' => ['/admin/storage/estates/create'],
            'self storage' => ['/admin/storage/self/create'],
        ];
    }

    #[DataProvider('intakeFormProvider')]
    public function test_firearm_type_map_is_attribute_safe(string $path): void
    {
        $html = $this->actingAs($this->admin())->get($path)->assertOk()->getContent();

        // The map must be HTML-escaped, because it sits inside x-data="...".
        $this->assertStringContainsString('&quot;handgun&quot;', $html);

        // A raw quote here would terminate the attribute early and silently
        // kill the entire repeater.
        $this->assertStringNotContainsString('types: {"', $html);
    }

    #[DataProvider('intakeFormProvider')]
    public function test_x_data_expression_is_balanced(string $path): void
    {
        $html = $this->actingAs($this->admin())->get($path)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/x-data="intakeItems\(/', $html);

        // Capture up to the next raw double quote — that is precisely where the
        // browser ends the attribute, so this sees what Alpine actually gets.
        preg_match('/x-data="(intakeItems\([^"]*)"/s', $html, $m);
        $this->assertNotEmpty($m, 'Could not isolate the intake x-data attribute.');

        $expression = html_entity_decode($m[1], ENT_QUOTES);

        // A truncated expression is exactly what the original bug produced.
        $this->assertSame(
            substr_count($expression, '{'),
            substr_count($expression, '}'),
            'x-data braces are unbalanced, so Alpine cannot parse it: '.$expression,
        );
        $this->assertStringEndsWith(')', trim($expression));
    }

    #[DataProvider('intakeFormProvider')]
    public function test_repeater_and_its_bound_fields_are_present(string $path): void
    {
        $response = $this->actingAs($this->admin())->get($path)->assertOk();

        // The fields themselves are produced client-side, so assert on the
        // template that generates them rather than on rendered inputs.
        $response->assertSee('x-for="(item, index) in items"', false);

        foreach (['page', 'position', 'shelf', 'tag_colour', 'tag_number', 'firearm_make', 'cartridge', 'serial_number', 'firearm_type', 'action_type', 'date_in'] as $field) {
            $response->assertSee("items[' + index + '][{$field}]", false);
        }
    }

    public function test_intake_pages_expose_every_firearm_type_and_its_actions(): void
    {
        $html = $this->actingAs($this->admin())
            ->get('/admin/storage/estates/create')
            ->assertOk()
            ->getContent();

        preg_match('/types: (\{.*?\})\s*\n?\s*\}\)"/s', $html, $m);
        $this->assertNotEmpty($m, 'Could not isolate the firearm type map.');

        $decoded = json_decode(html_entity_decode($m[1], ENT_QUOTES), true);

        $this->assertIsArray($decoded, 'Firearm type map is not decodable JSON.');
        $this->assertArrayHasKey('handgun', $decoded);
        $this->assertContains('Semi-auto', $decoded['handgun']);
    }
}
