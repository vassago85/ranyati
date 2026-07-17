@extends('account.layout')

@section('title', 'My Applications')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-bold text-white">My applications</h1>
        <p class="text-white/50 text-sm mt-1">Checked automatically once per day. You will receive an email when SAPS status changes.</p>
    </div>
    <a href="{{ route('account.applications.create') }}" class="btn-primary inline-flex items-center justify-center rounded-xl px-5 py-3 font-semibold no-underline">
        Add application
    </a>
</div>

@if ($applications->isEmpty())
    <div class="card p-8 text-center">
        <p class="text-white/60 mb-4">You have not added any applications yet.</p>
        <a href="{{ route('account.applications.create') }}" class="btn-primary inline-flex rounded-xl px-5 py-3 font-semibold no-underline">Add your first application</a>
    </div>
@else
    <div class="space-y-4">
        @foreach ($applications as $application)
            <div class="card p-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-semibold text-white">{{ $application->displayName() }}</h2>
                            @if ($application->status)
                                <span class="status-pill text-xs font-semibold px-2.5 py-1 rounded-full">{{ $application->status }}</span>
                            @endif
                            @if (\App\Models\FirearmApplication::isTerminalStatus($application->status))
                                <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-300">Finalised — checks stopped</span>
                            @elseif (! $application->monitoring_enabled)
                                <span class="text-xs px-2.5 py-1 rounded-full bg-white/10 text-white/50">Monitoring paused</span>
                            @endif
                        </div>
                        <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-1 text-sm text-white/60">
                            <div><dt class="inline font-medium text-white/80">Reference:</dt> {{ $application->reference_number }}</div>
                            @if ($application->serial_number)
                                <div><dt class="inline font-medium text-white/80">Serial:</dt> {{ $application->serial_number }}</div>
                            @endif
                            @if ($application->application_type)
                                <div><dt class="inline font-medium text-white/80">Type:</dt> {{ $application->application_type }}</div>
                            @endif
                            @if ($application->make)
                                <div><dt class="inline font-medium text-white/80">Make:</dt> {{ $application->make }}</div>
                            @endif
                            @if ($application->calibre)
                                <div><dt class="inline font-medium text-white/80">Calibre:</dt> {{ $application->calibre }}</div>
                            @endif
                            @if ($application->status_date)
                                <div><dt class="inline font-medium text-white/80">Status date:</dt> {{ $application->status_date }}</div>
                            @endif
                        </dl>
                        @if ($application->status_description)
                            <p class="text-sm text-white/70">{{ $application->status_description }}</p>
                        @endif
                        @if ($application->next_step && $application->next_step !== $application->status_description)
                            <p class="text-sm text-white/50"><span class="font-medium text-white/70">Next step:</span> {{ $application->next_step }}</p>
                        @endif
                        <p class="text-xs text-white/35">
                            @if ($application->last_checked_at)
                                Last checked {{ $application->last_checked_at->diffForHumans() }}
                            @else
                                Waiting for first SAPS check...
                            @endif
                            @if ($application->saps_data_updated_on)
                                · SAPS data updated {{ $application->saps_data_updated_on->format('Y-m-d') }}
                            @endif
                        </p>
                        @if ($application->last_check_error && ! $application->status)
                            <p class="text-sm text-red-300">{{ $application->last_check_error }}</p>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('account.applications.refresh', $application) }}">
                            @csrf
                            <button type="submit" class="btn-secondary rounded-lg px-3 py-2 text-sm">Refresh now</button>
                        </form>
                        <form method="POST" action="{{ route('account.applications.toggle-monitoring', $application) }}">
                            @csrf
                            <button type="submit" class="btn-secondary rounded-lg px-3 py-2 text-sm">
                                {{ $application->monitoring_enabled ? 'Pause monitoring' : 'Resume monitoring' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('account.applications.destroy', $application) }}" onsubmit="return confirm('Remove this application from tracking?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg px-3 py-2 text-sm text-red-300 border border-red-400/30">Remove</button>
                        </form>
                    </div>
                </div>

                @php($insight = $peerInsights[$application->id] ?? null)
                @if ($insight && $insight['batch_key'])
                    <div class="mt-5 pt-5 border-t border-white/5">
                        <div class="flex items-baseline justify-between flex-wrap gap-2 mb-3">
                            <div>
                                <span class="text-[10px] uppercase tracking-widest text-white/30 font-semibold">Batch</span>
                                <span class="text-sm text-white/80 font-semibold ml-2">{{ $insight['batch_key'] }}</span>
                                <span class="text-xs text-white/40 ml-2 font-mono">{{ $insight['range'] }}</span>
                            </div>
                            <div class="text-xs text-white/40">
                                @if ($insight['peer_count'] === 0)
                                    No other applications tracked in this batch yet
                                @else
                                    {{ $insight['peer_count'] }} other application{{ $insight['peer_count'] === 1 ? '' : 's' }} in this batch
                                @endif
                            </div>
                        </div>

                        @if ($insight['peer_count'] > 0)
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <div class="text-[10px] uppercase tracking-widest text-white/30 font-semibold mb-2">How peers compare</div>
                                    @if ($insight['status_distribution']->isEmpty())
                                        <p class="text-xs text-white/40">No statuses recorded for peers yet.</p>
                                    @else
                                        <ul class="space-y-1.5">
                                            @foreach ($insight['status_distribution'] as $row)
                                                <li class="flex justify-between text-xs">
                                                    <span class="text-white/70">{{ $row['status'] }}</span>
                                                    <span class="text-white/40">{{ $row['count'] }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-[10px] uppercase tracking-widest text-white/30 font-semibold mb-2">Average time at your status</div>
                                    @if ($insight['average_days_at_current_status'])
                                        <div class="text-2xl font-bold text-white">
                                            {{ $insight['average_days_at_current_status']['average_days'] }} <span class="text-sm font-normal text-white/40">days</span>
                                        </div>
                                        <div class="text-xs text-white/40 mt-1">
                                            Based on {{ $insight['average_days_at_current_status']['sample_size'] }} peer application{{ $insight['average_days_at_current_status']['sample_size'] === 1 ? '' : 's' }} that have moved past this status.
                                        </div>
                                    @else
                                        <p class="text-xs text-white/40">
                                            @if ($application->status)
                                                No peer has moved past <span class="text-white/60">{{ $application->status }}</span> yet, so there's no average to compare with.
                                            @else
                                                Average times will appear once your application has a recorded status.
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
@endsection
