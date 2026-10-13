<?php

namespace App\Support\Motivations;

use App\Models\MotivationRequest;
use App\Models\MotivationTemplateVersion;
use App\Support\Motivations\Templates\LegacyBlockConverter;
use App\Support\Motivations\Templates\LetterComposer;
use App\Support\Motivations\Templates\LetterPdf;
use App\Support\Motivations\Templates\RenderContext;

/**
 * Assembles the motivation book for a request.
 *
 * Historically this class walked the application type's `blocks` directly
 * and rendered them through its own DomPDF pipeline. It is now a thin
 * wrapper over the template engine:
 *
 *   • If the request has a `template_version_id`, the composer renders
 *     that frozen version.
 *   • Otherwise the request's `blocks_snapshot` (or live block defs) are
 *     converted on the fly with `LegacyBlockConverter` and fed through
 *     the same engine. This path keeps older requests and the existing
 *     test suite working while the template rollout is in flight.
 */
class BookRenderer
{
    /**
     * Render the book. Returns the raw PDF bytes plus any placeholder paths
     * that resolved to blank so the admin can see what's still missing.
     *
     * @return array{bytes: string, missing: array<int, string>}
     */
    public static function render(MotivationRequest $request): array
    {
        $pipeline = self::compose($request);

        return [
            'bytes' => LetterPdf::render($pipeline['segments'], $pipeline['settings'], $pipeline['context']),
            'missing' => $pipeline['missing'],
        ];
    }

    /**
     * Plain-text summary of the book — used by tests and by the admin
     * preview panel. Mirrors the structure the final PDF will have.
     *
     * @return array{text: string, missing: array<int, string>}
     */
    public static function preview(MotivationRequest $request): array
    {
        $pipeline = self::compose($request);

        return [
            'text' => LetterPdf::previewText($pipeline['segments']),
            'missing' => $pipeline['missing'],
        ];
    }

    /**
     * @return array{segments: array<int, array<string, mixed>>, settings: array<string, mixed>, context: array<string, mixed>, missing: array<int, string>, warnings: array<int, string>}
     */
    private static function compose(MotivationRequest $request): array
    {
        $request->loadMissing(['applicationType.blocks', 'documents', 'user.clientProfile', 'templateVersion']);

        if ($request->template_version_id && $request->templateVersion) {
            $blocks = (array) ($request->templateVersion->blocks ?? []);
            $settings = (array) ($request->templateVersion->settings ?? MotivationTemplateVersion::defaultSettings());
        } else {
            $legacy = $request->blocks_snapshot ?: $request->applicationType->blockDefinitions();
            $blocks = (new LegacyBlockConverter)->convertBlocks($legacy);
            $settings = MotivationTemplateVersion::defaultSettings();
        }

        $context = RenderContext::for($request);
        $composer = new LetterComposer($request, $context);
        $result = $composer->compose($blocks);

        return [
            'segments' => $result['segments'],
            'settings' => $settings,
            'context' => $context,
            'missing' => $result['missing'],
            'warnings' => $result['warnings'],
        ];
    }
}
