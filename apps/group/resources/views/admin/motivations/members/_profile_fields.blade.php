@php($p = $profile)

@if (! empty($includeAccountEmail))
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h2>Account</h2></div>
        <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <label>
                <span style="display:block;color:rgba(255,255,255,.7);font-size:12px;margin-bottom:4px;">Full name on the account *</span>
                <input type="text" name="name" value="{{ old('name') }}" required class="form-input">
                @error('name')<span style="color:#f87171;font-size:11px;">{{ $message }}</span>@enderror
            </label>
            <label>
                <span style="display:block;color:rgba(255,255,255,.7);font-size:12px;margin-bottom:4px;">Login email *</span>
                <input type="email" name="account_email" value="{{ old('account_email') }}" required class="form-input">
                @error('account_email')<span style="color:#f87171;font-size:11px;">{{ $message }}</span>@enderror
            </label>
        </div>
    </div>
@endif

<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><h2>Personal particulars</h2></div>
    <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        @php
            $fields = [
                ['full_names', 'Full names *', 'text', true],
                ['surname', 'Surname *', 'text', true],
                ['id_number', 'SA ID number *', 'text', true],
                ['occupation', 'Occupation *', 'text', true],
                ['cell', 'Cell *', 'tel', true],
                ['email', 'Contact email *', 'email', true],
                ['tel_work', 'Tel (W)', 'tel', false],
                ['tel_home', 'Tel (H)', 'tel', false],
                ['fax', 'Fax', 'tel', false],
                ['employer', 'Employer / company', 'text', false],
                ['business_address', 'Business address', 'textarea', false],
                ['physical_address', 'Physical address *', 'textarea', true],
                ['postal_address', 'Postal address', 'textarea', false],
                ['spouse_details', "Spouse's ID & full names", 'text', false],
                ['association_name', 'Association (NRAPA / Natshoot)', 'text', false],
                ['association_number', 'Association number', 'text', false],
                ['facebook_name', 'Facebook name', 'text', false],
            ];
        @endphp
        @foreach ($fields as [$name, $label, $type, $required])
            <label style="{{ $type === 'textarea' ? 'grid-column:1 / -1;' : '' }}">
                <span style="display:block;color:rgba(255,255,255,.7);font-size:12px;margin-bottom:4px;">{{ $label }}</span>
                @if ($type === 'textarea')
                    <textarea name="{{ $name }}" rows="2" class="form-input" {{ $required ? 'required' : '' }}>{{ old($name, $p->{$name}) }}</textarea>
                @else
                    <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $p->{$name}) }}" {{ $required ? 'required' : '' }} class="form-input">
                @endif
                @error($name)<span style="color:#f87171;font-size:11px;">{{ $message }}</span>@enderror
            </label>
        @endforeach
        <label style="display:flex;align-items:center;gap:8px;color:#fff;font-size:13px;">
            <input type="checkbox" name="self_employed" value="1" {{ old('self_employed', $p->self_employed) ? 'checked' : '' }}>
            Self-employed
        </label>
        <label style="display:flex;align-items:center;gap:8px;color:#fff;font-size:13px;">
            <input type="checkbox" name="facebook_consent" value="1" {{ old('facebook_consent', $p->facebook_consent) ? 'checked' : '' }}>
            Facebook friend request allowed
        </label>
    </div>
</div>
