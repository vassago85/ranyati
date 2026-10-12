@extends('admin.layout')

@section('title', 'Application types')
@section('actions')
    <a href="{{ route('admin.application-types.create') }}" class="btn btn-primary">New type</a>
@endsection

@section('content')
<div class="card">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Motivation?</th>
                <th>Blocks</th>
                <th>Requests</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($types as $type)
                <tr>
                    <td style="color:#fff;"><strong>{{ $type->name }}</strong></td>
                    <td>{{ $type->categoryLabel() }}</td>
                    <td>{{ $type->has_motivation ? 'Yes' : 'No' }}</td>
                    <td>{{ $type->blocks_count }}</td>
                    <td>{{ $type->requests_count }}</td>
                    <td>
                        @if ($type->is_active)
                            <span class="badge badge-green">Active</span>
                        @else
                            <span class="badge badge-zinc">Inactive</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex;gap:6px;">
                            <a href="{{ route('admin.application-types.edit', $type) }}" class="btn btn-sm btn-secondary">Edit</a>
                            <form method="POST" action="{{ route('admin.application-types.clone', $type) }}">@csrf
                                <button class="btn btn-sm btn-secondary">Clone</button>
                            </form>
                            <form method="POST" action="{{ route('admin.application-types.destroy', $type) }}" onsubmit="return confirm('Delete this application type?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:rgba(255,255,255,0.4);padding:40px;">No application types yet. Click "New type" to add one.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
