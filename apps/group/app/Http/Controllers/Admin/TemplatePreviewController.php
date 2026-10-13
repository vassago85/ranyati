<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MotivationRequest;
use App\Models\MotivationTemplate;
use App\Support\Motivations\QuestionSchema;
use App\Support\Motivations\Templates\LetterComposer;
use App\Support\Motivations\Templates\LetterPdf;
use App\Support\Motivations\Templates\PreviewSampleData;
use App\Support\Motivations\Templates\RenderContext;
use App\Support\Motivations\Templates\TemplateSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * The builder previews a template without saving by posting the live blocks
 * + settings, picking either a sample dataset or a real motivation request.
 *
 * The POST renders the PDF, caches the bytes keyed by a one-off token and
 * returns the token + preview metadata. The GET then streams the cached
 * PDF into an iframe (works inside `frame-src 'self'`).
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
        Cache::put('template-preview:'.$token, $bytes, now()->addMinutes(10));

        return response()->json([
            'token' => $token,
            'missing' => $result['missing'],
            'warnings' => $result['warnings'],
            'preview_text' => LetterPdf::previewText($result['segments']),
            'url' => route('admin.motivation-templates.preview.show', ['motivationTemplate' => $motivationTemplate, 'token' => $token]),
        ]);
    }

    public function show(Request $request, MotivationTemplate $motivationTemplate, string $token)
    {
        Gate::authorize('view', $motivationTemplate);

        // Use get() rather than pull() so the iframe can safely re-fetch
        // the same preview without hitting a 404 on reload.
        $bytes = Cache::get('template-preview:'.$token);
        abort_if($bytes === null, 404, 'Preview expired — generate a new one.');

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview-'.$motivationTemplate->slug.'.pdf"',
            'Cache-Control' => 'no-store',
        ]);
    }
}
