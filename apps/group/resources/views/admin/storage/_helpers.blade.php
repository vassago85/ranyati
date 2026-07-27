{{--
    Small view helpers/partials used across the storage module admin views.
    Nothing rendered directly; other views pull from this via @include.
--}}

@once
    <style>
        .storage-badge-in { background: rgba(52,211,153,0.12); color: #34d399; }
        .storage-badge-out { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.45); }
        .storage-mono { font-family: 'JetBrains Mono', 'Courier New', ui-monospace, monospace; font-variant-numeric: tabular-nums; letter-spacing: 0.02em; }
        .storage-slotgrid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; }
        .storage-slotgrid .slot { padding: 10px 12px; border-radius: 8px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); font-size: 12px; }
        .storage-slotgrid .slot .ref { font-weight: 700; color: #fff; letter-spacing: 0.03em; }
        .storage-slotgrid .slot .sub { color: rgba(255,255,255,0.45); font-size: 11px; margin-top: 2px; }
    </style>
@endonce
