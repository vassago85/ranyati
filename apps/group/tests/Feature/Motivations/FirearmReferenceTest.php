<?php

namespace Tests\Feature\Motivations;

use App\Support\Cartridges;
use App\Support\FirearmModels;
use App\Support\FirearmReference;
use App\Support\FirearmTypes;
use Tests\TestCase;

class FirearmReferenceTest extends TestCase
{
    public function test_every_model_points_at_a_listed_make(): void
    {
        $payload = FirearmReference::payload();

        $this->assertCount(count(FirearmModels::all()), $payload['models']);
        foreach ($payload['models'] as [$makeIndex, $model, $category]) {
            $this->assertArrayHasKey($makeIndex, $payload['makes']);
            $this->assertContains($category, [null, 'handgun', 'rifle', 'shotgun']);
        }
    }

    public function test_model_categories_line_up_with_questionnaire_types(): void
    {
        $typeKeys = array_map('strtolower', array_column(FirearmTypes::all(), 'label'));

        foreach (FirearmModels::all() as [, , $category]) {
            $this->assertTrue($category === null || in_array($category, $typeKeys, true), "Unknown category {$category}");
        }
    }

    public function test_calibre_aliases_belong_to_listed_calibres(): void
    {
        foreach (array_keys(Cartridges::ALIASES) as $name) {
            $this->assertArrayHasKey($name, Cartridges::CARTRIDGES);
        }

        $thirtyThirty = collect(FirearmReference::payload()['calibres'])->first(fn ($c) => in_array('.30-30 Winchester', $c[2], true));
        $this->assertSame('.30-30 WIN', $thirtyThirty[0] ?? null);
    }

    public function test_actions_are_keyed_by_the_type_labels_the_questionnaire_stores(): void
    {
        $this->assertSame(
            array_column(FirearmTypes::all(), 'label'),
            array_keys(FirearmReference::payload()['actions']),
        );
    }
}
