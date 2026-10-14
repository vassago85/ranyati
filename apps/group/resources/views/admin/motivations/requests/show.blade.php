@extends('admin.layout')

@section('title', $motivation->reference().' — '.$motivation->applicationType->name)

@php
    use App\Models\MotivationRequest;
    use App\Support\Motivations\DocumentKinds;

    $profile = $motivation->user->clientProfile;
    $isSubmitted = $motivation->status === MotivationRequest::STATUS_SUBMITTED;
    $isVerified = in_array($motivation->status, [MotivationRequest::STATUS_VERIFIED, MotivationRequest::STATUS_AWAITING_ENDORSEMENT], true);
    $allChecked = $motivation->documents->isNotEmpty() && $motivation->documents->every(fn ($d) => $d->checked_at);
    // Does this request already have a generated book / set of per-firearm
    // letters? Drives the Generate vs Regenerate label split on the Actions
    // card (bug 2 — "Regenerate motivations" showed even when nothing had
    // been generated yet, which was misleading at the Verified / Awaiting
    // endorsement stages).
    $hasGenerated = ($usesTemplates ?? false)
        ? ($letters ?? collect())->isNotEmpty()
        : (bool) $motivation->pdf_path;
    $generateNoun = ($usesTemplates ?? false) ? 'motivations' : 'book';
@endphp

@section('content')
<div class="grid-2" style="margin-bottom:16px;">
    <div class="card">
        <div class="card-header"><h2>Member</h2></div>
        <div class="card-body" style="line-height:1.9;font-size:13px;">
            <div style="color:#fff;font-weight:700;">{{ $motivation->user->name }}</div>
            <div style="color:rgba(255,255,255,.5);">{{ $motivation->user->email }}</div>
            @if ($profile)
                <hr style="border:0;border-top:1px solid rgba(255,255,255,.06);margin:12px 0;">
                <div><strong>Full name:</strong> {{ $profile->full_names }} {{ $profile->surname }}</div>
                <div><strong>ID:</strong> {{ $profile->id_number }} <span style="color:rgba(255,255,255,.4);">(age {{ \App\Models\ClientProfile::ageFromIdNumber($profile->id_number) ?? '—' }})</span></div>
                <div><strong>Occupation:</strong> {{ $profile->occupation }}</div>
                <div><strong>Cell:</strong> {{ $profile->cell }}</div>
                <div><strong>Address:</strong> {{ $profile->physical_address }}</div>
                @if ($profile->association_name)
                    <div><strong>Association:</strong> {{ $profile->association_name }} (member #{{ $profile->association_number ?: '—' }})</div>
                    <div style="color:rgba(255,255,255,.4);font-size:11px;margin-top:4px;">Verify the number on NRAPA/Natshoot before confirming details.</div>
                @endif
            @else
                <p style="color:rgba(255,255,255,.4);">Member hasn't completed their profile.</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>Status</h2></div>
        <div class="card-body" style="font-size:13px;line-height:1.9;">
            <div><span class="badge badge-orange">{{ $motivation->statusLabel() }}</span></div>
            @if ($motivation->submitted_at)   <div>Submitted {{ $motivation->submitted_at->diffForHumans() }}</div> @endif
            @if ($motivation->verified_at)    <div>Confirmed by {{ $motivation->verifier?->name ?: '—' }} {{ $motivation->verified_at->diffForHumans() }}</div> @endif
            @if ($motivation->pdf_generated_at) <div>Book generated {{ $motivation->pdf_generated_at->diffForHumans() }}</div> @endif
            @if ($motivation->approved_at)    <div>Marked ready by {{ $motivation->approver?->name ?: '—' }} {{ $motivation->approved_at->diffForHumans() }}</div> @endif
            @if ($motivation->printed_at)     <div>Printed {{ $motivation->printed_at->diffForHumans() }}</div> @endif
            @if ($motivation->invoiced_at)    <div>Invoice sent {{ $motivation->invoiced_at->diffForHumans() }} (R{{ number_format((float) $motivation->invoice_total, 2) }})</div> @endif
            @if ($motivation->paid_at)        <div>Paid {{ $motivation->paid_at->diffForHumans() }} ({{ $motivation->payment_method }}@if ($motivation->payment_reference) · {{ $motivation->payment_reference }}@endif)</div> @endif
            @if ($motivation->ready_at)       <div>Ready for collection {{ $motivation->ready_at->diffForHumans() }}</div> @endif
            @if ($motivation->shipped_at)     <div>Shipped {{ $motivation->shipped_at->diffForHumans() }} · {{ $motivation->courier }} · {{ $motivation->waybill_number }}</div> @endif
            @if ($motivation->delivered_at)   <div>Delivered {{ $motivation->delivered_at->diffForHumans() }}</div> @endif
            @if ($motivation->delivery_method)
                <div style="color:rgba(255,255,255,.5);font-size:11px;margin-top:4px;">Member selected: {{ $motivation->deliveryMethodLabel() }}</div>
            @endif
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-header">
        <h2>Answers</h2>
        <a href="{{ route('admin.motivation-requests.show', $motivation) }}#answers" class="btn btn-sm btn-secondary">Jump to section</a>
    </div>
    <div class="card-body" id="answers">
        @forelse ($answersDisplay as $section)
            <div style="margin-bottom:16px;">
                <div style="font-size:11px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px;">{{ $section['section'] }}</div>
                <dl style="display:grid;grid-template-columns:260px 1fr;gap:4px 16px;font-size:13px;">
                    @foreach ($section['items'] as $item)
                        <dt style="color:rgba(255,255,255,.5);">{{ $item['label'] }}</dt>
                        <dd style="color:#fff;white-space:pre-line;">{{ $item['value'] }}</dd>
                    @endforeach
                </dl>
            </div>
        @empty
            <p style="color:rgba(255,255,255,.4);">No answers yet.</p>
        @endforelse
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><h2>Documents</h2></div>
    <div class="card-body">
        {{-- Bug 6: when no documents have been uploaded the headers-only
             table was useless — a sentence of guidance is more helpful.
             The upload UI lives on the member portal and on the admin
             "edit on behalf" page. --}}
        @if ($motivation->documents->isEmpty())
            <div class="empty-state">
                <strong>No documents uploaded yet.</strong><br>
                The member can upload required documents from their portal,
                or you can upload on their behalf from the
                <a href="{{ route('admin.motivation-requests.edit', $motivation) }}" style="color:#F58220;">Edit answers &amp; documents</a>
                page.
            </div>
        @else
        <table>
            <thead>
                <tr>
                    <th>Kind</th>
                    <th>File</th>
                    <th>Reference</th>
                    <th>Dates</th>
                    <th>Flags</th>
                    <th style="text-align:right;">Checked</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($motivation->documents as $doc)
                    @php
                        // Use the full @php .. @endphp form here: the
                        // one-line variant mis-parses `??` combined with
                        // $requirements[$doc->kind] subscripts and leaves
                        // the opening PHP tag unterminated.
                        $req = $requirements[$doc->kind] ?? null;
                        $flags = $doc->flags($req);
                    @endphp
                    <tr>
                        <td style="color:#fff;">{{ $doc->kindLabel() }}</td>
                        <td>
                            <a href="{{ route('admin.motivation-requests.documents.view', [$motivation, $doc]) }}" target="_blank" style="color:#F58220;">
                                {{ $doc->original_name ?: basename($doc->path) }}
                            </a>
                        </td>
                        <td>{{ $doc->reference_number ?: '—' }}</td>
                        <td>
                            @if ($doc->issued_on) Issued {{ $doc->issued_on->format('Y-m-d') }}<br>@endif
                            @if ($doc->expires_on) Expires {{ $doc->expires_on->format('Y-m-d') }} @endif
                            @if (! $doc->issued_on && ! $doc->expires_on) — @endif
                        </td>
                        <td>
                            @if ($flags === [])
                                <span class="badge badge-green">OK</span>
                            @else
                                @foreach ($flags as $flag)
                                    <span class="badge" style="background:rgba(239,68,68,.15);color:#f87171;display:block;margin-bottom:2px;">{{ $flag }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <form method="POST" action="{{ route('admin.motivation-requests.documents.check', [$motivation, $doc]) }}" style="display:inline;">
                                @csrf
                                <button class="btn btn-sm {{ $doc->checked_at ? 'btn-primary' : 'btn-secondary' }}">
                                    {{ $doc->checked_at ? '✓ Checked' : 'Mark checked' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @endif {{-- /documents table-vs-empty-state --}}

        @if ($motivation->status === MotivationRequest::STATUS_AWAITING_ENDORSEMENT || ! $motivation->documents->contains('kind', DocumentKinds::ENDORSEMENT))
            @if (($requirements[DocumentKinds::ENDORSEMENT] ?? null) !== null)
                <div style="border-top:1px solid rgba(255,255,255,.06);margin-top:16px;padding-top:16px;">
                    <h3 style="color:#fff;font-size:14px;margin-bottom:8px;">Upload NRAPA / Natshoot endorsement</h3>
                    <form method="POST" action="{{ route('admin.motivation-requests.endorsement', $motivation) }}" enctype="multipart/form-data" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;align-items:end;">
                        @csrf
                        <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png" class="form-input">
                        <input type="text" name="reference_number" placeholder="Certificate number" class="form-input">
                        <input type="date" name="issued_on" class="form-input">
                        <button class="btn btn-primary">Upload &amp; rebuild</button>
                    </form>
                </div>
            @endif
        @endif
    </div>
</div>

@if ($usesTemplates ?? false)
<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><h2>Firearm motivations</h2></div>
    <div class="card-body">
        @if (($letters ?? collect())->isEmpty())
            <p style="color:rgba(255,255,255,.6);font-size:13px;">
                @if ($isSubmitted)
                    No motivations generated yet. Confirm the details above to generate a firearm motivation per listed firearm from the attached template.
                @else
                    No motivations generated yet. Use <strong style="color:#fff;">Generate {{ $generateNoun }}</strong> under <em>Actions</em> below to produce one firearm motivation per listed firearm from the attached template.
                @endif
            </p>
        @else
            <table style="width:100%;border-collapse:collapse;margin-bottom:12px;">
                <thead>
                    <tr style="color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.08em;">
                        <th style="text-align:left;padding:4px 8px;">Motivation</th>
                        <th style="text-align:left;padding:4px 8px;">Status</th>
                        <th style="text-align:left;padding:4px 8px;">Issues</th>
                        <th style="text-align:right;padding:4px 8px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($letters as $letter)
                        <tr style="border-top:1px solid rgba(255,255,255,.06);">
                            <td style="padding:6px 8px;color:#fff;font-size:13px;">{{ $letter->label() }}</td>
                            <td style="padding:6px 8px;">
                                <span style="padding:2px 8px;border-radius:999px;font-size:11px;letter-spacing:.06em;text-transform:uppercase;{{ $letter->isFinal() ? 'background:rgba(34,197,94,0.14);color:#86efac;' : 'background:rgba(245,130,32,0.14);color:#F58220;' }}">
                                    {{ $letter->statusLabel() }}
                                </span>
                                @if ($letter->generation_status === \App\Models\MotivationLetter::GEN_FAILED)
                                    <span style="color:#fca5a5;font-size:11px;">failed</span>
                                @endif
                            </td>
                            <td style="padding:6px 8px;font-size:11px;color:rgba(255,255,255,.5);">
                                @if (!empty($letter->missing))
                                    Missing: {{ implode(', ', $letter->missing) }}<br>
                                @endif
                                @if (!empty($letter->warnings))
                                    {{ implode('; ', $letter->warnings) }}
                                @endif
                                @if ($letter->error)
                                    <span style="color:#fca5a5;">{{ $letter->error }}</span>
                                @endif
                            </td>
                            <td style="padding:6px 8px;text-align:right;white-space:nowrap;">
                                @if ($letter->pdf_path)
                                    <a href="{{ route('admin.motivation-requests.letters.open', [$motivation, $letter]) }}" target="_blank" class="btn btn-sm btn-secondary">Open</a>
                                    <a href="{{ route('admin.motivation-requests.letters.download', [$motivation, $letter]) }}" class="btn btn-sm btn-secondary">Download</a>
                                @endif
                                @if ($letter->isDraft())
                                    <form method="POST" action="{{ route('admin.motivation-requests.letters.regenerate', [$motivation, $letter]) }}" style="display:inline;">
                                        @csrf <button class="btn btn-sm btn-secondary">Regenerate</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.motivation-requests.letters.finalise', [$motivation, $letter]) }}" style="display:inline;">
                                        @csrf <button class="btn btn-sm btn-primary">Finalise</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.motivation-requests.letters.destroy', [$motivation, $letter]) }}" style="display:inline;" onsubmit="return confirm('Delete this motivation draft?')">
                                        @csrf @method('DELETE') <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="color:rgba(255,255,255,.5);font-size:12px;">
                @php $drafts = $letters->where('status', 'draft')->count(); $finals = $letters->where('status', 'final')->count(); @endphp
                {{ $letters->count() }} motivation(s): {{ $drafts }} draft, {{ $finals }} final. All must be finalised before marking ready to print.
            </div>
        @endif
    </div>
</div>
@endif

<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><h2>Actions</h2></div>
    <div class="card-body" style="display:flex;flex-wrap:wrap;gap:8px;">
        @if ($isSubmitted)
            <form method="POST" action="{{ route('admin.motivation-requests.verify', $motivation) }}">
                @csrf <button class="btn btn-primary">Confirm details &amp; generate {{ ($usesTemplates ?? false) ? 'motivations' : 'book' }}</button>
            </form>
        @endif

        {{-- Bug 2: don't call the button "Regenerate" before anything has
             been generated. The underlying POST (/regenerate) is the same
             action in either case — only the label changes. Hide it entirely
             while the request is still at Submitted, because the dedicated
             "Confirm details & generate" button directly above already covers
             that transition. --}}
        @if ($motivation->status !== MotivationRequest::STATUS_PRINTED && ! $isSubmitted)
            <form method="POST" action="{{ route('admin.motivation-requests.regenerate', $motivation) }}">
                @csrf <button class="btn {{ $hasGenerated ? 'btn-secondary' : 'btn-primary' }}">
                    {{ $hasGenerated ? 'Regenerate' : 'Generate' }} {{ $generateNoun }}
                </button>
            </form>
        @endif

        @if (! ($usesTemplates ?? false) && $motivation->pdf_path)
            <a href="{{ route('admin.motivation-requests.book', $motivation) }}" class="btn btn-primary">Download book PDF</a>
        @endif

        @php
            $canMarkReady = ($usesTemplates ?? false)
                ? ($isVerified && $letters->isNotEmpty() && $letters->every(fn ($l) => $l->isFinal()))
                : ($isVerified && $motivation->pdf_path);
        @endphp

        @if ($canMarkReady)
            <form method="POST" action="{{ route('admin.motivation-requests.ready', $motivation) }}" onsubmit="return {{ $allChecked ? 'true' : "confirm('Some documents haven\\'t been checked yet — mark ready anyway?')" }}">
                @csrf <button class="btn btn-primary">Mark ready to print</button>
            </form>
        @endif

        @if ($motivation->status === MotivationRequest::STATUS_READY_TO_PRINT)
            <form method="POST" action="{{ route('admin.motivation-requests.printed', $motivation) }}">
                @csrf <button class="btn btn-primary">Mark printed</button>
            </form>
        @endif

        @if (! in_array($motivation->status, [MotivationRequest::STATUS_PRINTED, MotivationRequest::STATUS_COMPLETED], true))
            <form method="POST" action="{{ route('admin.motivation-requests.request-changes', $motivation) }}" style="display:flex;gap:4px;align-items:center;flex:1 1 100%;margin-top:8px;">
                @csrf
                <input type="text" name="reviewer_notes" placeholder="What should the member change?" class="form-input" required>
                <button class="btn btn-secondary">Request changes</button>
            </form>
        @endif
    </div>
</div>

@php
    $canInvoice = in_array($motivation->status, [MotivationRequest::STATUS_PRINTED, MotivationRequest::STATUS_READY_TO_PRINT], true)
                   || $motivation->status === MotivationRequest::STATUS_INVOICED;
    $invoice = $motivation->invoice();
    $proofOfPayment = $motivation->proofOfPayment();
@endphp

@if ($canInvoice || $motivation->isInFulfilment())
<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><h2>Fulfilment</h2></div>
    <div class="card-body" style="display:grid;gap:16px;">

        {{-- Invoice --}}
        @if ($canInvoice)
            <div>
                <h3 style="color:#fff;font-size:13px;margin-bottom:6px;">
                    1. {{ $invoice ? 'Replace invoice' : 'Upload invoice & notify member' }}
                </h3>
                @if ($invoice)
                    <div style="font-size:12px;color:rgba(255,255,255,.6);margin-bottom:8px;">
                        Current invoice:
                        <a href="{{ route('admin.motivation-requests.documents.view', [$motivation, $invoice]) }}" target="_blank" style="color:#F58220;">{{ $invoice->original_name }}</a>
                        · R{{ number_format((float) $motivation->invoice_total, 2) }}
                    </div>
                @endif
                <form method="POST" action="{{ route('admin.motivation-requests.invoice', $motivation) }}" enctype="multipart/form-data" style="display:grid;grid-template-columns:2fr 1fr auto;gap:8px;align-items:end;">
                    @csrf
                    <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png" class="form-input">
                    <input type="number" step="0.01" min="0" name="invoice_total" placeholder="Total R" class="form-input" required>
                    <button class="btn btn-primary">{{ $invoice ? 'Replace & re-send' : 'Send invoice' }}</button>
                </form>
            </div>
        @endif

        {{-- Proof of payment --}}
        @if ($proofOfPayment || $motivation->status === MotivationRequest::STATUS_INVOICED)
            <div>
                <h3 style="color:#fff;font-size:13px;margin-bottom:6px;">Proof of payment</h3>
                @if ($proofOfPayment)
                    <div style="font-size:12px;color:rgba(255,255,255,.7);">
                        Member uploaded
                        <a href="{{ route('admin.motivation-requests.documents.view', [$motivation, $proofOfPayment]) }}" target="_blank" style="color:#F58220;">{{ $proofOfPayment->original_name }}</a>
                        {{ $proofOfPayment->created_at->diffForHumans() }}.
                    </div>
                @else
                    <div style="font-size:12px;color:rgba(255,255,255,.4);">Member has not yet uploaded proof of payment.</div>
                @endif
            </div>
        @endif

        {{-- Mark paid --}}
        @if (in_array($motivation->status, [MotivationRequest::STATUS_INVOICED, MotivationRequest::STATUS_PRINTED], true))
            <div>
                <h3 style="color:#fff;font-size:13px;margin-bottom:6px;">2. Record payment</h3>
                <form method="POST" action="{{ route('admin.motivation-requests.paid', $motivation) }}" style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:end;">
                    @csrf
                    <select name="payment_method" class="form-input" required>
                        <option value="">Payment method…</option>
                        <option value="eft">EFT</option>
                        <option value="cash">Cash on collection</option>
                        <option value="card">Card on collection</option>
                        <option value="other">Other</option>
                    </select>
                    <input type="text" name="payment_reference" placeholder="Reference (optional)" class="form-input">
                    <button class="btn btn-primary">Mark paid &amp; notify</button>
                </form>
            </div>
        @endif

        {{-- Deliver --}}
        @if ($motivation->status === MotivationRequest::STATUS_PAID)
            <div class="grid-2">
                @if ($motivation->delivery_method !== MotivationRequest::DELIVERY_COURIER)
                    <form method="POST" action="{{ route('admin.motivation-requests.ready-for-collection', $motivation) }}" style="display:grid;gap:6px;">
                        @csrf
                        <h3 style="color:#fff;font-size:13px;margin:0;">3a. Ready for collection</h3>
                        <input type="text" name="note" placeholder="Collection note (optional)" class="form-input">
                        <button class="btn btn-primary">Notify member it's ready</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.motivation-requests.shipped', $motivation) }}" style="display:grid;gap:6px;">
                    @csrf
                    <h3 style="color:#fff;font-size:13px;margin:0;">3b. Shipped</h3>
                    <input type="text" name="courier" placeholder="Courier (e.g. Courier Guy)" class="form-input" required>
                    <input type="text" name="waybill_number" placeholder="Waybill number" class="form-input" required>
                    <button class="btn btn-primary">Mark shipped &amp; notify</button>
                </form>
            </div>
        @endif

        {{-- Mark completed --}}
        @if (in_array($motivation->status, [MotivationRequest::STATUS_READY_FOR_COLLECTION, MotivationRequest::STATUS_SHIPPED], true))
            <form method="POST" action="{{ route('admin.motivation-requests.completed', $motivation) }}">
                @csrf
                <button class="btn btn-primary">Close out — handed over to member</button>
            </form>
        @endif

    </div>
</div>
@endif
@endsection
