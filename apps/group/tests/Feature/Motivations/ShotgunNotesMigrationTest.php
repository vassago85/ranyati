<?php

namespace Tests\Feature\Motivations;

use App\Models\ApplicationType;
use App\Support\Motivations\QuestionSchema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShotgunNotesMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): object
    {
        return require database_path('migrations/2026_10_13_000001_show_shotgun_notes_only_for_shotguns.php');
    }

    private function type(?array $condition = null): ApplicationType
    {
        $field = ['name' => 'shotgun_action_notes', 'label' => 'Shotgun action and choke', 'type' => 'text'];
        if ($condition) {
            $field['condition'] = $condition;
        }

        return ApplicationType::create([
            'slug' => 'shotgun-'.uniqid(),
            'name' => 'Shotgun test',
            'category' => 'sport',
            'is_active' => true,
            'has_motivation' => true,
            'questions' => [[
                'key' => 'firearm_details', 'title' => 'Firearm details', 'fields' => [
                    ['name' => 'firearms', 'label' => 'Firearms', 'type' => 'firearms_table'],
                    $field,
                ],
            ]],
            'document_requirements' => [],
        ]);
    }

    public function test_shotgun_notes_only_show_when_a_row_is_a_shotgun(): void
    {
        $type = $this->type();

        $this->migration()->up();

        $field = QuestionSchema::fields($type->fresh()->sections())[1];
        $rifle = QuestionSchema::context([], ['firearms' => [['type' => 'Rifle']]], $type->fresh());
        $shotgun = QuestionSchema::context([], ['firearms' => [['type' => 'Rifle'], ['type' => 'Shotgun']]], $type->fresh());

        $this->assertFalse(QuestionSchema::isVisible($field, $rifle));
        $this->assertTrue(QuestionSchema::isVisible($field, $shotgun));
    }

    public function test_an_admin_set_condition_is_left_alone(): void
    {
        $custom = ['field' => 'answers.section', 'operator' => 'filled'];
        $type = $this->type($custom);

        $this->migration()->up();

        $this->assertSame($custom, $type->fresh()->sections()[0]['fields'][1]['condition']);
    }

    public function test_down_removes_only_the_condition_it_added(): void
    {
        $type = $this->type();
        $migration = $this->migration();

        $migration->up();
        $migration->down();

        $this->assertArrayNotHasKey('condition', $type->fresh()->sections()[0]['fields'][1]);
    }
}
