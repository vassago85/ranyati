<?php

namespace Tests\Feature\Motivations;

use App\Support\Motivations\QuestionSchema;
use Tests\TestCase;

class QuestionSchemaTest extends TestCase
{
    private function sections(bool $required = true): array
    {
        return [[
            'key' => 'firearm_details',
            'title' => 'Firearm details',
            'fields' => [
                ['name' => 'firearms', 'label' => 'Firearms', 'type' => 'firearms_table', 'required' => $required],
            ],
        ]];
    }

    public function test_every_firearm_row_needs_a_serial(): void
    {
        $answers = ['firearms' => [
            ['type' => 'Rifle', 'make' => 'Tikka', 'serial' => 'ABC123'],
            ['type' => 'Shotgun', 'make' => 'Beretta', 'serial' => ''],
        ]];

        $this->assertSame(
            ['Firearms: serial number for firearm 2'],
            QuestionSchema::missingRequired($this->sections(), $answers, ['answers' => $answers]),
        );
    }

    public function test_serial_is_checked_even_when_the_table_is_optional(): void
    {
        $answers = ['firearms' => [['type' => 'Handgun', 'make' => 'Glock']]];

        $this->assertSame(
            ['Firearms: serial number for firearm 1'],
            QuestionSchema::missingRequired($this->sections(false), $answers, ['answers' => $answers]),
        );
    }

    public function test_complete_rows_report_nothing_missing(): void
    {
        $answers = ['firearms' => [['type' => 'Rifle', 'serial' => 'XYZ']]];

        $this->assertSame([], QuestionSchema::missingRequired($this->sections(), $answers, ['answers' => $answers]));
    }
}
