<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MotivationPhase;
use App\Enums\MotivationStatus;
use App\Http\Controllers\Controller;
use App\Mail\MotivationUpdate;
use App\Models\ApplicationType;
use App\Models\MotivationDocument;
use App\Models\MotivationRequest;
use App\Support\Mask;
use App\Support\Motivations\BookRenderer;
use App\Support\Motivations\DocumentKinds;
use App\Support\Motivations\MotivationStorage;
use App\Support\Motivations\PowerOfAttorneyGenerator;
use App\Support\Motivations\QuestionSchema;
use App\Support\Motivations\Templates\LetterGenerator;
use App\Support\Motivations\Templates\TemplateSelector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MotivationRequestController extends Controller
{
    /**
     * Phase 2 (mockup 02): the motivations list.
     *
     * Query-string shape (every filter round-trips):
     *   q         free-text search (name, email, reference, masked ID/cell digits)
     *   filter    'needs-action' | 'in-progress' — the dashboard KPI
     *             tiles deep-link into these pseudo-filters
     *   phase     one of MotivationPhase keys (intake/preparation/payment/fulfilment)
     *   status    one of MotivationStatus values (overrides phase)
     *   type      application type id
     *   waiting   '3' or '7' (minimum days in current stage)
     *   sort      'oldest' | 'newest' (default newest)
     *   per_page  10|25|50 (default 25)
     */
    public function index(Request $request)
    {
        [$query, $filters] = $this->buildListQuery($request);

        $counts = $this->tabCounts($request);

        return view('admin.motivations.requests.index', [
            'requests' => $query->paginate((int) $filters['per_page'])->withQueryString(),
            'filters' => $filters,
            'counts' => $counts,
            'types' => ApplicationType::query()->orderBy('name')->get(['id', 'name', 'category']),
            'phases' => MotivationPhase::ordered(),
            'statuses' => MotivationStatus::ordered(),
        ]);
    }

    /**
     * CSV export of the current filter. Deliberately *un*masked — this is
     * an admin-only download for back-office processing and auditing.
     * The client-facing list view keeps the masked columns.
     */
    public function export(Request $request): StreamedResponse
    {
        [$query] = $this->buildListQuery($request);
        $query->getQuery()->offset = null;
        $query->getQuery()->limit = null;

        $filename = 'ranyati-motivations-'.now()->format('Y-m-d-His').'.csv';

        return new StreamedResponse(function () use ($query) {
            $out = fopen('php://output', 'wb');
            fputcsv($out, [
                'Reference', 'Client name', 'Client email',
                'ID number', 'Cell',
                'Application type', 'Category',
                'Status key', 'Status label', 'Phase',
                'Days in current stage',
                'Created', 'Updated',
            ]);
            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $m) {
                    $status = MotivationStatus::from($m->status);
                    $profile = $m->user->clientProfile;
                    fputcsv($out, [
                        $m->reference(),
                        $m->user->name,
                        $m->user->email,
                        $profile?->id_number,
                        $profile?->cell,
                        $m->applicationType->name,
                        $m->applicationType->categoryLabel(),
                        $status->value,
                        $status->label(),
                        $status->phase()->label(),
                        // Explicit cast to int. Newer Carbon returns a
                        // float microsecond value when the two
                        // timestamps are on the same instant, which
                        // leaks "4.33e-6" into the CSV for same-day
                        // rows if we don't round down.
                        (int) ($m->updated_at ? abs($m->updated_at->diffInDays(now())) : 0),
                        optional($m->created_at)->format('Y-m-d H:i:s'),
                        optional($m->updated_at)->format('Y-m-d H:i:s'),
                    ]);
                }
            });
            fclose($out);
        }, Response::HTTP_OK, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Build the shared query used by both the HTML list and the CSV
     * export so a URL that filters down to one row in the browser is
     * guaranteed to export the exact same one row.
     *
     * @return array{0: \Illuminate\Database\Eloquent\Builder, 1: array<string, mixed>}
     */
    private function buildListQuery(Request $request): array
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'filter' => in_array($request->query('filter'), ['needs-action', 'in-progress'], true) ? $request->query('filter') : null,
            'phase' => in_array($request->query('phase'), array_map(fn ($p) => $p->value, MotivationPhase::ordered()), true) ? $request->query('phase') : null,
            'status' => array_key_exists($request->query('status', ''), MotivationStatus::labels()) ? $request->query('status') : null,
            'type' => $request->query('type') ? (int) $request->query('type') : null,
            'waiting' => in_array($request->query('waiting'), ['3', '7'], true) ? (int) $request->query('waiting') : null,
            'sort' => $request->query('sort') === 'oldest' ? 'oldest' : 'newest',
            'per_page' => in_array((int) $request->query('per_page'), [10, 25, 50], true) ? (int) $request->query('per_page') : 25,
        ];

        $query = MotivationRequest::query()->with(['user.clientProfile', 'applicationType']);

        if ($filters['filter'] === 'needs-action') {
            $query->whereIn('status', MotivationStatus::needsActionValues());
        } elseif ($filters['filter'] === 'in-progress') {
            $query->whereNotIn('status', [
                MotivationStatus::DRAFT->value,
                MotivationStatus::COMPLETED->value,
            ]);
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        } elseif ($filters['phase']) {
            $phase = MotivationPhase::from($filters['phase']);
            $query->whereIn('status', array_map(fn ($s) => $s->value, $phase->statuses()));
        }

        if ($filters['type']) {
            $query->where('application_type_id', $filters['type']);
        }

        if ($filters['waiting']) {
            $query->where('updated_at', '<=', now()->subDays($filters['waiting']));
        }

        if ($filters['q'] !== '') {
            $digits = preg_replace('/\D+/', '', $filters['q']) ?? '';
            $like = '%'.$filters['q'].'%';
            $digitLike = $digits !== '' ? '%'.$digits.'%' : null;

            $query->where(function ($sub) use ($like, $digits, $digitLike) {
                $sub->whereHas('user', fn ($u) => $u->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like));

                // Reference search: "RM-000012", "RM12", "12" all map to id=12.
                $refDigits = ltrim($digits, '0');
                if ($refDigits !== '') {
                    $sub->orWhere('motivation_requests.id', (int) $refDigits);
                }

                if ($digitLike) {
                    $sub->orWhereHas('user.clientProfile', fn ($p) => $p->where('id_number', 'like', $digitLike)
                        ->orWhere('cell', 'like', $digitLike));
                }
            });
        }

        $query->orderBy('updated_at', $filters['sort'] === 'oldest' ? 'asc' : 'desc')->orderByDesc('id');

        return [$query, $filters];
    }

    /**
     * Counts for the 7 phase tabs. Each count honours the search query
     * and the type/waiting filters so the user sees a live breakdown of
     * the current filtered set, not an all-time snapshot.
     *
     * @return array<string, int>
     */
    private function tabCounts(Request $request): array
    {
        // Clone a tab-count baseline query that applies the same q/type/waiting
        // filters as the main list, but ignores status/phase/filter so the
        // tabs show what's reachable from the current context.
        $baseline = fn () => tap(MotivationRequest::query()->with([]),
            function ($q) use ($request) {
                if ($type = (int) $request->query('type')) {
                    $q->where('application_type_id', $type);
                }
                if (in_array($request->query('waiting'), ['3', '7'], true)) {
                    $q->where('updated_at', '<=', now()->subDays((int) $request->query('waiting')));
                }
                if (($qText = trim((string) $request->query('q', ''))) !== '') {
                    $digits = preg_replace('/\D+/', '', $qText) ?? '';
                    $like = '%'.$qText.'%';
                    $digitLike = $digits !== '' ? '%'.$digits.'%' : null;
                    $q->where(function ($sub) use ($like, $digits, $digitLike) {
                        $sub->whereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like));
                        $refDigits = ltrim($digits, '0');
                        if ($refDigits !== '') {
                            $sub->orWhere('motivation_requests.id', (int) $refDigits);
                        }
                        if ($digitLike) {
                            $sub->orWhereHas('user.clientProfile', fn ($p) => $p->where('id_number', 'like', $digitLike)->orWhere('cell', 'like', $digitLike));
                        }
                    });
                }
            });

        return [
            'all' => $baseline()->count(),
            'needs-action' => $baseline()->whereIn('status', MotivationStatus::needsActionValues())->count(),
            'intake' => $baseline()->whereIn('status', array_map(fn ($s) => $s->value, MotivationPhase::INTAKE->statuses()))->count(),
            'preparation' => $baseline()->whereIn('status', array_map(fn ($s) => $s->value, MotivationPhase::PREPARATION->statuses()))->count(),
            'payment' => $baseline()->whereIn('status', array_map(fn ($s) => $s->value, MotivationPhase::PAYMENT->statuses()))->count(),
            'fulfilment' => $baseline()->whereIn('status', array_map(fn ($s) => $s->value, MotivationPhase::FULFILMENT->statuses()))->count(),
            'completed' => $baseline()->where('status', MotivationStatus::COMPLETED->value)->count(),
        ];
    }

    public function show(MotivationRequest $motivation)
    {
        $motivation->load([
            'user.clientProfile', 'applicationType.blocks', 'applicationType.templates',
            'documents.checker', 'verifier', 'approver',
            'letters' => fn ($q) => $q->orderByRaw('firearm_index IS NULL asc')->orderBy('firearm_index'),
        ]);

        $requirements = $motivation->applicableRequirements();
        $usesTemplates = TemplateSelector::for($motivation) !== null || $motivation->applicationType?->templates->isNotEmpty();

        return view('admin.motivations.requests.show', [
            'motivation' => $motivation,
            'requirements' => $requirements,
            'documentsByKind' => $motivation->documents->groupBy('kind'),
            'answersDisplay' => $this->buildAnswersDisplay($motivation),
            'documentKinds' => DocumentKinds::all(),
            'usesTemplates' => $usesTemplates,
            'letters' => $motivation->letters,
        ]);
    }

    public function updateAnswers(Request $request, MotivationRequest $motivation)
    {
        abort_if($motivation->status === MotivationRequest::STATUS_PRINTED, 403);

        $sections = $motivation->applicationType->sections();
        $validated = $request->validate(QuestionSchema::rules($sections));

        $motivation->update([
            'answers' => QuestionSchema::normalizeAnswers($sections, $validated['answers'] ?? []),
        ]);

        return back()->with('success', 'Answers updated.');
    }

    /**
     * Admin-on-behalf: show the member's questionnaire so the admin can fill
     * it in for a walk-in customer. Reuses the member's edit blade, which
     * flips its action URLs when `$asAdmin` is true.
     */
    public function editOnBehalf(MotivationRequest $motivation)
    {
        $motivation->load(['applicationType.blocks', 'documents', 'user.clientProfile']);

        return view('admin.motivations.requests.edit', [
            'motivation' => $motivation,
            'type' => $motivation->applicationType,
            'requirements' => $motivation->applicableRequirements(),
            'documentKinds' => DocumentKinds::all(),
        ]);
    }

    public function uploadDocumentOnBehalf(Request $request, MotivationRequest $motivation)
    {
        $kind = (string) $request->input('kind');
        $requirements = $motivation->applicableRequirements();
        $allowedKinds = array_merge(array_keys(DocumentKinds::all()), [DocumentKinds::POWER_OF_ATTORNEY, DocumentKinds::INVOICE]);

        abort_unless(in_array($kind, $allowedKinds, true), 422);
        // Admin is allowed to upload regardless of status (unless already printed).
        abort_if(in_array($motivation->status, [MotivationRequest::STATUS_COMPLETED], true), 403);

        $data = $request->validate([
            'file' => 'required|file|mimes:'.implode(',', MotivationDocument::ALLOWED_EXTENSIONS).'|max:'.config('motivations.max_upload_kb'),
            'reference_number' => 'nullable|string|max:120',
            'issued_on' => 'nullable|date',
            'expires_on' => 'nullable|date',
        ]);

        $file = $data['file'];
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $path = MotivationStorage::documentPath($motivation->id, $kind, $ext);
        $disk = MotivationStorage::put($path, file_get_contents($file->getRealPath()));

        $motivation->documents()->create([
            'kind' => $kind,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'reference_number' => $data['reference_number'] ?? null,
            'issued_on' => $data['issued_on'] ?? null,
            'expires_on' => $data['expires_on'] ?? null,
            'uploaded_by' => $request->user()->id,
            'checked_by' => $request->user()->id,
            'checked_at' => now(),
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    public function deleteDocument(Request $request, MotivationRequest $motivation, MotivationDocument $document)
    {
        abort_unless($document->motivation_request_id === $motivation->id, 404);

        $document->delete();

        return back()->with('success', 'Document removed.');
    }

    /**
     * Submit a draft on behalf of a walk-in customer. Captures the delivery
     * method chosen by the admin and dispatches the usual internal
     * notification (but not the customer-facing one — the admin is already
     * standing in front of the customer).
     */
    public function submitOnBehalf(Request $request, MotivationRequest $motivation)
    {
        abort_unless(in_array($motivation->status, [MotivationRequest::STATUS_DRAFT, MotivationRequest::STATUS_CHANGES_REQUESTED], true), 403);
        $motivation->loadMissing(['applicationType.blocks', 'documents', 'user.clientProfile']);

        $data = $request->validate([
            'delivery_method' => ['required', Rule::in(array_keys(MotivationRequest::DELIVERY_METHODS))],
            'notify_member' => 'nullable|boolean',
        ]);

        $missingAnswers = $motivation->missingAnswers();
        $missingDocs = $motivation->missingDocuments();
        if ($missingAnswers !== [] || $missingDocs !== []) {
            return back()->with('error', 'Still missing answers or documents.')
                ->withErrors(['answers' => $missingAnswers, 'documents' => $missingDocs]);
        }

        $motivation->update([
            'status' => MotivationRequest::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'delivery_method' => $data['delivery_method'],
        ]);

        if ($request->boolean('notify_member')) {
            Mail::send(new MotivationUpdate($motivation, MotivationUpdate::STAGE_SUBMITTED));
        }
        Mail::send(new MotivationUpdate($motivation, MotivationUpdate::STAGE_NEW_SUBMISSION_INTERNAL));

        return redirect()->route('admin.motivation-requests.show', $motivation)
            ->with('success', 'Submitted on behalf — the review workflow can now begin.');
    }

    public function downloadPoa(MotivationRequest $motivation)
    {
        return PowerOfAttorneyGenerator::stream($motivation);
    }

    public function verify(Request $request, MotivationRequest $motivation)
    {
        $motivation->loadMissing(['user.clientProfile', 'applicationType.blocks', 'documents']);

        $missingAnswers = $motivation->missingAnswers();
        $missingDocs = $motivation->missingDocuments();
        if ($missingAnswers !== [] || $missingDocs !== []) {
            return back()->with('error', 'Cannot confirm details: answers or documents are missing.');
        }

        $profile = $motivation->user->clientProfile?->toPlaceholderArray() ?? [];
        $motivation->update([
            'status' => MotivationRequest::STATUS_VERIFIED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'profile_snapshot' => $profile,
            'blocks_snapshot' => $motivation->applicationType->blockDefinitions(),
        ]);

        // Prefer the new template pipeline when the application type has one
        // or more templates attached; otherwise fall back to the legacy book.
        if (TemplateSelector::for($motivation->fresh(['applicationType', 'user.clientProfile', 'documents']))) {
            return $this->generateLetters($motivation, $request);
        }

        return $this->generateBook($motivation);
    }

    public function requestChanges(Request $request, MotivationRequest $motivation)
    {
        $data = $request->validate(['reviewer_notes' => 'required|string|max:2000']);

        $motivation->update([
            'status' => MotivationRequest::STATUS_CHANGES_REQUESTED,
            'reviewer_notes' => $data['reviewer_notes'],
        ]);

        $motivation->loadMissing(['user.clientProfile', 'applicationType']);
        Mail::send(new MotivationUpdate($motivation, MotivationUpdate::STAGE_CHANGES_REQUESTED, $data['reviewer_notes']));

        return back()->with('success', 'Changes requested — the member can now update their application.');
    }

    public function regenerate(Request $request, MotivationRequest $motivation)
    {
        if (TemplateSelector::for($motivation->fresh(['applicationType', 'user.clientProfile', 'documents']))) {
            return $this->generateLetters($motivation, $request);
        }

        return $this->generateBook($motivation);
    }

    /**
     * Generate letters via the new template pipeline. On success, flip the
     * request status to AWAITING_ENDORSEMENT or VERIFIED based on whether
     * an endorsement is still needed.
     */
    private function generateLetters(MotivationRequest $motivation, Request $request): RedirectResponse
    {
        $generator = new LetterGenerator($request->user()->id);
        $result = $generator->generate($motivation);

        $motivation->update([
            'status' => $motivation->needsEndorsement()
                ? MotivationRequest::STATUS_AWAITING_ENDORSEMENT
                : MotivationRequest::STATUS_VERIFIED,
        ]);

        $message = count($result['letters']).' motivation(s) generated.';
        if ($result['missing'] !== []) {
            $message .= ' Missing placeholders: '.implode(', ', $result['missing']).'.';
        }
        if ($result['warnings'] !== []) {
            return back()->with('error', $message.' '.implode(' ', $result['warnings']));
        }

        return back()->with('success', $message);
    }

    private function generateBook(MotivationRequest $motivation): RedirectResponse
    {
        $motivation->loadMissing(['applicationType.blocks', 'documents', 'user.clientProfile']);

        try {
            $result = BookRenderer::render($motivation);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Could not generate the book: '.$e->getMessage());
        }

        // Replace any previously generated book — only one is kept at a time.
        if ($motivation->pdf_path) {
            MotivationStorage::delete($motivation->pdf_disk ?? MotivationStorage::defaultDisk(), $motivation->pdf_path);
        }

        $path = MotivationStorage::bookPath($motivation->id);
        $disk = MotivationStorage::put($path, $result['bytes']);

        $motivation->update([
            'pdf_disk' => $disk,
            'pdf_path' => $path,
            'pdf_generated_at' => now(),
            'status' => $motivation->needsEndorsement() ? MotivationRequest::STATUS_AWAITING_ENDORSEMENT : MotivationRequest::STATUS_VERIFIED,
        ]);

        $message = 'Book generated.';
        if ($result['missing'] !== []) {
            $message .= ' Missing placeholders: '.implode(', ', $result['missing']);
        }

        return back()->with('success', $message);
    }

    public function downloadBook(MotivationRequest $motivation)
    {
        abort_unless($motivation->pdf_path, 404);

        return MotivationStorage::response(
            $motivation->pdf_disk ?: MotivationStorage::defaultDisk(),
            $motivation->pdf_path,
            'motivation-'.$motivation->reference().'.pdf',
            inline: false,
        );
    }

    public function checkDocument(Request $request, MotivationRequest $motivation, MotivationDocument $document)
    {
        abort_unless($document->motivation_request_id === $motivation->id, 404);

        $document->update([
            'checked_by' => $document->checked_at ? null : $request->user()->id,
            'checked_at' => $document->checked_at ? null : now(),
        ]);

        return back();
    }

    public function viewDocument(MotivationRequest $motivation, MotivationDocument $document)
    {
        abort_unless($document->motivation_request_id === $motivation->id, 404);

        return MotivationStorage::response(
            $document->disk,
            $document->path,
            $document->original_name ?: basename($document->path),
        );
    }

    public function uploadEndorsement(Request $request, MotivationRequest $motivation)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:'.implode(',', MotivationDocument::ALLOWED_EXTENSIONS).'|max:'.config('motivations.max_upload_kb'),
            'reference_number' => 'nullable|string|max:120',
            'issued_on' => 'nullable|date',
        ]);

        $file = $data['file'];
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $path = MotivationStorage::documentPath($motivation->id, DocumentKinds::ENDORSEMENT, $ext);
        $disk = MotivationStorage::put($path, file_get_contents($file->getRealPath()));

        $motivation->documents()->create([
            'kind' => DocumentKinds::ENDORSEMENT,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'reference_number' => $data['reference_number'] ?? null,
            'issued_on' => $data['issued_on'] ?? null,
            'uploaded_by' => $request->user()->id,
        ]);

        $fresh = $motivation->fresh(['applicationType.blocks', 'documents', 'user.clientProfile']);

        // Regenerate using whichever pipeline the type is wired for.
        if (TemplateSelector::for($fresh)) {
            return $this->generateLetters($fresh, $request);
        }

        return $this->generateBook($fresh);
    }

    public function markReady(Request $request, MotivationRequest $motivation)
    {
        $motivation->loadMissing(['documents', 'letters']);

        $usesTemplates = TemplateSelector::for($motivation->fresh(['applicationType', 'user.clientProfile', 'documents'])) !== null;

        if ($usesTemplates) {
            if ($motivation->letters->isEmpty()) {
                return back()->with('error', 'Generate the motivation(s) first.');
            }
            $pending = $motivation->letters->filter(fn ($l) => ! $l->isFinal());
            if ($pending->isNotEmpty()) {
                return back()->with('error', 'Finalise every motivation first ('.$pending->count().' still in draft).');
            }
        } elseif (! $motivation->pdf_path) {
            return back()->with('error', 'Generate the book first.');
        }

        $requirements = $motivation->applicableRequirements();
        $unchecked = [];
        foreach ($motivation->documents as $doc) {
            if (! $doc->checked_at) {
                $unchecked[] = $doc->kindLabel();
            }
            $flags = $doc->flags($requirements[$doc->kind] ?? null);
            if ($flags !== []) {
                $unchecked[] = $doc->kindLabel().' ('.implode('; ', $flags).')';
            }
        }

        if ($unchecked !== []) {
            return back()->with('error', 'Resolve these document issues first: '.implode('; ', array_unique($unchecked)));
        }

        $motivation->update([
            'status' => MotivationRequest::STATUS_READY_TO_PRINT,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Ready to print.');
    }

    public function markPrinted(MotivationRequest $motivation)
    {
        $motivation->update([
            'status' => MotivationRequest::STATUS_PRINTED,
            'printed_at' => now(),
        ]);

        return back()->with('success', 'Marked as printed.');
    }

    public function uploadInvoice(Request $request, MotivationRequest $motivation)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:'.implode(',', MotivationDocument::ALLOWED_EXTENSIONS).'|max:'.config('motivations.max_upload_kb'),
            'invoice_total' => 'required|numeric|min:0|max:9999999.99',
        ]);

        $file = $data['file'];
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $path = MotivationStorage::documentPath($motivation->id, DocumentKinds::INVOICE, $ext);
        $disk = MotivationStorage::put($path, file_get_contents($file->getRealPath()));

        // Replace any previous invoice — only the latest is sent to the member.
        $motivation->documents()->where('kind', DocumentKinds::INVOICE)->get()->each->delete();

        $motivation->documents()->create([
            'kind' => DocumentKinds::INVOICE,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType() ?: 'application/pdf',
            'size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        $motivation->update([
            'status' => MotivationRequest::STATUS_INVOICED,
            'invoice_total' => $data['invoice_total'],
            'invoiced_at' => now(),
        ]);

        $motivation->loadMissing(['user.clientProfile', 'applicationType', 'documents']);
        Mail::send(new MotivationUpdate($motivation, MotivationUpdate::STAGE_INVOICED));

        return back()->with('success', 'Invoice sent to the member.');
    }

    public function markPaid(Request $request, MotivationRequest $motivation)
    {
        $data = $request->validate([
            'payment_method' => 'required|string|max:30',
            'payment_reference' => 'nullable|string|max:120',
        ]);

        abort_unless(in_array($motivation->status, [MotivationRequest::STATUS_INVOICED, MotivationRequest::STATUS_PRINTED], true), 422, 'Issue an invoice first.');

        $motivation->update([
            'status' => MotivationRequest::STATUS_PAID,
            'payment_method' => $data['payment_method'],
            'payment_reference' => $data['payment_reference'] ?? null,
            'paid_at' => now(),
        ]);

        $motivation->loadMissing(['user.clientProfile', 'applicationType']);
        Mail::send(new MotivationUpdate($motivation, MotivationUpdate::STAGE_PAID));

        return back()->with('success', 'Payment recorded. The member has been notified.');
    }

    public function markReadyForCollection(Request $request, MotivationRequest $motivation)
    {
        $data = $request->validate(['note' => 'nullable|string|max:500']);

        abort_unless($motivation->status === MotivationRequest::STATUS_PAID, 422, 'Payment must be recorded first.');
        abort_unless($motivation->delivery_method === MotivationRequest::DELIVERY_COLLECT, 422, 'This motivation is set for courier.');

        $motivation->update([
            'status' => MotivationRequest::STATUS_READY_FOR_COLLECTION,
            'ready_at' => now(),
        ]);

        $motivation->loadMissing(['user.clientProfile', 'applicationType']);
        Mail::send(new MotivationUpdate($motivation, MotivationUpdate::STAGE_READY_FOR_COLLECTION, $data['note'] ?? null));

        return back()->with('success', 'Member notified it is ready for collection.');
    }

    public function markShipped(Request $request, MotivationRequest $motivation)
    {
        $data = $request->validate([
            'courier' => 'required|string|max:60',
            'waybill_number' => 'required|string|max:60',
        ]);

        abort_unless($motivation->status === MotivationRequest::STATUS_PAID, 422, 'Payment must be recorded first.');

        $motivation->update([
            'status' => MotivationRequest::STATUS_SHIPPED,
            'delivery_method' => MotivationRequest::DELIVERY_COURIER,
            'courier' => $data['courier'],
            'waybill_number' => $data['waybill_number'],
            'shipped_at' => now(),
        ]);

        $motivation->loadMissing(['user.clientProfile', 'applicationType']);
        Mail::send(new MotivationUpdate($motivation, MotivationUpdate::STAGE_SHIPPED));

        return back()->with('success', 'Marked as shipped. The member has been notified.');
    }

    public function markCompleted(MotivationRequest $motivation)
    {
        abort_unless(in_array($motivation->status, [MotivationRequest::STATUS_READY_FOR_COLLECTION, MotivationRequest::STATUS_SHIPPED], true), 422);

        $motivation->update([
            'status' => MotivationRequest::STATUS_COMPLETED,
            'delivered_at' => now(),
        ]);

        $motivation->loadMissing(['user.clientProfile', 'applicationType']);
        Mail::send(new MotivationUpdate($motivation, MotivationUpdate::STAGE_COMPLETED));

        return back()->with('success', 'Motivation closed out. The member has been thanked.');
    }

    /**
     * Build a read-friendly answer list for the review screen.
     *
     * @return array<int, array{section: string, items: array<int, array{label: string, value: string}>}>
     */
    private function buildAnswersDisplay(MotivationRequest $motivation): array
    {
        $out = [];
        $answers = $motivation->answers ?? [];
        $context = $motivation->conditionContext();

        foreach ($motivation->applicationType->sections() as $section) {
            $items = [];
            foreach ($section['fields'] ?? [] as $field) {
                $visible = QuestionSchema::isVisible(
                    array_merge($field, ['section_condition' => $section['condition'] ?? null]),
                    $context,
                );
                if (! $visible) {
                    continue;
                }
                $items[] = [
                    'label' => $field['label'],
                    'value' => QuestionSchema::display($field, $answers[$field['name']] ?? null),
                ];
            }
            if ($items !== []) {
                $out[] = ['section' => $section['title'], 'items' => $items];
            }
        }

        return $out;
    }
}
