<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Mail\MotivationUpdate;
use App\Models\ApplicationType;
use App\Models\MotivationDocument;
use App\Models\MotivationRequest;
use App\Support\Motivations\DocumentKinds;
use App\Support\Motivations\MotivationStorage;
use App\Support\Motivations\PowerOfAttorneyGenerator;
use App\Support\Motivations\PreviousMotivationData;
use App\Support\Motivations\QuestionSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MotivationRequestController extends Controller
{
    public function index(Request $request)
    {
        return view('account.motivations.index', [
            'profile' => $request->user()->clientProfile,
            'requests' => $request->user()
                ->motivationRequests()
                ->with('applicationType')
                ->latest()
                ->get(),
            'types' => ApplicationType::where('is_active', true)->orderBy('sort')->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->requireProfile($request);

        $hasPrevious = $request->user()->motivationRequests()
            ->whereNotNull('submitted_at')
            ->exists();

        return view('account.motivations.create', [
            'types' => ApplicationType::where('is_active', true)->orderBy('sort')->orderBy('name')->get(),
            'hasPrevious' => $hasPrevious,
        ]);
    }

    public function store(Request $request)
    {
        $this->requireProfile($request);

        $data = $request->validate([
            'application_type_id' => 'required|exists:application_types,id',
            'reuse_previous' => 'nullable|boolean',
        ]);

        $type = ApplicationType::where('is_active', true)->findOrFail($data['application_type_id']);

        $motivation = $request->user()->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => [],
        ]);

        if ($request->boolean('reuse_previous')) {
            $summary = PreviousMotivationData::applyTo($motivation, $request->user()->id);
            if ($summary['answers_reused'] || $summary['firearms_reused'] || $summary['documents_reused'] !== []) {
                return redirect()->route('account.motivations.edit', $motivation)
                    ->with('success', $this->reuseMessage($summary));
            }
        }

        return redirect()->route('account.motivations.edit', $motivation);
    }

    /**
     * @param  array{answers_reused: bool, firearms_reused: bool, documents_reused: array<int, string>, stale_kinds: array<int, string>}  $summary
     */
    private function reuseMessage(array $summary): string
    {
        $parts = [];
        if ($summary['answers_reused']) {
            $parts[] = 'Pre-filled your answers from your previous motivation';
        } elseif ($summary['firearms_reused']) {
            // Firearms carry across every type (SD / hunt / sport). If we
            // didn't carry any same-type answers, still tell the member
            // their firearm particulars are already filled in.
            $parts[] = 'Pre-filled your firearm particulars from a previous motivation';
        }
        if ($summary['answers_reused'] && $summary['firearms_reused']) {
            // "answers reused" already includes firearms_table when it was
            // present in the same-type answers blob — no need to duplicate.
        }
        if ($summary['documents_reused'] !== []) {
            $parts[] = 'carried over '.implode(', ', $summary['documents_reused']);
        }

        $msg = ucfirst(implode(' and ', $parts)).'.';

        if ($summary['stale_kinds'] !== []) {
            $msg .= ' Please re-upload: '.implode(', ', $summary['stale_kinds']).'.';
        }

        return $msg;
    }

    public function edit(Request $request, MotivationRequest $motivation)
    {
        $this->authorizeMember($request, $motivation);
        $motivation->load(['applicationType.blocks', 'documents']);

        return view('account.motivations.edit', [
            'motivation' => $motivation,
            'type' => $motivation->applicationType,
            'requirements' => $motivation->applicableRequirements(),
            'documentKinds' => DocumentKinds::all(),
        ]);
    }

    public function update(Request $request, MotivationRequest $motivation)
    {
        $this->authorizeMember($request, $motivation);
        abort_unless($motivation->isMemberEditable(), 403);

        $sections = $motivation->applicationType->sections();
        $validated = $request->validate(QuestionSchema::rules($sections));

        $motivation->update([
            'answers' => QuestionSchema::normalizeAnswers($sections, $validated['answers'] ?? []),
        ]);

        return back()->with('success', 'Your answers have been saved.');
    }

    public function submit(Request $request, MotivationRequest $motivation)
    {
        $this->authorizeMember($request, $motivation);
        abort_unless($motivation->isMemberEditable(), 403);
        $motivation->loadMissing(['applicationType.blocks', 'documents']);

        $validated = $request->validate([
            'delivery_method' => ['required', \Illuminate\Validation\Rule::in(array_keys(MotivationRequest::DELIVERY_METHODS))],
        ]);

        $missingAnswers = $motivation->missingAnswers();
        $missingDocs = $motivation->missingDocuments();

        if ($missingAnswers !== [] || $missingDocs !== []) {
            return back()->with('error', 'Please complete all required fields and uploads before submitting.')
                ->withErrors([
                    'answers' => $missingAnswers,
                    'documents' => $missingDocs,
                ]);
        }

        $motivation->update([
            'status' => MotivationRequest::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'delivery_method' => $validated['delivery_method'],
        ]);

        $motivation->loadMissing(['user.clientProfile']);
        Mail::send(new MotivationUpdate($motivation, MotivationUpdate::STAGE_SUBMITTED));
        Mail::send(new MotivationUpdate($motivation, MotivationUpdate::STAGE_NEW_SUBMISSION_INTERNAL));

        return redirect()->route('account.motivations.show', $motivation)
            ->with('success', 'Submitted. Our team will review your details and documents.');
    }

    public function show(Request $request, MotivationRequest $motivation)
    {
        $this->authorizeMember($request, $motivation);
        $motivation->load(['applicationType', 'documents']);

        return view('account.motivations.show', [
            'motivation' => $motivation,
        ]);
    }

    public function destroy(Request $request, MotivationRequest $motivation)
    {
        $this->authorizeMember($request, $motivation);
        abort_unless(in_array($motivation->status, [MotivationRequest::STATUS_DRAFT], true), 403);

        DB::transaction(function () use ($motivation) {
            foreach ($motivation->documents as $doc) {
                $doc->delete();
            }
            $motivation->delete();
        });

        return redirect()->route('account.motivations.index')
            ->with('success', 'Draft removed.');
    }

    public function uploadDocument(Request $request, MotivationRequest $motivation)
    {
        $this->authorizeMember($request, $motivation);

        $kind = (string) $request->input('kind');
        $requirements = $motivation->applicableRequirements();
        $isProofOfPayment = $kind === DocumentKinds::PROOF_OF_PAYMENT;

        if ($isProofOfPayment) {
            abort_unless($motivation->status === MotivationRequest::STATUS_INVOICED, 403, 'Proof of payment can only be uploaded once we have invoiced you.');
        } else {
            abort_unless($motivation->isMemberEditable(), 403);
            abort_unless(isset($requirements[$kind]) || $kind === DocumentKinds::POWER_OF_ATTORNEY, 422, 'Document type is not required for this application.');
        }

        $data = $request->validate([
            'file' => 'required|file|mimes:'.implode(',', MotivationDocument::ALLOWED_EXTENSIONS).'|max:'.config('motivations.max_upload_kb'),
            'reference_number' => 'nullable|string|max:120',
            'issued_on' => 'nullable|date',
            'expires_on' => 'nullable|date',
        ]);

        $file = $data['file'];
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $path = MotivationStorage::documentPath($motivation->id, $kind, $extension);
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
        ]);

        if ($isProofOfPayment) {
            $motivation->loadMissing(['user.clientProfile', 'applicationType']);
            Mail::send(new MotivationUpdate($motivation, MotivationUpdate::STAGE_PROOF_OF_PAYMENT_INTERNAL));
        }

        return back()->with('success', 'Document uploaded.');
    }

    public function deleteDocument(Request $request, MotivationRequest $motivation, MotivationDocument $document)
    {
        $this->authorizeMember($request, $motivation);
        abort_unless($motivation->isMemberEditable(), 403);
        abort_unless($document->motivation_request_id === $motivation->id, 404);

        $document->delete();

        return back()->with('success', 'Document removed.');
    }

    public function viewDocument(Request $request, MotivationRequest $motivation, MotivationDocument $document)
    {
        $this->authorizeMember($request, $motivation);
        abort_unless($document->motivation_request_id === $motivation->id, 404);

        return MotivationStorage::response(
            $document->disk,
            $document->path,
            $document->original_name ?: basename($document->path),
        );
    }

    public function downloadPoa(Request $request, MotivationRequest $motivation)
    {
        $this->authorizeMember($request, $motivation);

        return PowerOfAttorneyGenerator::stream($motivation);
    }

    public function downloadReference(Request $request, MotivationRequest $motivation)
    {
        $this->authorizeMember($request, $motivation);

        return PowerOfAttorneyGenerator::reference($motivation);
    }

    private function authorizeMember(Request $request, MotivationRequest $motivation): void
    {
        abort_unless($motivation->user_id === $request->user()->id, 404);
    }

    private function requireProfile(Request $request): void
    {
        $profile = $request->user()->clientProfile;
        if (! $profile || $profile->missingRequired() !== []) {
            abort(redirect()->route('account.profile.edit')
                ->with('info', 'Please complete your profile first — it is used on every motivation.'));
        }
    }
}
