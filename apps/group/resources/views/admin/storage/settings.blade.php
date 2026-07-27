@extends('admin.layout')
@section('title', 'Storage settings')

@section('content')
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <form method="POST" action="{{ route('admin.storage.settings.save') }}">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h2>Cloudflare R2 (private bucket)</h2>
                    <span class="badge {{ $configured ? 'badge-green' : 'badge-orange' }}">
                        {{ $configured ? 'Configured' : 'Not configured' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label" for="r2_account_id">Cloudflare account ID</label>
                        <input type="text" id="r2_account_id" name="r2_account_id" class="form-input" value="{{ old('r2_account_id', $accountId) }}">
                        <div class="form-hint">If set, the endpoint below is auto-derived when left blank ({account}.r2.cloudflarestorage.com).</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="r2_endpoint">Endpoint URL</label>
                        <input type="text" id="r2_endpoint" name="r2_endpoint" class="form-input" placeholder="https://<account>.r2.cloudflarestorage.com" value="{{ old('r2_endpoint', $endpoint) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="r2_bucket">Bucket name</label>
                        <input type="text" id="r2_bucket" name="r2_bucket" class="form-input" placeholder="ranyati-custody" value="{{ old('r2_bucket', $bucket) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="r2_access_key">Access key ID</label>
                        <input type="text" id="r2_access_key" name="r2_access_key" class="form-input" value="{{ old('r2_access_key', $accessKey) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="r2_secret_key">Secret access key</label>
                        <input type="password" id="r2_secret_key" name="r2_secret_key" class="form-input" placeholder="{{ $secretMask ?: 'paste a new secret to set/rotate' }}" value="">
                        @if ($secretMask)
                            <div class="form-hint">A key is already saved (encrypted). Leave blank to keep it. Currently: <span class="storage-mono">{{ $secretMask }}</span></div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top: 24px;">
                <div class="card-header"><h2>Self-storage defaults</h2></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label" for="default_rate">Default monthly storage rate (per firearm)</label>
                        <input type="number" step="0.01" min="0" id="default_rate" name="default_rate" class="form-input" value="{{ old('default_rate', number_format($defaultRate, 2, '.', '')) }}">
                        <div class="form-hint">Prefilled into new self-storage agreements. Individual agreements can override this at intake.</div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <button class="btn btn-primary">Save storage settings</button>
            </div>
        </form>

        <div>
            <div class="card">
                <div class="card-header"><h2>Test R2 connection</h2></div>
                <div class="card-body">
                    <p style="font-size:12px; color: rgba(255,255,255,0.55); margin-bottom: 12px;">
                        Writes and deletes a small text object to confirm credentials, endpoint, and bucket work end-to-end.
                    </p>
                    <form method="POST" action="{{ route('admin.storage.settings.test') }}">
                        @csrf
                        <button class="btn btn-secondary" @if(!$configured) disabled title="Save R2 credentials first" @endif>Run test</button>
                    </form>
                </div>
            </div>

            <div class="card" style="margin-top: 24px;">
                <div class="card-header"><h2>What lives here</h2></div>
                <div class="card-body" style="font-size: 12px; color: rgba(255,255,255,0.55); line-height: 1.55;">
                    <p><strong>Bucket is private.</strong> Firearm photos and licence uploads are stored in Cloudflare R2 and only ever exposed via short-lived (5&nbsp;min) presigned URLs from the admin panel.</p>
                    <p style="margin-top:8px;">The secret key is encrypted at rest with Laravel <span class="storage-mono">Crypt</span> (APP_KEY). Rotate here without a redeploy.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
