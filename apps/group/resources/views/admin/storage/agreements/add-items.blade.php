@extends('admin.layout')
@section('title', 'Add firearms — '.($agreement->isEstate() ? 'Estate agreement #'.$agreement->id : 'Self-storage agreement #'.$agreement->id))

@section('content')
    @include('admin.storage._helpers')

    <div class="card">
        <div class="card-header">
            <h2>{{ $agreement->party_label }}</h2>
            <span class="badge badge-zinc">{{ $agreement->isEstate() ? 'Deceased estate' : 'Self storage' }}</span>
        </div>
        <div class="card-body">
            <div style="font-size: 13px; color: rgba(255,255,255,0.5);">
                Adding firearms to an existing agreement — the client's details stay as recorded.
                Currently {{ $agreement->items->count() }} firearm(s) on this agreement.
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.storage.agreements.items.store', $agreement) }}" enctype="multipart/form-data">
        @csrf

        @include('admin.storage._intake_items')

        <div style="margin-top: 20px; display:flex; gap: 8px;">
            <button class="btn btn-primary">Add to this agreement</button>
            <a href="{{ route('admin.storage.agreements.show', $agreement) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
