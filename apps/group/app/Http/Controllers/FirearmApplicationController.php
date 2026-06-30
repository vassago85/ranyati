<?php

namespace App\Http\Controllers;

use App\Jobs\CheckFirearmApplicationJob;
use App\Models\FirearmApplication;
use App\Services\BatchAnalytics;
use App\Services\FirearmApplicationChecker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FirearmApplicationController extends Controller
{
    public function index(Request $request, BatchAnalytics $analytics): View
    {
        $applications = $request->user()
            ->firearmApplications()
            ->latest()
            ->get();

        $peerInsights = $applications->mapWithKeys(fn ($app) => [
            $app->id => $analytics->peerInsight($app),
        ]);

        return view('account.applications.index', compact('applications', 'peerInsights'));
    }

    public function create(): View
    {
        return view('account.applications.form', [
            'application' => new FirearmApplication,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:100'],
            'reference_number' => ['required', 'string', 'max:40'],
            'serial_number' => ['nullable', 'string', 'max:40'],
            'competency_only' => ['sometimes', 'boolean'],
        ]);

        $competencyOnly = $request->boolean('competency_only');

        if (! $competencyOnly && blank($data['serial_number'] ?? null)) {
            return back()->withInput()->withErrors([
                'serial_number' => 'Serial number is required for licence or renewal enquiries.',
            ]);
        }

        $application = $request->user()->firearmApplications()->create([
            'label' => $data['label'] ?? null,
            'reference_number' => trim($data['reference_number']),
            'serial_number' => $competencyOnly ? null : trim($data['serial_number']),
            'competency_only' => $competencyOnly,
        ]);

        CheckFirearmApplicationJob::dispatch($application, notify: false);

        return redirect()->route('account.applications.index')
            ->with('success', 'Application added. We are fetching the current SAPS status now.');
    }

    public function destroy(Request $request, FirearmApplication $application): RedirectResponse
    {
        abort_unless($application->user_id === $request->user()->id, 403);

        $application->delete();

        return back()->with('success', 'Application removed from monitoring.');
    }

    public function toggleMonitoring(Request $request, FirearmApplication $application): RedirectResponse
    {
        abort_unless($application->user_id === $request->user()->id, 403);

        $application->update([
            'monitoring_enabled' => ! $application->monitoring_enabled,
        ]);

        return back()->with('success', $application->monitoring_enabled
            ? 'Daily monitoring enabled.'
            : 'Daily monitoring paused.');
    }

    public function refresh(Request $request, FirearmApplication $application, FirearmApplicationChecker $checker): RedirectResponse
    {
        abort_unless($application->user_id === $request->user()->id, 403);

        if ($application->last_checked_at?->gte(now()->subHour())) {
            return back()->withErrors([
                'refresh' => 'Please wait at least an hour between manual refreshes.',
            ]);
        }

        $result = $checker->check($application->fresh(['user']), notify: false);

        if (! $result['success']) {
            return back()->withErrors([
                'refresh' => $result['error'] ?? 'Could not refresh status from SAPS.',
            ]);
        }

        return back()->with('success', 'Status refreshed from SAPS.');
    }
}
