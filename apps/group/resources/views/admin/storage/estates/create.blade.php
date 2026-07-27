@extends('admin.layout')
@section('title', 'New estate intake')

@section('content')
    @include('admin.storage._helpers')

    <form method="POST" action="{{ route('admin.storage.estates.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="type" value="{{ \App\Models\StorageAgreement::TYPE_DECEASED_ESTATE }}">

        <div class="card">
            <div class="card-header"><h2>Estate details</h2></div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Estate late (EL)</label>
                        <input type="text" name="estate_late" class="form-input" value="{{ old('estate_late') }}" placeholder="e.g. J.M. Smith" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bank (optional)</label>
                        <select name="bank" class="form-input">
                            <option value="">— none —</option>
                            @foreach ($banks as $b)
                                <option value="{{ $b }}" @selected(old('bank') === $b)>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Attorneys (optional)</label>
                        <input type="text" name="attorneys" class="form-input" value="{{ old('attorneys') }}">
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
            <button class="btn btn-primary">Create estate agreement + firearms</button>
            <a href="{{ route('admin.storage.estates') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
