@extends('admin.layout')
@section('title', 'Item · '.$item->register_ref)

@section('actions')
    <a class="btn btn-secondary btn-sm" href="{{ route('admin.storage.items.label', $item) }}" target="_blank">Print label</a>
    @if ($item->isInCustody())
        <a class="btn btn-primary btn-sm" href="{{ route('admin.storage.items.collect.form', $item) }}">Collect</a>
    @endif
@endsection

@section('content')
    @include('admin.storage._helpers')

    <div class="card">
        <div class="card-header">
            <h2>{{ $item->register_ref }} · {{ $item->tag_ref }}</h2>
            @if ($item->isInCustody())
                <span class="badge storage-badge-in">In custody</span>
            @else
                <span class="badge storage-badge-out">Released</span>
            @endif
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; font-size: 13px;">
                <div><span style="color:rgba(255,255,255,0.35);">Agreement</span><br>
                    <a href="{{ route('admin.storage.agreements.show', $item->agreement) }}">{{ $item->agreement->party_label }}</a>
                </div>
                <div><span style="color:rgba(255,255,255,0.35);">Make / calibre</span><br>{{ $item->firearm_make }} — {{ $item->cartridge }}</div>
                <div><span style="color:rgba(255,255,255,0.35);">Type / action</span><br>{{ ucfirst($item->firearm_type) }} · {{ $item->action_type }}</div>
                <div><span style="color:rgba(255,255,255,0.35);">Serial</span><br><span class="storage-mono">{{ $item->serial_number }}</span></div>
                <div><span style="color:rgba(255,255,255,0.35);">Date received</span><br>{{ \Illuminate\Support\Carbon::parse($item->date_in)->format('d M Y') }}</div>
                @if ($item->agreement->isSelfStorage())
                    <div><span style="color:rgba(255,255,255,0.35);">Fee due (today)</span><br><span class="storage-mono">R{{ number_format((float) $fee, 2) }}</span> ({{ $item->fullMonthsSinceIntake() }} month{{ $item->fullMonthsSinceIntake() === 1 ? '' : 's' }})</div>
                @endif
            </div>
            @if ($item->condition_notes)
                <div style="margin-top: 16px; padding: 12px; background: rgba(255,255,255,0.03); border-radius: 6px; font-size: 12px; color: rgba(255,255,255,0.6);">
                    <strong>Condition notes:</strong> {{ $item->condition_notes }}
                </div>
            @endif
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-top: 24px;">
        <div>
            <div class="card">
                <div class="card-header"><h2>Custody timeline</h2></div>
                <div class="card-body" style="padding: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>Event</th>
                                <th>By</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($item->events as $ev)
                                <tr>
                                    <td>{{ $ev->occurred_at?->format('d M Y H:i') }}</td>
                                    <td><span class="badge badge-blue">{{ $ev->label() }}</span></td>
                                    <td>{{ $ev->user?->name ?? '—' }}</td>
                                    <td style="white-space: normal;">
                                        {{ $ev->notes ?? '—' }}
                                        @if ($ev->old_tag || $ev->new_tag)
                                            <br><small style="color:rgba(255,255,255,0.4);">{{ $ev->old_tag }} → {{ $ev->new_tag }}</small>
                                        @endif
                                        @if ($ev->released_to_name)
                                            <br><small style="color:rgba(255,255,255,0.4);">Released to {{ $ev->released_to_name }} (ID {{ $ev->released_to_id_number }})</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card" style="margin-top: 24px;">
                <div class="card-header"><h2>Add custody event</h2></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.storage.items.events.store', $item) }}">
                        @csrf
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                            <div class="form-group">
                                <label class="form-label">Event type</label>
                                <select name="event_type" class="form-input">
                                    <option value="inspection">Inspection</option>
                                    <option value="transfer_internal">Internal transfer (shelf/tag move)</option>
                                    <option value="correction">Correction</option>
                                    <option value="note">Note</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Old tag (transfer only)</label>
                                <input type="text" name="old_tag" class="form-input storage-mono" placeholder="{{ $item->tag_ref }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">New tag (transfer only, e.g. AB-R-0042)</label>
                                <input type="text" name="new_tag" class="form-input storage-mono">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="2" class="form-input" required></textarea>
                        </div>
                        <button class="btn btn-primary btn-sm">Record event</button>
                    </form>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header"><h2>Photos</h2></div>
                <div class="card-body">
                    @forelse ($item->photos as $photo)
                        <a href="{{ route('admin.storage.items.files.download', ['item' => $item, 'file' => $photo]) }}" target="_blank" style="display:block; margin-bottom: 8px; font-size: 12px;">
                            {{ $photo->original_name ?? basename($photo->path) }}
                        </a>
                    @empty
                        <div style="color: rgba(255,255,255,0.35); font-size: 12px;">No photos uploaded.</div>
                    @endforelse
                </div>
            </div>

            @if ($item->agreement->isSelfStorage())
                <div class="card" style="margin-top: 24px;">
                    <div class="card-header"><h2>Licence documents</h2></div>
                    <div class="card-body">
                        @forelse ($item->licences as $doc)
                            <a href="{{ route('admin.storage.items.files.download', ['item' => $item, 'file' => $doc]) }}" target="_blank" style="display:block; margin-bottom: 8px; font-size: 12px;">
                                {{ $doc->original_name ?? basename($doc->path) }}
                            </a>
                        @empty
                            <div style="color: rgba(255,255,255,0.35); font-size: 12px;">No licence documents on file.</div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
