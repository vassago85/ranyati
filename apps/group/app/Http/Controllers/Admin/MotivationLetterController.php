<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MotivationLetter;
use App\Models\MotivationRequest;
use App\Support\ActivityLog;
use App\Support\Motivations\MotivationStorage;
use App\Support\Motivations\Templates\LetterComposer;
use App\Support\Motivations\Templates\LetterGenerator;
use App\Support\Motivations\Templates\LetterPdf;
use App\Support\Motivations\Templates\RenderContext;
use App\Support\Motivations\Templates\RichText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * Admin actions on an individual firearm motivation: open (inline), download
 * (attachment), regenerate (replace draft with fresh render), edit text
 * (overrides inside the snapshot), finalise (lock + stamp).
 */
class MotivationLetterController extends Controller
{
    /**
     * Kick off a generation run for the whole request.
     */
    public function generate(Request $request, MotivationRequest $motivation)
    {
        Gate::authorize('update', MotivationLetter::class);
        $motivation->loadMissing('applicationType');

        $result = (new LetterGenerator($request->user()->id))->generate($motivation);

        $flash = count($result['letters']).' motivation(s) generated.';
        if ($result['missing'] !== []) {
            $flash .= ' Missing placeholders: '.implode(', ', $result['missing']).'.';
        }
        if ($result['warnings'] !== []) {
            return back()->with('warning', $flash.' '.implode(' ', $result['warnings']));
        }

        return back()->with('success', $flash);
    }

    /**
     * Short JSON response the request page polls while generation is in
     * flight. Also handy for inline refreshes.
     */
    public function status(MotivationRequest $motivation)
    {
        Gate::authorize('view', MotivationLetter::class);
        $letters = $motivation->letters()->orderBy('firearm_index')->get();

        return response()->json([
            'letters' => $letters->map(fn ($l) => $this->asJson($l))->values(),
            'any_running' => $letters->contains(fn ($l) => in_array($l->generation_status, [MotivationLetter::GEN_QUEUED, MotivationLetter::GEN_RUNNING], true)),
        ]);
    }

    public function open(MotivationRequest $motivation, MotivationLetter $letter)
    {
        $this->guardLetterBelongsTo($motivation, $letter);
        Gate::authorize('view', MotivationLetter::class);
        abort_unless($letter->pdf_path, 404);

        return MotivationStorage::response(
            $letter->pdf_disk ?: MotivationStorage::defaultDisk(),
            $letter->pdf_path,
            $this->filenameFor($motivation, $letter),
            inline: true,
        );
    }

    public function download(MotivationRequest $motivation, MotivationLetter $letter)
    {
        $this->guardLetterBelongsTo($motivation, $letter);
        Gate::authorize('view', MotivationLetter::class);
        abort_unless($letter->pdf_path, 404);

        return MotivationStorage::response(
            $letter->pdf_disk ?: MotivationStorage::defaultDisk(),
            $letter->pdf_path,
            $this->filenameFor($motivation, $letter),
            inline: false,
        );
    }

    /**
     * Replace the stored PDF for a single draft letter from the current
     * template version (or the pinned one on the request).
     */
    public function regenerate(Request $request, MotivationRequest $motivation, MotivationLetter $letter)
    {
        $this->guardLetterBelongsTo($motivation, $letter);
        Gate::authorize('update', MotivationLetter::class);
        abort_if($letter->isFinal(), 403, 'Final motivations cannot be regenerated.');

        // Re-run the whole generation — simpler, and keeps every motivation
        // aligned with the current firearm count / template version.
        $result = (new LetterGenerator($request->user()->id))->generate($motivation);

        ActivityLog::record(ActivityLog::ACTION_LETTER_REGENERATED, $motivation, [
            'letter_count' => count($result['letters']),
        ], $request->user()->id);

        return back()->with('success', 'Motivation regenerated.');
    }

    /**
     * Save per-letter block overrides. The snapshot is edited in place; the
     * PDF is re-rendered from the override.
     */
    public function updateText(Request $request, MotivationRequest $motivation, MotivationLetter $letter)
    {
        $this->guardLetterBelongsTo($motivation, $letter);
        Gate::authorize('update', MotivationLetter::class);
        abort_if($letter->isFinal(), 403, 'Final motivations cannot be edited.');

        $data = $request->validate([
            'block_overrides' => 'nullable|array',
            'block_overrides.*' => 'nullable|string',
        ]);

        $overrides = [];
        foreach ($data['block_overrides'] ?? [] as $blockId => $html) {
            $overrides[(string) $blockId] = RichText::sanitize((string) $html);
        }

        $snapshot = (array) $letter->snapshot;
        $snapshot['overrides'] = $overrides;
        $letter->update(['snapshot' => $snapshot]);

        $this->rerenderOne($letter);

        ActivityLog::record(ActivityLog::ACTION_LETTER_EDITED, $motivation, [
            'letter_id' => $letter->id,
            'override_count' => count($overrides),
        ], $request->user()->id);

        return back()->with('success', 'Motivation text updated.');
    }

    /**
     * Lock the motivation — once final we don't touch the stored PDF.
     */
    public function finalise(Request $request, MotivationRequest $motivation, MotivationLetter $letter)
    {
        $this->guardLetterBelongsTo($motivation, $letter);
        Gate::authorize('update', MotivationLetter::class);
        abort_unless($letter->isDraft(), 403, 'Already final.');
        abort_unless($letter->pdf_path, 403, 'Motivation has no PDF yet; regenerate first.');

        $letter->update([
            'status' => MotivationLetter::STATUS_FINAL,
            'finalised_by' => $request->user()->id,
            'finalised_at' => now(),
        ]);

        ActivityLog::record(ActivityLog::ACTION_LETTER_FINALISED, $motivation, [
            'letter_id' => $letter->id,
            'label' => $letter->label(),
        ], $request->user()->id);

        return back()->with('success', $letter->label().' finalised.');
    }

    public function destroy(Request $request, MotivationRequest $motivation, MotivationLetter $letter)
    {
        $this->guardLetterBelongsTo($motivation, $letter);
        Gate::authorize('update', MotivationLetter::class);
        abort_if($letter->isFinal(), 403, 'Final motivations cannot be deleted.');

        $letter->delete();

        return back()->with('success', 'Motivation removed.');
    }

    /**
     * Re-render just one motivation from its own snapshot. Used after text edits.
     */
    private function rerenderOne(MotivationLetter $letter): void
    {
        $motivation = $letter->motivationRequest()->with(['user.clientProfile', 'applicationType.blocks', 'documents'])->firstOrFail();
        $version = $letter->templateVersion()->first();
        if (! $version) {
            return;
        }

        $snapshot = (array) $letter->snapshot;
        $firearmRow = $snapshot['firearm'] ?? null;
        $firearmIndex = $letter->firearm_index;

        $context = RenderContext::for(
            $motivation,
            $firearmRow !== null && $firearmIndex !== null
                ? RenderContext::firearm($firearmIndex, (array) $firearmRow)
                : null,
        );

        // Apply overrides into the version blocks before composing.
        $blocks = (array) $version->blocks;
        $overrides = (array) ($snapshot['overrides'] ?? []);
        if ($overrides !== []) {
            foreach ($blocks as &$block) {
                $id = (string) ($block['id'] ?? '');
                if ($id !== '' && array_key_exists($id, $overrides)) {
                    // Replace the block kind with a rich_text so the user's
                    // free-form edit renders verbatim.
                    $block = [
                        'id' => $id,
                        'kind' => 'rich_text',
                        'settings' => ['html' => $overrides[$id]],
                        'condition' => $block['condition'] ?? null,
                    ];
                }
            }
            unset($block);
        }

        $composer = new LetterComposer($motivation, $context);
        $result = $composer->compose($blocks);
        $bytes = LetterPdf::render($result['segments'], (array) $version->settings, $context);

        // Replace the stored PDF.
        if ($letter->pdf_path) {
            MotivationStorage::delete($letter->pdf_disk ?: MotivationStorage::defaultDisk(), $letter->pdf_path);
        }
        $path = sprintf('motivations/%d/letter-%d-%s.pdf', $motivation->id, $letter->id, Str::uuid());
        $disk = MotivationStorage::put($path, $bytes);

        $letter->update([
            'pdf_disk' => $disk,
            'pdf_path' => $path,
            'missing' => $result['missing'],
            'warnings' => $result['warnings'],
            'generation_status' => MotivationLetter::GEN_DONE,
            'generated_at' => now(),
        ]);
    }

    private function guardLetterBelongsTo(MotivationRequest $motivation, MotivationLetter $letter): void
    {
        abort_unless($letter->motivation_request_id === $motivation->id, 404);
    }

    private function filenameFor(MotivationRequest $motivation, MotivationLetter $letter): string
    {
        $suffix = $letter->isCombined() ? 'combined' : 'firearm-'.$letter->firearm_index;

        return 'motivation-'.$motivation->reference().'-'.$suffix.'.pdf';
    }

    private function asJson(MotivationLetter $letter): array
    {
        return [
            'id' => $letter->id,
            'label' => $letter->label(),
            'firearm_index' => $letter->firearm_index,
            'firearm_serial' => $letter->firearm_serial,
            'status' => $letter->status,
            'status_label' => $letter->statusLabel(),
            'generation_status' => $letter->generation_status,
            'generated_at' => $letter->generated_at?->toIso8601String(),
            'finalised_at' => $letter->finalised_at?->toIso8601String(),
            'missing' => $letter->missing ?: [],
            'warnings' => $letter->warnings ?: [],
            'has_pdf' => $letter->pdf_path !== null,
        ];
    }
}
