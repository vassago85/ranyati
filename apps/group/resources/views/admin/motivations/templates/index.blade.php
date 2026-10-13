@extends('admin.layout')

@section('title', 'Firearm motivation templates')
@section('actions')
    <a href="{{ route('admin.motivation-templates.assets.index') }}" class="btn btn-secondary">Assets</a>
    <a href="{{ route('admin.motivation-templates.create') }}" class="btn btn-primary">New template</a>
@endsection

@section('content')
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;" class="filter-tabs">
    <a href="{{ route('admin.motivation-templates.index') }}" class="filter-tab {{ $activeStatus ? '' : 'active' }}">All <span class="count">{{ $counts->sum() }}</span></a>
    @foreach ($statuses as $key => $label)
        <a href="{{ route('admin.motivation-templates.index', ['status' => $key] + ($activeCategory ? ['category' => $activeCategory] : [])) }}" class="filter-tab {{ $activeStatus === $key ? 'active' : '' }}">
            {{ $label }} <span class="count">{{ $counts[$key] ?? 0 }}</span>
        </a>
    @endforeach
</div>

<div style="margin-bottom:16px;">
    <select class="form-input" style="max-width:280px;" onchange="window.location = this.value">
        <option value="{{ route('admin.motivation-templates.index', $activeStatus ? ['status' => $activeStatus] : []) }}" {{ $activeCategory ? '' : 'selected' }}>All categories</option>
        @foreach ($categories as $key => $label)
            <option value="{{ route('admin.motivation-templates.index', array_filter(['status' => $activeStatus, 'category' => $key])) }}" {{ $activeCategory === $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Status</th>
                <th>Published</th>
                <th>Attached to</th>
                <th>Versions</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($templates as $template)
                <tr>
                    <td style="color:#fff;">
                        <strong>{{ $template->name }}</strong>
                        @if ($template->description)
                            <div style="font-size:11px;color:rgba(255,255,255,.4);margin-top:2px;">{{ \Illuminate\Support\Str::limit($template->description, 120) }}</div>
                        @endif
                    </td>
                    <td>{{ $template->categoryLabel() }}</td>
                    <td>
                        @switch($template->status)
                            @case('published')
                                <span class="badge badge-green">{{ $template->statusLabel() }}</span>
                                @break
                            @case('archived')
                                <span class="badge badge-zinc">{{ $template->statusLabel() }}</span>
                                @break
                            @default
                                <span class="badge badge-orange">{{ $template->statusLabel() }}</span>
                        @endswitch
                    </td>
                    <td>
                        @if ($template->publishedVersion)
                            v{{ $template->publishedVersion->version }}
                            <div style="font-size:11px;color:rgba(255,255,255,.4);">{{ $template->publishedVersion->published_at?->diffForHumans() }}</div>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @forelse ($template->applicationTypes as $type)
                            <span class="badge badge-zinc">{{ $type->name }}</span>
                        @empty
                            <span style="color:rgba(255,255,255,.4);">—</span>
                        @endforelse
                    </td>
                    <td>{{ $template->versions_count }}</td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
                            <a href="{{ route('admin.motivation-templates.edit', $template) }}" class="btn btn-sm btn-secondary">Open builder</a>
                            <form method="POST" action="{{ route('admin.motivation-templates.duplicate', $template) }}">@csrf
                                <button class="btn btn-sm btn-secondary">Duplicate</button>
                            </form>
                            @if ($template->status !== 'archived')
                                <form method="POST" action="{{ route('admin.motivation-templates.archive', $template) }}" onsubmit="return confirm('Archive this template?')">@csrf
                                    <button class="btn btn-sm btn-danger">Archive</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.motivation-templates.unarchive', $template) }}">@csrf
                                    <button class="btn btn-sm btn-secondary">Un-archive</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:rgba(255,255,255,0.4);padding:40px;">No templates yet. Click "New template" to add one.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
