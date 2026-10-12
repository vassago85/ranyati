<?php

namespace Tests\Feature\Motivations;

use App\Support\Motivations\ConditionEvaluator;
use Tests\TestCase;

class ConditionEvaluatorTest extends TestCase
{
    public function test_null_condition_always_passes(): void
    {
        $this->assertTrue(ConditionEvaluator::passes(null, []));
    }

    public function test_equals_handles_booleans_and_case(): void
    {
        $ctx = ['profile' => ['self_employed' => true, 'name' => 'Jane']];
        $this->assertTrue(ConditionEvaluator::passes(
            ['field' => 'profile.self_employed', 'operator' => 'equals', 'value' => 'Yes'],
            $ctx,
        ));
        $this->assertTrue(ConditionEvaluator::passes(
            ['field' => 'profile.name', 'operator' => 'equals', 'value' => 'jane'],
            $ctx,
        ));
    }

    public function test_in_matches_any_value_from_a_csv(): void
    {
        $ctx = ['answers' => ['section' => 'Section 15 — Occasional']];
        $this->assertTrue(ConditionEvaluator::passes(
            ['field' => 'answers.section', 'operator' => 'in', 'value' => 'Section 15 — Occasional,Section 16 — Dedicated'],
            $ctx,
        ));
        $this->assertFalse(ConditionEvaluator::passes(
            ['field' => 'answers.section', 'operator' => 'in', 'value' => 'Section 13'],
            $ctx,
        ));
    }

    public function test_filled_and_empty_treat_blank_string_as_empty(): void
    {
        $this->assertTrue(ConditionEvaluator::passes(
            ['field' => 'profile.name', 'operator' => 'filled'],
            ['profile' => ['name' => ' Jane ']],
        ));
        $this->assertTrue(ConditionEvaluator::passes(
            ['field' => 'profile.name', 'operator' => 'empty'],
            ['profile' => ['name' => '']],
        ));
    }

    public function test_wildcard_paths_scan_lists(): void
    {
        $ctx = ['firearms' => [['type' => 'Handgun'], ['type' => 'Rifle']]];
        $this->assertTrue(ConditionEvaluator::passes(
            ['field' => 'firearms.*.type', 'operator' => 'equals', 'value' => 'Rifle'],
            $ctx,
        ));
        $this->assertFalse(ConditionEvaluator::passes(
            ['field' => 'firearms.*.type', 'operator' => 'equals', 'value' => 'Shotgun'],
            $ctx,
        ));
    }

    public function test_all_and_any_groups(): void
    {
        $ctx = ['answers' => ['section' => 'Section 13', 'has_farm' => 'Yes']];
        $this->assertTrue(ConditionEvaluator::passes([
            'all' => [
                ['field' => 'answers.section', 'operator' => 'equals', 'value' => 'Section 13'],
                ['field' => 'answers.has_farm', 'operator' => 'equals', 'value' => 'Yes'],
            ],
        ], $ctx));

        $this->assertFalse(ConditionEvaluator::passes([
            'all' => [
                ['field' => 'answers.section', 'operator' => 'equals', 'value' => 'Section 13'],
                ['field' => 'answers.has_farm', 'operator' => 'equals', 'value' => 'No'],
            ],
        ], $ctx));

        $this->assertTrue(ConditionEvaluator::passes([
            'any' => [
                ['field' => 'answers.section', 'operator' => 'equals', 'value' => 'Section 16'],
                ['field' => 'answers.has_farm', 'operator' => 'equals', 'value' => 'Yes'],
            ],
        ], $ctx));
    }

    public function test_gt_counts_arrays(): void
    {
        $ctx = ['firearms' => [['t' => 1], ['t' => 2], ['t' => 3], ['t' => 4], ['t' => 5]]];
        $this->assertTrue(ConditionEvaluator::passes(
            ['field' => 'firearms', 'operator' => 'gt', 'value' => '4'],
            $ctx,
        ));
    }

    public function test_normalize_rejects_bad_fields_and_operators(): void
    {
        $this->assertNull(ConditionEvaluator::normalize(['field' => 'drop; table users', 'operator' => 'equals', 'value' => 'x']));
        $this->assertSame('equals', ConditionEvaluator::normalize(['field' => 'answers.x', 'operator' => 'wat', 'value' => 'x'])['operator']);
    }
}
