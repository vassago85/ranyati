<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MotivationRequest;
use App\Models\MotivationTemplate;
use App\Support\Motivations\Placeholders;
use App\Support\Motivations\QuestionSchema;
use App\Support\Motivations\Templates\LetterComposer;
use App\Support\Motivations\Templates\LetterPdf;
use App\Support\Motivations\Templates\PlaceholderCatalogue;
use App\Support\Motivations\Templates\PreviewSampleData;
use App\Support\Motivations\Templates\RenderContext;
use App\Support\Motivations\Templates\TemplateSchema;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The builder previews a template without saving by posting the live blocks
 * + settings, picking either a sample dataset or a real motivation request.
 *
 * The POST renders the PDF, writes the bytes to the local disk keyed by a
 * one-off token and returns the token + preview metadata. The GET then
 * streams that file into an iframe (works inside `frame-src 'self'`).
 *
 * The bytes stay off the cache table on purpose. Production uses the
 * database cache on MySQL, and Laravel only base64-encodes serialized
 * values that contain null bytes for SQLite and Postgres. A PDF is
 * binary, so the MySQL insert fails with "Incorrect string value" and
 * the builder surfaces Laravel's generic "Server Error" JSON message.
 */
class TemplatePreviewController extends Controller
{
    public function store(Request $request, MotivationTemplate $motivationTemplate)
    {
        Gate::authorize('view', $motivationTemplate);

        $data = $request->validate([
            'blocks' => 'required|array',
            'settings' => 'nullable|array',
            'request_id' => 'nullable|integer|exists:motivation_requests,id',
            'firearm_index' => 'nullable|integer|min:1',
        ]);

        $blocks = TemplateSchema::normalizeBlocks($data['blocks']);
        $settings = TemplateSchema::normalizeSettingsJson($data['settings'] ?? []);

        $motivationRequest = isset($data['request_id'])
            ? MotivationRequest::with(['user.clientProfile', 'applicationType.blocks', 'documents'])
                ->findOrFail($data['request_id'])
            : PreviewSampleData::requestFor($motivationTemplate);

        // If a per-firearm preview was asked for, point the context at
        // that row; otherwise the composer will see the first.
        $firearmRow = null;
        if (! empty($data['firearm_index']) && $motivationRequest->applicationType) {
            $firearms = QuestionSchema::firearms(
                $motivationRequest->applicationType->sections(),
                $motivationRequest->answers ?? [],
            );
            $firearmRow = $firearms[$data['firearm_index']] ?? null;
        }

        $context = RenderContext::for(
            $motivationRequest,
            $firearmRow ? RenderContext::firearm((int) $data['firearm_index'], $firearmRow) : null,
        );

        $composer = new LetterComposer($motivationRequest, $context);
        $result = $composer->compose($blocks);

        $bytes = LetterPdf::render($result['segments'], $settings, $context);
        $token = (string) Str::uuid();
        $this->storePreview($token, $bytes);

        return response()->json([
            'token' => $token,
            'missing' => $result['missing'],
            'warnings' => $result['warnings'],
            'preview_text' => LetterPdf::previewText($result['segments']),
            'url' => route('admin.motivation-templates.preview.show', ['motivationTemplate' => $motivationTemplate, 'token' => $token]),
        ]);
    }

    /**
     * Live canvas preview data: placeholder values resolved against the
     * chosen sample record, per-block HTML for the special "card" kinds
     * and whether each block's condition passes.
     *
     * The admin builder calls this on every settings change for a card
     * (debounced) and whenever the sample record is swapped. It is a
     * JSON endpoint — no PDF rendering, no disk writes.
     */
    public function canvas(Request $request, MotivationTemplate $motivationTemplate)
    {
        Gate::authorize('view', $motivationTemplate);

        $data = $request->validate([
            'blocks' => 'nullable|array',
            'request_id' => 'nullable|integer|exists:motivation_requests,id',
            'firearm_index' => 'nullable|integer|min:1',
        ]);

        $blocks = TemplateSchema::normalizeBlocks($data['blocks'] ?? []);

        $motivationRequest = isset($data['request_id'])
            ? MotivationRequest::with(['user.clientProfile', 'applicationType.blocks', 'documents'])
                ->findOrFail($data['request_id'])
            : PreviewSampleData::requestFor($motivationTemplate);

        $firearmRow = null;
        if (! empty($data['firearm_index']) && $motivationRequest->applicationType) {
            $firearms = QuestionSchema::firearms(
                $motivationRequest->applicationType->sections(),
                $motivationRequest->answers ?? [],
            );
            $firearmRow = $firearms[$data['firearm_index']] ?? null;
        }

        $context = RenderContext::for(
            $motivationRequest,
            $firearmRow ? RenderContext::firearm((int) $data['firearm_index'], $firearmRow) : null,
        );

        $composer = new LetterComposer($motivationRequest, $context);

        // Resolve every placeholder catalogue path once, so the Alpine
        // front-end can swap chips -> sample values without a round-trip
        // per chip.
        $values = [];
        foreach (PlaceholderCatalogue::forTemplate($motivationTemplate) as $group) {
            foreach ($group as $path => $_label) {
                $values[$path] = Placeholders::format(data_get($context, $path));
            }
        }

        // Per-block HTML only for the "card" kinds — the plain text,
        // heading, section and rich_text blocks render inline in the
        // canvas via TipTap, so we don't need server HTML for them.
        $cardKinds = [
            'yes_no_sentences', 'crime_context', 'table',
            'document_insert', 'dynamic_image', 'signature', 'title_page',
        ];
        $cards = [];
        $passes = [];
        foreach ($blocks as $block) {
            $passes[$block['id']] = \App\Support\Motivations\ConditionEvaluator::passes($block['condition'] ?? null, $context);
            if (in_array($block['kind'], $cardKinds, true)) {
                $cards[$block['id']] = $composer->previewBlock($block);
            }
        }

        return response()->json([
            'values' => $values,
            'cards' => $cards,
            'passes' => $passes,
        ]);
    }

    public function show(Request $request, MotivationTemplate $motivationTemplate, string $token)
    {
        Gate::authorize('view', $motivationTemplate);

        abort_unless(Str::isUuid($token), 404);

        $path = self::previewPath($token);
        $disk = Storage::disk('local');
        // Previews are short-lived. Drop the file once it is older than
        // the window the builder is allowed to re-fetch it in.
        if (! $disk->exists($path) || $disk->lastModified($path) < now()->subMinutes(10)->getTimestamp()) {
            $disk->delete($path);
            abort(404, 'Preview expired — generate a new one.');
        }

        $bytes = $disk->get($path);
        abort_if(! is_string($bytes) || $bytes === '', 404, 'Preview expired — generate a new one.');

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview-'.$motivationTemplate->slug.'.pdf"',
            'Cache-Control' => 'no-store',
        ]);
    }

    private function storePreview(string $token, string $bytes): void
    {
        $disk = Storage::disk('local');
        $disk->put(self::previewPath($token), $bytes);
        $this->purgeExpiredPreviews($disk);
    }

    private static function previewPath(string $token): string
    {
        return 'template-previews/'.$token.'.pdf';
    }

    private function purgeExpiredPreviews(Filesystem $disk): void
    {
        $cutoff = now()->subMinutes(15)->getTimestamp();
        foreach ($disk->files('template-previews') as $file) {
            if ($disk->lastModified($file) < $cutoff) {
                $disk->delete($file);
            }
        }
    }
}
