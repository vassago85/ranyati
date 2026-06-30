@php
    $status = $circuitStatus ?? ['is_open' => false, 'consecutive_failures' => 0, 'threshold' => 3, 'last_failure' => null, 'open_until' => null, 'last_tripped_at' => null];
@endphp

@if ($status['is_open'])
    <div class="card" style="background: linear-gradient(180deg, rgba(239,68,68,0.15), rgba(239,68,68,0.05)); border-color: rgba(239,68,68,0.3); margin-bottom: 24px;">
        <div class="card-body" style="display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; align-items: center;">
            <div>
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: #ef4444;">Circuit breaker open</div>
                <div style="margin-top: 6px; font-size: 14px; color: #fff;">
                    Daily SAPS checks are paused
                    @if ($status['open_until'])
                        until <strong>{{ $status['open_until']->format('Y-m-d H:i') }}</strong> ({{ $status['open_until']->diffForHumans() }}).
                    @else
                        .
                    @endif
                </div>
                @if ($status['last_failure'])
                    <div style="margin-top: 6px; font-size: 12px; color: rgba(255,255,255,0.6);">
                        Last error: <span style="color: #fca5a5;">{{ $status['last_failure']['error'] }}</span>
                    </div>
                @endif
            </div>
            <form method="POST" action="{{ route('admin.applications.circuit.reset') }}" onsubmit="return confirm('Reset the circuit breaker? The next scheduled run will hit SAPS again.')">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm">Reset circuit</button>
            </form>
        </div>
    </div>
@elseif ($status['consecutive_failures'] > 0)
    <div class="alert alert-info" style="margin-bottom: 24px;">
        <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
        <div>
            {{ $status['consecutive_failures'] }} consecutive SAPS failure{{ $status['consecutive_failures'] === 1 ? '' : 's' }} recorded.
            Circuit will trip at {{ $status['threshold'] }}.
            @if ($status['last_failure'])
                <span style="color: rgba(255,255,255,0.45); margin-left: 4px;">Last: {{ $status['last_failure']['error'] }}</span>
            @endif
        </div>
    </div>
@endif
