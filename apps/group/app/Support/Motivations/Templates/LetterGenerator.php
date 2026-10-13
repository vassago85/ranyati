<?php

namespace App\Support\Motivations\Templates;

use App\Models\MotivationLetter;
use App\Models\MotivationRequest;
use App\Models\MotivationTemplateVersion;
use App\Support\ActivityLog;
use App\Support\Motivations\MotivationStorage;
use App\Support\Motivations\QuestionSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Produces per-firearm (or combined) firearm motivations for a request from
 * the template version chosen by `TemplateSelector`.
 *
 * The result is a set of `motivation_letters` rows (status = draft) with the
 * rendered PDF stored on the member document disk. Existing drafts for the
 * same request are replaced; final motivations are never touched.
 */
class LetterGenerator
{
    public function __construct(
        private readonly ?int $actorId = null,
    ) {}

    /**
     * Generate motivations for the request and return the fresh collection.
     * The return key stays `letters` so the DB/model vocabulary doesn't leak
     * into the renamed admin UI copy.
     *
     * @return array{letters: array<int, MotivationLetter>, template_version: ?MotivationTemplateVersion, missing: array<int, string>, warnings: array<int, string>}
     */
    public function generate(MotivationRequest $request): array
    {
        $request->loadMissing(['user.clientProfile', 'applicationType.blocks', 'documents']);

        $version = TemplateSelector::for($request);
        if (! $version) {
            return [
                'letters' => [],
                'template_version' => null,
                'missing' => [],
                'warnings' => ['No published template is attached to this application type — nothing to generate.'],
            ];
        }

        $settings = (array) ($version->settings ?? MotivationTemplateVersion::defaultSettings());
        $mode = (string) ($settings['letter_mode'] ?? 'per_firearm');

        return DB::transaction(function () use ($request, $version, $settings, $mode) {
            // Scrap previous drafts; keep anything already finalised.
            $request->letters()->where('status', MotivationLetter::STATUS_DRAFT)->get()->each->delete();

            $missing = [];
            $warnings = [];
            $letters = [];

            if ($mode === 'combined' || ! $request->applicationType) {
                $letters[] = $this->renderOne($request, $version, $settings, null, null, $missing, $warnings);
            } else {
                $firearms = QuestionSchema::firearms(
                    $request->applicationType->sections(),
                    $request->answers ?? [],
                );
                if ($firearms === []) {
                    // No firearms rows → render a single combined motivation.
                    $letters[] = $this->renderOne($request, $version, $settings, null, null, $missing, $warnings);
                } else {
                    // QuestionSchema::firearms() keys rows by 1..N, so $i is
                    // already the 1-based firearm index.
                    foreach ($firearms as $i => $row) {
                        $letters[] = $this->renderOne($request, $version, $settings, (int) $i, $row, $missing, $warnings);
                    }
                }
            }

            ActivityLog::record(
                ActivityLog::ACTION_LETTER_GENERATED,
                $request,
                [
                    'template_version_id' => $version->id,
                    'template_id' => $version->template_id,
                    'letter_count' => count($letters),
                    'missing' => array_values(array_unique($missing)),
                ],
                $this->actorId,
            );

            // Also pin the version onto the request for audit / fallback.
            $request->update(['template_version_id' => $version->id]);

            return [
                'letters' => $letters,
                'template_version' => $version,
                'missing' => array_values(array_unique($missing)),
                'warnings' => array_values(array_unique($warnings)),
            ];
        });
    }

    /**
     * @param  array<int|string, string>|null  $firearmRow
     */
    private function renderOne(
        MotivationRequest $request,
        MotivationTemplateVersion $version,
        array $settings,
        ?int $firearmIndex,
        ?array $firearmRow,
        array &$missing,
        array &$warnings,
    ): MotivationLetter {
        $letter = MotivationLetter::create([
            'motivation_request_id' => $request->id,
            'template_version_id' => $version->id,
            'firearm_index' => $firearmIndex,
            'firearm_serial' => (string) ($firearmRow['serial'] ?? '') ?: null,
            'snapshot' => [
                'profile' => $request->user->clientProfile?->toPlaceholderArray() ?? [],
                'answers' => $request->answers ?? [],
                'firearm' => $firearmRow,
                'generated_at' => now()->toIso8601String(),
            ],
            'status' => MotivationLetter::STATUS_DRAFT,
            'generation_status' => MotivationLetter::GEN_RUNNING,
            'generated_by' => $this->actorId,
        ]);

        try {
            $context = RenderContext::for(
                $request,
                $firearmRow !== null && $firearmIndex !== null
                    ? RenderContext::firearm($firearmIndex, $firearmRow)
                    : null,
            );

            $composer = new LetterComposer($request, $context);
            $result = $composer->compose((array) $version->blocks);
            $bytes = LetterPdf::render($result['segments'], $settings, $context);

            $path = sprintf('motivations/%d/letter-%d-%s.pdf', $request->id, $letter->id, Str::uuid());
            $disk = MotivationStorage::put($path, $bytes);

            $letter->update([
                'pdf_disk' => $disk,
                'pdf_path' => $path,
                'missing' => $result['missing'],
                'warnings' => $result['warnings'],
                'generation_status' => MotivationLetter::GEN_DONE,
                'generated_at' => now(),
            ]);

            foreach ($result['missing'] as $m) {
                $missing[] = $m;
            }
            foreach ($result['warnings'] as $w) {
                $warnings[] = $w;
            }
        } catch (Throwable $e) {
            report($e);
            $letter->update([
                'generation_status' => MotivationLetter::GEN_FAILED,
                'error' => Str::limit($e->getMessage(), 500),
            ]);
            $warnings[] = $letter->label().': '.$e->getMessage();
        }

        return $letter->fresh();
    }
}
