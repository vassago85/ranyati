@once
    <style>
        .arms-badge {
            display: inline-flex;
            align-items: center;
            min-height: 22px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.01em;
            line-height: 1.2;
        }
        .arms-badge-active { background: rgba(52, 211, 153, 0.15); color: #34d399; }
        .arms-badge-sold { background: rgba(239, 68, 68, 0.18); color: #fca5a5; }
        .arms-badge-archived { background: rgba(255, 255, 255, 0.08); color: rgba(255, 255, 255, 0.55); }
        .arms-badge-featured { background: rgba(245, 130, 32, 0.16); color: #fdba74; }
        .arms-badge-reduced { background: rgba(239, 68, 68, 0.15); color: #fca5a5; }
        .arms-badge-amber { background: rgba(251, 191, 36, 0.15); color: #fbbf24; }
        .arms-badge-muted { background: rgba(255, 255, 255, 0.08); color: rgba(255, 255, 255, 0.55); }
        .arms-badge-new { background: rgba(52, 211, 153, 0.15); color: #34d399; }
    </style>
@endonce
@php
    $tone = $tone ?? null;
    $label = $label ?? null;
    if ($tone === null && isset($listing)) {
        if ($listing->isLive()) {
            $tone = 'active';
            $label = 'Active';
        } elseif ($listing->status === 'sold') {
            $tone = 'sold';
            $label = 'Sold';
        } else {
            $tone = 'archived';
            $label = 'Archived';
        }
    }
@endphp
@if($label)
    <span class="arms-badge arms-badge-{{ $tone }}">{{ $label }}</span>
@endif
