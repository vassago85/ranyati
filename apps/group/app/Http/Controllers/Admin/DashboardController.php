<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MotivationPhase;
use App\Enums\MotivationStatus;
use App\Http\Controllers\Controller;
use App\Models\MotivationEnquiry;
use App\Models\MotivationRequest;

/**
 * Admin overview at `/admin` — Phase 2 (mockup 01).
 *
 * Pulls four KPI counts, a phase pipeline breakdown, and the oldest
 * actionable rows into the "Needs your action" table. Every count is
 * clickable and lands the admin on the matching filtered list, so the
 * numbers on this page always agree with what the list shows.
 */
class DashboardController extends Controller
{
    public function __invoke()
    {
        $amberDays = (int) config('motivations.waiting_thresholds.amber_days', 3);
        $redDays = (int) config('motivations.waiting_thresholds.red_days', 5);

        // ----- 4 KPI tiles ----------------------------------------------
        $needsActionTotal = MotivationRequest::query()
            ->whereIn('status', MotivationStatus::needsActionValues())
            ->count();

        // "In progress" = anything that isn't a terminal state (draft on
        // one end, completed on the other). Matches the member-visible
        // notion of "actively being worked on".
        $inProgressTotal = MotivationRequest::query()
            ->whereNotIn('status', [
                MotivationStatus::DRAFT->value,
                MotivationStatus::COMPLETED->value,
            ])
            ->count();

        $awaitingPaymentTotal = MotivationRequest::query()
            ->where('status', MotivationStatus::INVOICED->value)
            ->count();

        $unreadEnquiriesTotal = MotivationEnquiry::query()
            ->whereNull('read_at')
            ->count();

        // ----- Pipeline card --------------------------------------------
        $statusCounts = MotivationRequest::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->all();

        // Completed is special: the brief wants "last 30 days" instead of
        // the all-time count so an admin can see "we closed 12 jobs this
        // month" instead of "we've closed 2,814 ever".
        $completedLast30 = MotivationRequest::query()
            ->where('status', MotivationStatus::COMPLETED->value)
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();

        $pipeline = [];
        foreach (MotivationPhase::ordered() as $phase) {
            $phaseItems = [];
            $phaseTotal = 0;
            foreach ($phase->statuses() as $status) {
                $count = $status === MotivationStatus::COMPLETED
                    ? $completedLast30
                    : (int) ($statusCounts[$status->value] ?? 0);
                $phaseItems[] = [
                    'status' => $status,
                    'count' => $count,
                    'note' => $status === MotivationStatus::COMPLETED ? 'last 30 days' : null,
                ];
                $phaseTotal += $count;
            }
            $pipeline[] = [
                'phase' => $phase,
                'items' => $phaseItems,
                'total' => $phaseTotal,
            ];
        }

        // ----- Needs-your-action table ----------------------------------
        //
        // Sort by `updated_at` oldest first, because every status
        // transition touches updated_at — so "time in current stage" is a
        // monotonic function of this timestamp without needing to join
        // an audit table (Phase 3 adds the real history).
        $needsAction = MotivationRequest::query()
            ->with(['user.clientProfile', 'applicationType'])
            ->whereIn('status', MotivationStatus::needsActionValues())
            ->orderBy('updated_at')
            ->limit(10)
            ->get()
            ->map(function (MotivationRequest $m) use ($amberDays, $redDays) {
                $status = MotivationStatus::from($m->status);
                $days = (int) $m->updated_at?->diffInDays(now()) ?? 0;

                return [
                    'motivation' => $m,
                    'status' => $status,
                    'days_in_stage' => $days,
                    'severity' => $days >= $redDays ? 'red' : ($days >= $amberDays ? 'amber' : 'grey'),
                ];
            });

        return view('admin.dashboard', [
            'kpis' => [
                'needs_action' => $needsActionTotal,
                'in_progress' => $inProgressTotal,
                'awaiting_payment' => $awaitingPaymentTotal,
                'unread_enquiries' => $unreadEnquiriesTotal,
            ],
            'pipeline' => $pipeline,
            'needsAction' => $needsAction,
            'thresholds' => [
                'amber' => $amberDays,
                'red' => $redDays,
            ],
        ]);
    }
}
