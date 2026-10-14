@extends('admin.layout')

@section('title', 'Builder: '.$template->name)

@php
    $initialPayload = [
        'template' => [
            'id' => $template->id,
            'name' => $template->name,
            'slug' => $template->slug,
            'category' => $template->category,
            'description' => $template->description,
            'status' => $template->status,
            'published_version_id' => $template->published_version_id,
        ],
        'draft' => [
            'id' => $draft->id,
            'version' => $draft->version,
            'blocks' => $draft->blocks,
            'settings' => $draft->settings,
            'updated_at' => $draft->updated_at?->toIso8601String(),
        ],
        'palette' => $palette,
        'kinds' => $kinds,
        'placeholderCatalogue' => $placeholderCatalogue,
        'conditionFields' => $conditionFields,
        'operators' => $operators,
        'unaryOps' => array_values($unaryOps),
        'assets' => $assets->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'category' => $a->category,
            'category_label' => $a->categoryLabel(),
            'url' => $a->url(),
            'mime' => $a->mime,
            'alt' => $a->alt,
            'width' => $a->width,
            'height' => $a->height,
        ])->values()->all(),
        'assetCategories' => $assetCategories,
        'documentKinds' => $documentKinds,
        'provinces' => $provinces,
        'previewRequests' => $previewRequests,
        'urls' => [
            'update' => route('admin.motivation-templates.update', $template),
            'autosave' => route('admin.motivation-templates.autosave', $template),
            'publish' => route('admin.motivation-templates.publish', $template),
            'preview' => route('admin.motivation-templates.preview.store', $template),
            'canvasPreview' => route('admin.motivation-templates.canvas-preview', $template),
            'versions' => route('admin.motivation-templates.versions', $template),
            'assetStore' => route('admin.motivation-templates.assets.store'),
            'csrfToken' => csrf_token(),
        ],
    ];
@endphp

@push('head-scripts')
    @vite(['resources/css/template-builder.css', 'resources/js/template-builder/index.js'])
@endpush

@section('actions')
    <a href="{{ route('admin.motivation-templates.index') }}" class="btn btn-secondary">Back</a>
@endsection

@section('content')
<style>
    /* Only the modal + legacy chrome styles stay inline; the canvas/
       builder CSS is in resources/css/template-builder.css. */
    .status-badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 11px; letter-spacing: 0.06em; text-transform: uppercase; }
    .status-badge.is-draft { background: rgba(245,130,32,0.14); color: #F58220; }
    .status-badge.is-published { background: rgba(34,197,94,0.14); color: #86efac; }
    .status-badge.is-archived { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.5); }
    .modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 50; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .modal { background: #0d1424; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; max-width: 90vw; max-height: 90vh; overflow: auto; padding: 20px; }
    .modal.wide { width: 760px; }
    .mini-btn { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 5px; padding: 2px 6px; font-size: 11px; cursor: pointer; }
    .mini-btn:hover { background: rgba(245,130,32,0.15); color: #F58220; }
    .mini-btn.danger:hover { background: rgba(239,68,68,0.15); color: #fca5a5; border-color: rgba(239,68,68,0.3); }
    .settings-body label { display: block; margin-bottom: 10px; font-size: 12px; color: rgba(255,255,255,0.7); }
    .settings-body label .label-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; font-size: 10px; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.4); }
    .version-item { padding: 8px 10px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 6px; margin-bottom: 6px; }
    .mobile-only { display: none; }
    @media (max-width: 1023px) {
        .mobile-only { display: block; padding: 12px; background: rgba(245,130,32,0.08); border: 1px solid rgba(245,130,32,0.3); border-radius: 8px; color: #F58220; font-size: 12px; margin-bottom: 10px; }
        .tb-shell > .tb-pane { margin-bottom: 10px; }
    }
    @media print { .mobile-only { display: none; } }
</style>

<div class="mobile-only">The builder needs a wider screen. Open this page on a laptop or desktop to edit; the mobile view is read-only.</div>

<div x-data='templateBuilder(@json($initialPayload))' x-init="init()">
    @include('admin.motivations.templates._topbar')

    <div class="tb-shell" :class="{'is-right-collapsed': rightCollapsed}">
        @include('admin.motivations.templates._left_panel')
        @include('admin.motivations.templates._canvas')
        @include('admin.motivations.templates._right_panel')
    </div>

    @include('admin.motivations.templates._modals')
</div>
@endsection
