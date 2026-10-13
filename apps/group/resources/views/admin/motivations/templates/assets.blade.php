@extends('admin.layout')

@section('title', 'Template assets')

@section('actions')
    <a href="{{ route('admin.motivation-templates.index') }}" class="btn btn-secondary">Back to templates</a>
@endsection

@section('content')
<style>
    .asset-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px; }
    .asset-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 10px; text-align: center; position: relative; }
    .asset-card img { background: #fff; border-radius: 4px; max-width: 100%; max-height: 110px; margin: 0 auto; display: block; }
    .asset-card .asset-name { color: #fff; font-size: 13px; margin-top: 6px; }
    .asset-card .asset-meta { color: rgba(255,255,255,0.4); font-size: 11px; }
    .asset-card .asset-delete { position: absolute; top: 6px; right: 6px; }
</style>

<div class="card" style="margin-bottom:14px;">
    <h2 style="margin:0 0 10px;">Upload a new asset</h2>
    @if (session('success'))
        <div style="padding:8px;margin-bottom:10px;background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.2);border-radius:6px;color:#86efac;font-size:12px;">
            {{ session('success') }}
        </div>
    @endif
    <form method="POST" action="{{ route('admin.motivation-templates.assets.store') }}" enctype="multipart/form-data" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:10px;align-items:end;">
        @csrf
        <label><div style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:4px;">Name</div>
            <input class="form-input" name="name" placeholder="e.g. Club logo" required></label>
        <label><div style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:4px;">Alt text</div>
            <input class="form-input" name="alt" placeholder="For accessibility"></label>
        <label><div style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:4px;">Category</div>
            <select class="form-input" name="category" required>
                @foreach ($categories as $k => $label)
                    <option value="{{ $k }}">{{ $label }}</option>
                @endforeach
            </select></label>
        <label><div style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:4px;">File (JPG or PNG, max 4MB)</div>
            <input class="form-input" type="file" name="file" accept=".jpg,.jpeg,.png" required></label>
        <button class="btn btn-primary btn-sm" type="submit">Upload</button>
    </form>
</div>

<div class="card">
    <h2 style="margin:0 0 10px;">Library ({{ $assets->count() }})</h2>
    @if ($assets->isEmpty())
        <div style="padding:40px;text-align:center;color:rgba(255,255,255,.4);">No assets yet. Upload one above.</div>
    @else
        <div class="asset-grid">
            @foreach ($assets as $asset)
                <div class="asset-card">
                    <form method="POST" action="{{ route('admin.motivation-templates.assets.destroy', $asset) }}"
                          class="asset-delete"
                          onsubmit="return confirm('Delete {{ addslashes($asset->name) }}? Templates that use it will show a missing image.');">
                        @csrf @method('DELETE')
                        <button class="mini-btn danger" style="background:rgba(239,68,68,0.15);color:#fca5a5;border:1px solid rgba(239,68,68,0.3);border-radius:4px;padding:1px 6px;font-size:11px;cursor:pointer;" type="submit">×</button>
                    </form>
                    <img src="{{ $asset->url() }}" alt="{{ $asset->alt ?: $asset->name }}">
                    <div class="asset-name">{{ $asset->name }}</div>
                    <div class="asset-meta">{{ $asset->categoryLabel() }} · {{ $asset->width }}×{{ $asset->height }}</div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
