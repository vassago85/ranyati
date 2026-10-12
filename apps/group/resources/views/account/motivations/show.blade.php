@extends('account.layout')

@section('title', $motivation->applicationType->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $motivation->applicationType->name }}</h1>
            <p class="text-white/50 text-sm mt-1">Reference {{ $motivation->reference() }} &middot; <span class="status-pill px-2 py-0.5 rounded-full text-xs">{{ $motivation->memberStatusLabel() }}</span></p>
        </div>
        <div class="flex gap-2">
            @if ($motivation->isMemberEditable())
                <a href="{{ route('account.motivations.edit', $motivation) }}" class="btn-primary rounded-lg px-4 py-2 text-sm no-underline">Continue editing</a>
            @endif
            @if ($motivation->status === \App\Models\MotivationRequest::STATUS_DRAFT)
                <form method="POST" action="{{ route('account.motivations.destroy', $motivation) }}" onsubmit="return confirm('Delete this draft?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-secondary rounded-lg px-4 py-2 text-sm text-red-300">Delete draft</button>
                </form>
            @endif
        </div>
    </div>

    @if ($motivation->status === \App\Models\MotivationRequest::STATUS_CHANGES_REQUESTED && $motivation->reviewer_notes)
        <div class="card p-5 border border-amber-500/30">
            <h2 class="text-amber-200 font-semibold mb-2">Changes requested</h2>
            <p class="text-white/70 text-sm whitespace-pre-line">{{ $motivation->reviewer_notes }}</p>
        </div>
    @endif

    <div class="card p-5">
        <h2 class="text-white font-semibold mb-3">Next step</h2>
        <p class="text-white/70 text-sm">
            @switch ($motivation->status)
                @case(\App\Models\MotivationRequest::STATUS_DRAFT)
                @case(\App\Models\MotivationRequest::STATUS_CHANGES_REQUESTED)
                    Keep filling in the questionnaire and uploading your documents, including the signed Power of Attorney, then submit.
                    @break
                @case(\App\Models\MotivationRequest::STATUS_SUBMITTED)
                    Our team is reviewing your details and documents.
                    @break
                @case(\App\Models\MotivationRequest::STATUS_VERIFIED)
                @case(\App\Models\MotivationRequest::STATUS_AWAITING_ENDORSEMENT)
                @case(\App\Models\MotivationRequest::STATUS_READY_TO_PRINT)
                    Your details have been confirmed and the motivation is being prepared.
                    @break
                @case(\App\Models\MotivationRequest::STATUS_PRINTED)
                    Your motivation is complete. Our office will send through an invoice shortly.
                    @break
                @case(\App\Models\MotivationRequest::STATUS_INVOICED)
                    Please settle the invoice (EFT or on collection) and upload proof of payment below.
                    @break
                @case(\App\Models\MotivationRequest::STATUS_PAID)
                    Payment received — thank you. We're preparing your motivation for {{ $motivation->delivery_method === 'courier' ? 'shipping' : 'collection' }}.
                    @break
                @case(\App\Models\MotivationRequest::STATUS_READY_FOR_COLLECTION)
                    Your motivation is ready to collect from our office.
                    @break
                @case(\App\Models\MotivationRequest::STATUS_SHIPPED)
                    Your motivation has been handed to the courier.
                    @if ($motivation->waybill_number)
                        <br><span class="text-white/90">Waybill: <strong>{{ $motivation->waybill_number }}</strong>{{ $motivation->courier ? ' via '.$motivation->courier : '' }}</span>
                    @endif
                    @break
                @case(\App\Models\MotivationRequest::STATUS_COMPLETED)
                    This motivation is complete and has been handed over to you. Good luck with your application.
                    @break
            @endswitch
        </p>
    </div>

    @if (in_array($motivation->status, [
            \App\Models\MotivationRequest::STATUS_INVOICED,
            \App\Models\MotivationRequest::STATUS_PAID,
            \App\Models\MotivationRequest::STATUS_READY_FOR_COLLECTION,
            \App\Models\MotivationRequest::STATUS_SHIPPED,
            \App\Models\MotivationRequest::STATUS_COMPLETED,
        ], true))
        @php
            $invoice = $motivation->invoice();
            $proofOfPayment = $motivation->proofOfPayment();
        @endphp
        <div class="card p-5">
            <h2 class="text-white font-semibold mb-3">Payment</h2>
            @if ($invoice)
                <div class="flex items-center justify-between bg-white/5 rounded-lg px-3 py-2 text-sm mb-3">
                    <div>
                        <div class="text-white">Invoice{{ $motivation->invoice_total ? ' — R'.number_format((float) $motivation->invoice_total, 2) : '' }}</div>
                        <div class="text-white/40 text-xs">{{ $invoice->original_name }}</div>
                    </div>
                    <a href="{{ route('account.motivations.documents.view', [$motivation, $invoice]) }}" target="_blank" class="btn-primary rounded-lg px-3 py-1.5 text-xs no-underline">Download invoice</a>
                </div>
            @endif

            @if ($motivation->status === \App\Models\MotivationRequest::STATUS_INVOICED)
                @if ($proofOfPayment)
                    <p class="text-white/60 text-sm">We received your proof of payment {{ $proofOfPayment->created_at->diffForHumans() }} — we'll confirm as soon as the funds reflect.</p>
                @else
                    <p class="text-white/60 text-sm mb-3">After paying by EFT (reference <strong class="text-white">{{ $motivation->reference() }}</strong>) please upload proof of payment below. If you're paying on collection, you can skip this.</p>
                    <form method="POST" action="{{ route('account.motivations.documents.upload', $motivation) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="kind" value="{{ \App\Support\Motivations\DocumentKinds::PROOF_OF_PAYMENT }}">
                        <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png" class="form-input text-sm">
                        <button type="submit" class="btn-secondary rounded-lg px-3 py-2 text-sm">Upload proof</button>
                    </form>
                @endif
            @elseif ($proofOfPayment)
                <p class="text-white/60 text-sm">Proof of payment uploaded.</p>
            @endif
        </div>
    @endif

    <div class="card p-5">
        <h2 class="text-white font-semibold mb-3">Uploaded documents</h2>
        @if ($motivation->documents->isEmpty())
            <p class="text-white/50 text-sm">No documents uploaded yet.</p>
        @else
            <ul class="space-y-2">
                @foreach ($motivation->documents as $doc)
                    <li class="flex items-center justify-between bg-white/5 rounded-lg px-3 py-2 text-sm">
                        <div>
                            <div class="text-white">{{ $doc->kindLabel() }}</div>
                            <div class="text-white/40 text-xs">{{ $doc->original_name }}</div>
                        </div>
                        <a href="{{ route('account.motivations.documents.view', [$motivation, $doc]) }}" target="_blank" class="btn-secondary rounded-lg px-3 py-1.5 text-xs no-underline">View</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
