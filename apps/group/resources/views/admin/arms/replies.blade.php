@extends('admin.layout')
@section('title', 'Saved replies')

@section('actions')
    <a href="{{ route('admin.arms.enquiries') }}" class="btn btn-secondary btn-sm">Back to enquiries</a>
@endsection

@section('content')
    <p style="font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 16px; max-width: 640px;">
        These are the messages offered when you reply on WhatsApp. Rewrite the placeholder text before you send it.
        You can use {name}, {firearm}, {price} and {stock_number}.
    </p>

    <div class="card" style="margin-bottom: 16px;">
        <div class="card-header"><h2>Add a reply</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.arms.replies.store') }}" style="display:grid; gap: 12px;">
                @csrf
                <label>
                    <span class="form-label">Title</span>
                    <input type="text" name="title" class="form-input" required maxlength="120" spellcheck="true" lang="en-ZA" value="{{ old('title') }}">
                </label>
                <label>
                    <span class="form-label">Message</span>
                    <textarea name="body" class="form-input" rows="4" required spellcheck="true" lang="en-ZA">{{ old('body') }}</textarea>
                </label>
                <button type="submit" class="btn btn-primary" style="min-height:44px; justify-self:start;">Save reply</button>
            </form>
        </div>
    </div>

    @forelse($replies as $reply)
        <div class="card" style="margin-bottom: 12px;" x-data="{ confirm: false }">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.arms.replies.update', $reply) }}" style="display:grid; gap: 12px;">
                    @csrf
                    @method('PUT')
                    <label>
                        <span class="form-label">Title</span>
                        <input type="text" name="title" class="form-input" required maxlength="120" spellcheck="true" lang="en-ZA" value="{{ $reply->title }}">
                    </label>
                    <label>
                        <span class="form-label">Message</span>
                        <textarea name="body" class="form-input" rows="5" required spellcheck="true" lang="en-ZA">{{ $reply->body }}</textarea>
                    </label>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <button type="submit" class="btn btn-primary" style="min-height:44px;">Update</button>
                        <button type="button" class="btn btn-danger" style="min-height:44px;" @click="confirm = true">Delete</button>
                    </div>
                </form>
                <div x-show="confirm" x-cloak style="margin-top: 12px;">
                    <p style="color:#fff; margin-bottom: 8px;">Delete “{{ $reply->title }}”? This cannot be undone.</p>
                    <form method="POST" action="{{ route('admin.arms.replies.delete', $reply) }}" style="display:flex; gap:8px;">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" @click="confirm = false">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="card"><div class="card-body"><p style="color: rgba(255,255,255,0.7);">No saved replies yet. Add one above.</p></div></div>
    @endforelse
@endsection
