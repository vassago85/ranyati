<?php

namespace App\Enums;

use App\Models\MotivationRequest;

/**
 * Single source of truth for the twelve granular motivation statuses:
 * their order, labels, phase, badge colour, stepper position and whether
 * the next action is owned by an admin or by the member.
 *
 * The {@see MotivationRequest} model still exposes `STATUS_*` string
 * constants and the {@see MotivationRequest::STATUSES} map so existing
 * controllers and tests keep compiling. Both representations deliberately
 * share the same stored string values, so `MotivationStatus::from($row->status)`
 * is always safe for a motivation that was persisted via either path.
 */
enum MotivationStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case CHANGES_REQUESTED = 'changes_requested';
    case VERIFIED = 'verified';
    case AWAITING_ENDORSEMENT = 'awaiting_endorsement';
    case READY_TO_PRINT = 'ready_to_print';
    case PRINTED = 'printed';
    case INVOICED = 'invoiced';
    case PAID = 'paid';
    case READY_FOR_COLLECTION = 'ready_for_collection';
    case SHIPPED = 'shipped';
    case COMPLETED = 'completed';

    /**
     * Admin-facing label. Mirrors `MotivationRequest::STATUSES`.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::CHANGES_REQUESTED => 'Changes requested',
            self::VERIFIED => 'Details confirmed',
            self::AWAITING_ENDORSEMENT => 'Awaiting endorsement',
            self::READY_TO_PRINT => 'Ready to print',
            self::PRINTED => 'Motivation printed',
            self::INVOICED => 'Awaiting payment',
            self::PAID => 'Payment received',
            self::READY_FOR_COLLECTION => 'Ready for collection',
            self::SHIPPED => 'Shipped',
            self::COMPLETED => 'Completed',
        };
    }

    /**
     * Which of the four high-level phases this status belongs to.
     */
    public function phase(): MotivationPhase
    {
        return match ($this) {
            self::DRAFT,
            self::SUBMITTED,
            self::CHANGES_REQUESTED => MotivationPhase::INTAKE,

            self::VERIFIED,
            self::AWAITING_ENDORSEMENT,
            self::READY_TO_PRINT,
            self::PRINTED => MotivationPhase::PREPARATION,

            self::INVOICED,
            self::PAID => MotivationPhase::PAYMENT,

            self::READY_FOR_COLLECTION,
            self::SHIPPED,
            self::COMPLETED => MotivationPhase::FULFILMENT,
        };
    }

    /**
     * Colour family for the status badge in lists and the detail header.
     * Values map to the admin stylesheet's `badge-{colour}` classes.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            // Admin must act next — amber.
            self::SUBMITTED,
            self::VERIFIED,
            self::AWAITING_ENDORSEMENT,
            self::PAID => 'orange',

            // Member must act next or the request is paused — zinc.
            self::DRAFT,
            self::CHANGES_REQUESTED => 'zinc',

            // In progress but no immediate action required — blue.
            self::READY_TO_PRINT,
            self::PRINTED,
            self::INVOICED,
            self::READY_FOR_COLLECTION,
            self::SHIPPED => 'blue',

            // Terminal success state — green.
            self::COMPLETED => 'green',
        };
    }

    /**
     * Position in the 12-step stepper (1-indexed, matching the order the
     * admin sees in the detail page progress indicator).
     */
    public function stepIndex(): int
    {
        return match ($this) {
            self::DRAFT => 1,
            self::SUBMITTED => 2,
            self::CHANGES_REQUESTED => 3,
            self::VERIFIED => 4,
            self::AWAITING_ENDORSEMENT => 5,
            self::READY_TO_PRINT => 6,
            self::PRINTED => 7,
            self::INVOICED => 8,
            self::PAID => 9,
            self::READY_FOR_COLLECTION => 10,
            self::SHIPPED => 11,
            self::COMPLETED => 12,
        };
    }

    /**
     * True when the next move belongs to an admin: these are the four
     * statuses that populate the dashboard's "Needs your action" table
     * and that get an orange dot on the pipeline card.
     *
     * Submitted → confirm the details
     * Verified → generate the motivation(s)
     * Awaiting endorsement → upload the endorsement
     * Paid → issue to the member (collect or ship)
     */
    public function needsAdminAction(): bool
    {
        return match ($this) {
            self::SUBMITTED,
            self::VERIFIED,
            self::AWAITING_ENDORSEMENT,
            self::PAID => true,

            default => false,
        };
    }

    /**
     * Short imperative that names the next step for the admin. Used in
     * the "Needs your action" dashboard table and the list's Next-step
     * button when `needsAdminAction()` is true.
     *
     * Returns null when there is nothing for an admin to do next.
     */
    public function nextStepLabel(): ?string
    {
        return match ($this) {
            self::SUBMITTED => 'Confirm details',
            self::VERIFIED => 'Generate motivation',
            self::AWAITING_ENDORSEMENT => 'Upload endorsement',
            self::PAID => 'Issue to member',
            default => null,
        };
    }

    /**
     * Every status in display order (same order as the stepper and the
     * `MotivationRequest::STATUSES` map). Preferred over `cases()` when
     * UI ordering matters.
     *
     * @return list<self>
     */
    public static function ordered(): array
    {
        return [
            self::DRAFT,
            self::SUBMITTED,
            self::CHANGES_REQUESTED,
            self::VERIFIED,
            self::AWAITING_ENDORSEMENT,
            self::READY_TO_PRINT,
            self::PRINTED,
            self::INVOICED,
            self::PAID,
            self::READY_FOR_COLLECTION,
            self::SHIPPED,
            self::COMPLETED,
        ];
    }

    /**
     * Backwards-compatible array of key => label. Lets code that was
     * written against `MotivationRequest::STATUSES` switch to the enum
     * incrementally.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $out = [];
        foreach (self::ordered() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }

    /**
     * The statuses whose next step is an admin's. Convenient for queries
     * like `MotivationRequest::whereIn('status', MotivationStatus::needsActionValues())`.
     *
     * @return list<string>
     */
    public static function needsActionValues(): array
    {
        return array_values(array_map(
            fn (self $c) => $c->value,
            array_filter(self::ordered(), fn (self $c) => $c->needsAdminAction()),
        ));
    }
}
