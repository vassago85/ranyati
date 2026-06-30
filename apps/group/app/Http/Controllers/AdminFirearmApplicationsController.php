<?php

namespace App\Http\Controllers;

use App\Models\FirearmApplication;
use App\Models\FirearmApplicationCheck;
use App\Models\User;
use App\Services\BatchAnalytics;
use App\Services\SapsCircuitBreaker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminFirearmApplicationsController extends Controller
{
    public function index(BatchAnalytics $analytics, SapsCircuitBreaker $circuit): View
    {
        $batches = $analytics->batchesOverview();
        $totals = [
            'applications' => FirearmApplication::count(),
            'monitored' => FirearmApplication::where('monitoring_enabled', true)->count(),
            'clients' => User::where('role', User::ROLE_CLIENT)->count(),
            'verified_clients' => User::where('role', User::ROLE_CLIENT)
                ->whereNotNull('email_verified_at')
                ->count(),
        ];

        $circuitStatus = $circuit->status();
        $recentFailureCount = FirearmApplicationCheck::query()
            ->where('success', false)
            ->where('checked_at', '>=', now()->subHours(24))
            ->count();
        $recentSuccessCount = FirearmApplicationCheck::query()
            ->where('success', true)
            ->where('checked_at', '>=', now()->subHours(24))
            ->count();

        $clients = User::query()
            ->where('role', User::ROLE_CLIENT)
            ->withCount(['firearmApplications', 'firearmApplications as monitored_applications_count' => function ($q) {
                $q->where('monitoring_enabled', true);
            }])
            ->orderByDesc('firearm_applications_count')
            ->orderBy('name')
            ->get();

        $recentChanges = $analytics->recentTransitions(null, 15, anonymous: false);
        $overallAverages = $analytics->averageDaysPerStatus();

        return view('admin.applications.index', compact(
            'batches',
            'totals',
            'clients',
            'recentChanges',
            'overallAverages',
            'circuitStatus',
            'recentFailureCount',
            'recentSuccessCount',
        ));
    }

    public function log(Request $request, SapsCircuitBreaker $circuit): View
    {
        $filter = $request->string('filter')->toString() ?: 'all';

        $query = FirearmApplicationCheck::query()
            ->with(['application:id,reference_number,user_id,label', 'application.user:id,name,email'])
            ->latest('checked_at');

        if ($filter === 'failures') {
            $query->where('success', false);
        } elseif ($filter === 'successes') {
            $query->where('success', true);
        } elseif ($filter === 'changes') {
            $query->where('status_changed', true);
        }

        $checks = $query->paginate(50)->withQueryString();

        $circuitStatus = $circuit->status();

        $summary = [
            'last_24h_total' => FirearmApplicationCheck::query()
                ->where('checked_at', '>=', now()->subHours(24))
                ->count(),
            'last_24h_failures' => FirearmApplicationCheck::query()
                ->where('success', false)
                ->where('checked_at', '>=', now()->subHours(24))
                ->count(),
            'last_24h_changes' => FirearmApplicationCheck::query()
                ->where('status_changed', true)
                ->where('checked_at', '>=', now()->subHours(24))
                ->count(),
        ];

        return view('admin.applications.log', compact('checks', 'filter', 'circuitStatus', 'summary'));
    }

    public function resetCircuit(SapsCircuitBreaker $circuit): RedirectResponse
    {
        $circuit->reset();

        return redirect()
            ->route('admin.applications.log')
            ->with('success', 'SAPS circuit breaker reset. The next scheduled run will hit SAPS again.');
    }

    public function showBatch(string $batchKey, BatchAnalytics $analytics): View
    {
        abort_unless(ctype_digit($batchKey) && strlen($batchKey) === FirearmApplication::BATCH_KEY_LENGTH, 404);

        $applications = FirearmApplication::query()
            ->inBatch($batchKey)
            ->with('user:id,name,email,email_verified_at')
            ->orderBy('reference_number')
            ->get();

        abort_if($applications->isEmpty(), 404);

        $distribution = $analytics->statusDistribution($batchKey);
        $averages = $analytics->averageDaysPerStatus($batchKey);
        $recent = $analytics->recentTransitions($batchKey, 20, anonymous: false);

        return view('admin.applications.batch', [
            'batchKey' => $batchKey,
            'range' => $analytics->formatRange($batchKey),
            'applications' => $applications,
            'distribution' => $distribution,
            'averages' => $averages,
            'recent' => $recent,
        ]);
    }

    public function showUser(Request $request, User $user): View
    {
        abort_unless($user->isClient(), 404);

        $user->load(['firearmApplications' => function ($q) {
            $q->latest();
        }, 'firearmApplications.transitions' => function ($q) {
            $q->orderByDesc('entered_at');
        }]);

        return view('admin.applications.user', [
            'client' => $user,
        ]);
    }
}
