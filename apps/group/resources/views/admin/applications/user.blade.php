@extends('admin.layout')

@section('title', $client->name)

@section('actions')
    <a href="{{ route('admin.applications') }}" class="btn btn-secondary btn-sm">All clients</a>
@endsection

@section('content')
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-size: 22px; font-weight: 700; color: #fff;">{{ $client->name }}</div>
                <div style="font-size: 13px; color: rgba(255,255,255,0.5); margin-top: 4px;">{{ $client->email }}</div>
                <div style="margin-top: 8px;">
                    @if ($client->email_verified_at)
                        <span class="badge badge-green">Verified {{ $client->email_verified_at->diffForHumans() }}</span>
                    @else
                        <span class="badge badge-zinc">Unverified</span>
                    @endif
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: rgba(255,255,255,0.35);">Applications</div>
                <div style="font-size: 28px; font-weight: 800; color: #fff; margin-top: 4px;">{{ $client->firearmApplications->count() }}</div>
            </div>
        </div>
    </div>
</div>

@forelse ($client->firearmApplications as $app)
    <div class="card" style="margin-bottom: 16px;">
        <div class="card-header">
            <div>
                <h2 style="font-size: 14px;">{{ $app->displayName() }}</h2>
                <div style="font-size: 11px; color: rgba(255,255,255,0.35); margin-top: 2px; font-family: monospace;">
                    Ref {{ $app->reference_number }}
                    @if ($app->serial_number) · Serial {{ $app->serial_number }} @endif
                    @if ($app->batch_key)
                        · <a href="{{ route('admin.applications.batch', $app->batch_key) }}" style="color: #38bdf8;">Batch {{ $app->batch_key }}</a>
                    @endif
                </div>
            </div>
            <div>
                @if (\App\Models\FirearmApplication::isTerminalStatus($app->status))
                    <span class="badge badge-green">Finalised</span>
                @elseif ($app->monitoring_enabled)
                    <span class="badge badge-green">Monitored</span>
                @else
                    <span class="badge badge-zinc">Paused</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div>
                    <div style="font-size: 10px; text-transform: uppercase; color: rgba(255,255,255,0.3); font-weight: 700;">Current status</div>
                    <div style="margin-top: 4px;">
                        @if ($app->status)
                            <span class="badge badge-orange">{{ $app->status }}</span>
                        @else
                            <span style="color: rgba(255,255,255,0.4);">—</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div style="font-size: 10px; text-transform: uppercase; color: rgba(255,255,255,0.3); font-weight: 700;">Status date</div>
                    <div style="margin-top: 4px; color: #fff;">{{ $app->status_date ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size: 10px; text-transform: uppercase; color: rgba(255,255,255,0.3); font-weight: 700;">Last checked</div>
                    <div style="margin-top: 4px; color: #fff;">{{ $app->last_checked_at?->diffForHumans() ?? 'never' }}</div>
                </div>
                <div>
                    <div style="font-size: 10px; text-transform: uppercase; color: rgba(255,255,255,0.3); font-weight: 700;">Type</div>
                    <div style="margin-top: 4px; color: #fff;">{{ $app->application_type ?? '—' }}</div>
                </div>
            </div>

            @if ($app->status_description)
                <p style="font-size: 13px; color: rgba(255,255,255,0.7);">{{ $app->status_description }}</p>
            @endif

            <div style="margin-top: 16px;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.35); margin-bottom: 8px;">Status history</div>
                @if ($app->transitions->isEmpty())
                    <div style="font-size: 12px; color: rgba(255,255,255,0.35);">No transitions recorded yet.</div>
                @else
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        @foreach ($app->transitions as $t)
                            <li style="padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.03); display: flex; justify-content: space-between; gap: 12px; font-size: 12px;">
                                <div>
                                    <span style="color: #fff;">{{ $t->status }}</span>
                                    @if ($t->exited_at)
                                        <span style="color: rgba(255,255,255,0.35); margin-left: 8px;">{{ $t->duration_days }} day{{ $t->duration_days === 1 ? '' : 's' }}</span>
                                    @else
                                        <span class="badge badge-blue" style="margin-left: 8px;">Current</span>
                                    @endif
                                </div>
                                <div style="color: rgba(255,255,255,0.4);">
                                    {{ $t->entered_at?->format('Y-m-d') }}
                                    @if ($t->exited_at) → {{ $t->exited_at->format('Y-m-d') }} @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body" style="color: rgba(255,255,255,0.4); font-size: 13px;">
            This client has not added any applications yet.
        </div>
    </div>
@endforelse
@endsection
