<?php

namespace Tests\Unit\Enums;

use App\Enums\MotivationPhase;
use App\Enums\MotivationStatus;
use App\Models\MotivationRequest;
use PHPUnit\Framework\TestCase;

/**
 * Enum-shape test for the Phase 2 `MotivationStatus` enum. These are
 * cheap pure-PHP checks: no DB, no HTTP, no views. They lock in the
 * contract the dashboard, list, sidebar and (later) workflow service
 * are built against.
 */
class MotivationStatusTest extends TestCase
{
    public function test_every_status_has_a_matching_motivation_request_constant(): void
    {
        // Both representations must share the exact same stored string
        // values, so a motivation row written through the legacy path
        // and read through the enum (or vice versa) is always safe.
        foreach (MotivationStatus::cases() as $case) {
            $const = 'STATUS_'.strtoupper($case->name);
            $this->assertTrue(
                defined(MotivationRequest::class.'::'.$const),
                "MotivationRequest::{$const} is missing — enum/constant drift.",
            );
            $this->assertSame(
                constant(MotivationRequest::class.'::'.$const),
                $case->value,
                "MotivationRequest::{$const} and MotivationStatus::{$case->name} disagree on the stored value.",
            );
        }
    }

    public function test_enum_labels_match_motivation_request_statuses_map(): void
    {
        // The legacy `STATUSES` array is still the authority for a few
        // controllers. Keeping it 1:1 with the enum means the switchover
        // can happen file-by-file without ever producing wrong labels.
        $this->assertSame(MotivationRequest::STATUSES, MotivationStatus::labels());
    }

    public function test_ordered_returns_every_case_in_display_order(): void
    {
        $this->assertCount(12, MotivationStatus::ordered());
        $this->assertSame(
            [
                'draft', 'submitted', 'changes_requested',
                'verified', 'awaiting_endorsement', 'ready_to_print', 'printed',
                'invoiced', 'paid',
                'ready_for_collection', 'shipped', 'completed',
            ],
            array_map(fn ($c) => $c->value, MotivationStatus::ordered()),
        );
    }

    public function test_step_index_runs_1_through_12_without_gaps(): void
    {
        $indexes = array_map(fn ($c) => $c->stepIndex(), MotivationStatus::ordered());
        $this->assertSame(range(1, 12), $indexes);
    }

    /**
     * @dataProvider needs_action_cases
     */
    public function test_needs_admin_action_flag(MotivationStatus $status, bool $expected): void
    {
        $this->assertSame($expected, $status->needsAdminAction());
    }

    public static function needs_action_cases(): array
    {
        return [
            'submitted (needs confirm)' => [MotivationStatus::SUBMITTED, true],
            'verified (needs generate)' => [MotivationStatus::VERIFIED, true],
            'awaiting endorsement (needs upload)' => [MotivationStatus::AWAITING_ENDORSEMENT, true],
            'paid (needs issue)' => [MotivationStatus::PAID, true],

            'draft (member works on it)' => [MotivationStatus::DRAFT, false],
            'changes requested (member works on it)' => [MotivationStatus::CHANGES_REQUESTED, false],
            'ready to print (not actionable yet)' => [MotivationStatus::READY_TO_PRINT, false],
            'printed (waiting for invoice flow)' => [MotivationStatus::PRINTED, false],
            'invoiced (member must pay)' => [MotivationStatus::INVOICED, false],
            'ready for collection (member must collect)' => [MotivationStatus::READY_FOR_COLLECTION, false],
            'shipped (member must receive)' => [MotivationStatus::SHIPPED, false],
            'completed (done)' => [MotivationStatus::COMPLETED, false],
        ];
    }

    public function test_needs_action_values_matches_the_cases_flagged_true(): void
    {
        $values = MotivationStatus::needsActionValues();
        $this->assertSame(
            ['submitted', 'verified', 'awaiting_endorsement', 'paid'],
            $values,
        );
    }

    public function test_next_step_label_is_present_for_every_needs_action_status_and_null_otherwise(): void
    {
        foreach (MotivationStatus::cases() as $case) {
            if ($case->needsAdminAction()) {
                $this->assertNotNull(
                    $case->nextStepLabel(),
                    $case->name.' needs an admin action but has no nextStepLabel().',
                );
            } else {
                $this->assertNull(
                    $case->nextStepLabel(),
                    $case->name.' is not actionable but still exposes a nextStepLabel().',
                );
            }
        }
    }

    public function test_phase_grouping_matches_the_brief(): void
    {
        // The brief locks these groupings in — they drive the pipeline
        // card and the 7-tab list.
        $this->assertSame(MotivationPhase::INTAKE, MotivationStatus::DRAFT->phase());
        $this->assertSame(MotivationPhase::INTAKE, MotivationStatus::SUBMITTED->phase());
        $this->assertSame(MotivationPhase::INTAKE, MotivationStatus::CHANGES_REQUESTED->phase());

        $this->assertSame(MotivationPhase::PREPARATION, MotivationStatus::VERIFIED->phase());
        $this->assertSame(MotivationPhase::PREPARATION, MotivationStatus::AWAITING_ENDORSEMENT->phase());
        $this->assertSame(MotivationPhase::PREPARATION, MotivationStatus::READY_TO_PRINT->phase());
        $this->assertSame(MotivationPhase::PREPARATION, MotivationStatus::PRINTED->phase());

        $this->assertSame(MotivationPhase::PAYMENT, MotivationStatus::INVOICED->phase());
        $this->assertSame(MotivationPhase::PAYMENT, MotivationStatus::PAID->phase());

        $this->assertSame(MotivationPhase::FULFILMENT, MotivationStatus::READY_FOR_COLLECTION->phase());
        $this->assertSame(MotivationPhase::FULFILMENT, MotivationStatus::SHIPPED->phase());
        $this->assertSame(MotivationPhase::FULFILMENT, MotivationStatus::COMPLETED->phase());
    }

    public function test_phase_statuses_round_trip_every_case_once(): void
    {
        // Every granular status must appear in exactly one phase's
        // statuses() list. Catches the easy copy/paste bug where a case
        // ends up in two phases or none.
        $counts = array_fill_keys(array_map(fn ($c) => $c->value, MotivationStatus::cases()), 0);

        foreach (MotivationPhase::cases() as $phase) {
            foreach ($phase->statuses() as $status) {
                $counts[$status->value]++;
            }
        }

        foreach ($counts as $value => $count) {
            $this->assertSame(1, $count, "status '{$value}' appears in {$count} phases, expected exactly 1.");
        }
    }

    public function test_badge_color_is_one_of_the_defined_classes(): void
    {
        $allowed = ['orange', 'green', 'blue', 'zinc'];
        foreach (MotivationStatus::cases() as $case) {
            $this->assertContains(
                $case->badgeColor(),
                $allowed,
                $case->name.' uses badge colour "'.$case->badgeColor().'" which is not in the admin stylesheet.',
            );
        }
    }
}
