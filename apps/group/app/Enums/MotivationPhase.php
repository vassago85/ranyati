<?php

namespace App\Enums;

/**
 * The four high-level phases a motivation request moves through on its
 * way from member submission to admin close-out. Each phase groups a
 * handful of the twelve granular statuses in {@see MotivationStatus} so
 * the dashboard and the list can offer a shorter navigation.
 *
 * Introduced for the Phase 2 UX overhaul. The granular status column on
 * `motivation_requests` is the authoritative stored value; phases are
 * derived from it and never persisted on their own.
 */
enum MotivationPhase: string
{
    case INTAKE = 'intake';
    case PREPARATION = 'preparation';
    case PAYMENT = 'payment';
    case FULFILMENT = 'fulfilment';

    /**
     * Human-facing label used in UI (phase tabs, dashboard columns).
     */
    public function label(): string
    {
        return match ($this) {
            self::INTAKE => 'Intake',
            self::PREPARATION => 'Preparation',
            self::PAYMENT => 'Payment',
            self::FULFILMENT => 'Fulfilment',
        };
    }

    /**
     * The statuses that make up this phase, in display order. The order
     * matches the stepper left-to-right so the dashboard pipeline columns
     * line up visually with the detail page's progress indicator.
     *
     * @return list<MotivationStatus>
     */
    public function statuses(): array
    {
        return match ($this) {
            self::INTAKE => [
                MotivationStatus::DRAFT,
                MotivationStatus::SUBMITTED,
                MotivationStatus::CHANGES_REQUESTED,
            ],
            self::PREPARATION => [
                MotivationStatus::VERIFIED,
                MotivationStatus::AWAITING_ENDORSEMENT,
                MotivationStatus::READY_TO_PRINT,
                MotivationStatus::PRINTED,
            ],
            self::PAYMENT => [
                MotivationStatus::INVOICED,
                MotivationStatus::PAID,
            ],
            self::FULFILMENT => [
                MotivationStatus::READY_FOR_COLLECTION,
                MotivationStatus::SHIPPED,
                MotivationStatus::COMPLETED,
            ],
        };
    }

    /**
     * Every phase in display order. Preferred over `cases()` whenever UI
     * ordering matters (which is almost always).
     *
     * @return list<self>
     */
    public static function ordered(): array
    {
        return [self::INTAKE, self::PREPARATION, self::PAYMENT, self::FULFILMENT];
    }
}
