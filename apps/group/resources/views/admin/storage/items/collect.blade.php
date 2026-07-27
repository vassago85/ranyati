@extends('admin.layout')
@section('title', 'Collect · '.$item->register_ref)

@section('content')
    @include('admin.storage._helpers')

    <div class="card">
        <div class="card-header">
            <h2>Collect firearm {{ $item->register_ref }}</h2>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 13px; margin-bottom: 20px;">
                <div><span style="color:rgba(255,255,255,0.35);">Firearm</span><br>{{ $item->firearm_make }} — {{ $item->cartridge }} · {{ $item->serial_number }}</div>
                <div><span style="color:rgba(255,255,255,0.35);">Received</span><br>{{ \Illuminate\Support\Carbon::parse($item->date_in)->format('d M Y') }}</div>
                <div><span style="color:rgba(255,255,255,0.35);">Agreement</span><br>{{ $item->agreement->party_label }}</div>
                <div>
                    <span style="color:rgba(255,255,255,0.35);">Fee due</span><br>
                    @if ($item->agreement->isSelfStorage())
                        <span class="storage-mono" style="font-size: 16px; font-weight: 700; color: #F58220;">R{{ number_format((float) $fee, 2) }}</span>
                        <br><small style="color:rgba(255,255,255,0.4);">{{ $item->fullMonthsSinceIntake() }} started month{{ $item->fullMonthsSinceIntake() === 1 ? '' : 's' }} @ R{{ number_format((float) ($item->agreement->storage_rate ?? 0), 2) }}</small>
                    @else
                        <span style="color:rgba(255,255,255,0.45);">Estate — no standing fee</span>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('admin.storage.items.collect', $item) }}">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Collected by — full name</label>
                        <input type="text" name="released_to_name" class="form-input" value="{{ old('released_to_name') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Collected by — SA ID / passport number</label>
                        <input type="text" name="released_to_id_number" class="form-input storage-mono" value="{{ old('released_to_id_number') }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" rows="2" class="form-input"></textarea>
                </div>

                <div style="display:flex; gap: 8px;">
                    <button class="btn btn-primary">Confirm collection</button>
                    <a href="{{ route('admin.storage.items.show', $item) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
