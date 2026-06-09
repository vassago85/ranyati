@extends('admin.layout')
@section('title', 'Documents')

@section('actions')
    <a href="{{ route('admin.documents.create') }}" class="btn btn-primary">
        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Upload Documents
    </a>
@endsection

@section('content')
    <div class="stat-grid" style="margin-bottom: 24px;">
        <div class="card stat-card">
            <div class="stat-label">Total</div>
            <div class="stat-value">{{ $documents->total() }}</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Published</div>
            <div class="stat-value" style="color: #34d399;">{{ $documents->where('is_published', true)->count() }}</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Hidden</div>
            <div class="stat-value" style="color: rgba(255,255,255,0.3);">{{ $documents->where('is_published', false)->count() }}</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Total Downloads</div>
            <div class="stat-value">{{ \App\Models\Document::sum('download_count') }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>All Documents</h2>
            <a href="{{ route('forms') }}" target="_blank" class="btn btn-secondary btn-sm">View public page &rarr;</a>
        </div>
        @if($documents->isEmpty())
            <div class="card-body" style="text-align: center; padding: 48px 20px;">
                <svg style="width:40px;height:40px;color:rgba(255,255,255,0.1);margin:0 auto 12px;" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                <p style="font-size: 13px; color: rgba(255,255,255,0.3);">No documents yet. Upload your questionnaires and forms.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width: 44px;">Type</th>
                        <th>Document</th>
                        <th>Category</th>
                        <th>Size</th>
                        <th>Downloads</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $document)
                        <tr>
                            <td>
                                <span class="badge badge-orange" style="font-size: 9px; letter-spacing: 0.05em;">{{ $document->extension }}</span>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #fff;">{{ $document->title }}</div>
                                <div style="font-size: 11px; color: rgba(255,255,255,0.25); margin-top: 2px;">{{ $document->original_name }}</div>
                            </td>
                            <td>{{ $document->category ?: '—' }}</td>
                            <td>{{ $document->size_for_humans }}</td>
                            <td>{{ $document->download_count }}</td>
                            <td>
                                @if($document->is_published)
                                    <span class="badge badge-green">Published</span>
                                @else
                                    <span class="badge badge-zinc">Hidden</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <a href="{{ route('admin.documents.edit', $document) }}" class="btn btn-secondary btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.documents.toggle', $document) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm">{{ $document->is_published ? 'Hide' : 'Publish' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.documents.delete', $document) }}" style="display:inline;" onsubmit="return confirm('Delete this document permanently?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($documents->hasPages())
                <div style="padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.04);">
                    {{ $documents->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
