@extends('admin.layout')
@section('title', 'New self-storage intake')

@section('content')
    @include('admin.storage._helpers')

    <form method="POST" action="{{ route('admin.storage.self.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="type" value="{{ \App\Models\StorageAgreement::TYPE_SELF_STORAGE }}">

        <div class="card">
            <div class="card-header"><h2>Client details</h2></div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Client name</label>
                        <input type="text" name="client_name" class="form-input" value="{{ old('client_name') }}" placeholder="P.J. Smith" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email (for collection notices)</label>
                        <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Monthly rate (per firearm)</label>
                        <input type="number" step="0.01" min="0" name="storage_rate" class="form-input storage-mono" value="{{ old('storage_rate', number_format($defaultRate, 2, '.', '')) }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" rows="2" class="form-input">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        @include('admin.storage._intake_items')

        <div style="margin-top: 20px; display:flex; gap: 8px;">
            <button class="btn btn-primary">Create self-storage agreement + firearms</button>
            <a href="{{ route('admin.storage.self') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
