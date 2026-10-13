@extends('admin.layout')

@section('title', 'New firearm motivation template')

@section('content')
<form method="POST" action="{{ route('admin.motivation-templates.store') }}" class="card" style="max-width:720px;">
    @csrf

    @if ($errors->any())
        <div style="margin-bottom:16px;padding:12px;border:1px solid rgba(239,68,68,0.3);background:rgba(239,68,68,0.08);border-radius:6px;color:#fca5a5;">
            {{ $errors->first() }}
        </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr;gap:12px;">
        <label>
            <div style="font-size:11px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Name</div>
            <input class="form-input" name="name" value="{{ old('name') }}" required>
        </label>
        <label>
            <div style="font-size:11px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Slug (optional)</div>
            <input class="form-input" name="slug" value="{{ old('slug') }}" placeholder="auto-generated from the name">
        </label>
        <label>
            <div style="font-size:11px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Category</div>
            <select class="form-input" name="category" required>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <div style="font-size:11px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Description</div>
            <textarea class="form-input" name="description" rows="3">{{ old('description') }}</textarea>
        </label>
    </div>

    <div style="margin-top:16px;display:flex;gap:8px;">
        <button class="btn btn-primary">Create template</button>
        <a href="{{ route('admin.motivation-templates.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection
