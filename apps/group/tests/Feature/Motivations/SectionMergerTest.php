<?php

namespace Tests\Feature\Motivations;

use App\Support\Motivations\Templates\BlockKinds;
use App\Support\Motivations\Templates\SectionMerger;
use App\Support\Motivations\Templates\TemplateSchema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SectionMergerTest extends TestCase
{
    #[Test]
    public function merges_adjacent_heading_and_text_pairs(): void
    {
        $blocks = TemplateSchema::normalizeBlocks([
            ['kind' => BlockKinds::HEADING, 'settings' => ['text' => 'Hello', 'level' => 2]],
            ['kind' => BlockKinds::TEXT, 'settings' => ['body' => 'First paragraph.']],
            ['kind' => BlockKinds::HEADING, 'settings' => ['text' => 'Later', 'level' => 3]],
            ['kind' => BlockKinds::TEXT, 'settings' => ['body' => 'Another paragraph.']],
        ]);

        $merged = SectionMerger::merge($blocks);

        $this->assertCount(2, $merged);
        $this->assertSame(BlockKinds::SECTION, $merged[0]['kind']);
        $this->assertSame('Hello', $merged[0]['settings']['heading']);
        $this->assertSame(2, $merged[0]['settings']['level']);
        $this->assertStringContainsString('First paragraph.', $merged[0]['settings']['html']);

        $this->assertSame(BlockKinds::SECTION, $merged[1]['kind']);
        $this->assertSame(3, $merged[1]['settings']['level']);
    }

    #[Test]
    public function section_keeps_the_heading_id(): void
    {
        $blocks = TemplateSchema::normalizeBlocks([
            ['id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'kind' => BlockKinds::HEADING, 'settings' => ['text' => 'Keep me', 'level' => 1]],
            ['id' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', 'kind' => BlockKinds::TEXT, 'settings' => ['body' => 'ok']],
        ]);

        $merged = SectionMerger::merge($blocks);

        $this->assertSame('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', $merged[0]['id']);
    }

    #[Test]
    public function pairs_with_different_conditions_are_not_merged(): void
    {
        $blocks = TemplateSchema::normalizeBlocks([
            [
                'kind' => BlockKinds::HEADING,
                'settings' => ['text' => 'Hi', 'level' => 2],
                'condition' => ['field' => 'profile.self_employed', 'operator' => 'equals', 'value' => 'yes'],
            ],
            [
                'kind' => BlockKinds::TEXT,
                'settings' => ['body' => 'body'],
                // Different condition — the renderer behaviour would change
                // if we fused them, so SectionMerger must leave them alone.
                'condition' => ['field' => 'profile.self_employed', 'operator' => 'not_equals', 'value' => 'yes'],
            ],
        ]);

        $merged = SectionMerger::merge($blocks);

        $this->assertCount(2, $merged);
        $this->assertSame(BlockKinds::HEADING, $merged[0]['kind']);
        $this->assertSame(BlockKinds::TEXT, $merged[1]['kind']);
    }

    #[Test]
    public function matching_conditions_are_merged(): void
    {
        $cond = ['field' => 'profile.self_employed', 'operator' => 'equals', 'value' => 'yes'];
        $blocks = TemplateSchema::normalizeBlocks([
            ['kind' => BlockKinds::HEADING, 'settings' => ['text' => 'Hi', 'level' => 2], 'condition' => $cond],
            ['kind' => BlockKinds::TEXT, 'settings' => ['body' => 'body'], 'condition' => $cond],
        ]);

        $merged = SectionMerger::merge($blocks);

        $this->assertCount(1, $merged);
        $this->assertSame(BlockKinds::SECTION, $merged[0]['kind']);
        $this->assertNotNull($merged[0]['condition']);
    }

    #[Test]
    public function unpaired_heading_or_text_blocks_are_left_alone(): void
    {
        $blocks = TemplateSchema::normalizeBlocks([
            ['kind' => BlockKinds::HEADING, 'settings' => ['text' => 'Standalone', 'level' => 1]],
            ['kind' => BlockKinds::IMAGE, 'settings' => []],
            ['kind' => BlockKinds::TEXT, 'settings' => ['body' => 'orphan paragraph']],
        ]);

        $merged = SectionMerger::merge($blocks);

        $this->assertCount(3, $merged);
        $this->assertSame(BlockKinds::HEADING, $merged[0]['kind']);
        $this->assertSame(BlockKinds::IMAGE, $merged[1]['kind']);
        $this->assertSame(BlockKinds::TEXT, $merged[2]['kind']);
    }

    #[Test]
    public function recurses_into_conditional_groups_and_two_column(): void
    {
        $blocks = TemplateSchema::normalizeBlocks([
            [
                'kind' => BlockKinds::CONDITIONAL_GROUP,
                'settings' => ['label' => 'g', 'children' => [
                    ['kind' => BlockKinds::HEADING, 'settings' => ['text' => 'Inner', 'level' => 2]],
                    ['kind' => BlockKinds::TEXT, 'settings' => ['body' => 'inner body']],
                ]],
            ],
            [
                'kind' => BlockKinds::TWO_COLUMN,
                'settings' => [
                    'left' => [
                        ['kind' => BlockKinds::HEADING, 'settings' => ['text' => 'L', 'level' => 3]],
                        ['kind' => BlockKinds::TEXT, 'settings' => ['body' => 'left body']],
                    ],
                    'right' => [],
                ],
            ],
        ]);

        $merged = SectionMerger::merge($blocks);

        $this->assertSame(BlockKinds::SECTION, $merged[0]['settings']['children'][0]['kind']);
        $this->assertSame(BlockKinds::SECTION, $merged[1]['settings']['left'][0]['kind']);
    }

    #[Test]
    public function plain_text_round_trips_through_rich_section_body(): void
    {
        // The merger's paragraphsToHtml must escape and reconstruct the
        // text so a section renders byte-identically to the heading + text
        // it replaced.
        $html = SectionMerger::paragraphsToHtml("First.\n\nSecond.");
        $this->assertStringContainsString('<p>First.</p>', $html);
        $this->assertStringContainsString('<p>Second.</p>', $html);

        $html2 = SectionMerger::paragraphsToHtml('Smith & Sons < Co');
        $this->assertStringContainsString('Smith &amp; Sons &lt; Co', $html2);
    }
}
