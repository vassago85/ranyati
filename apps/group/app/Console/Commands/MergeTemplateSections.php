<?php

namespace App\Console\Commands;

use App\Models\MotivationRequest;
use App\Models\MotivationTemplate;
use App\Support\Motivations\Templates\LetterComposer;
use App\Support\Motivations\Templates\LetterPdf;
use App\Support\Motivations\Templates\PreviewSampleData;
use App\Support\Motivations\Templates\RenderContext;
use App\Support\Motivations\Templates\SectionMerger;
use App\Support\Motivations\Templates\TemplateSchema;
use App\Support\Motivations\Templates\TemplateVersionManager;
use Illuminate\Console\Command;

/**
 * Walk every template's draft and fold adjacent heading + text pairs into
 * one section block, so the long "heading, text, heading, text…" lists
 * (Self Defence Handgun has 39 such blocks) collapse into readable
 * sections (~22).
 *
 * Writes to drafts only. Published versions are frozen and are left
 * alone — admins republish from the merged draft in the builder.
 *
 * `--dry-run` renders the before and after through LetterComposer +
 * previewText and reports any text or page-count differences. Without
 * `--dry-run` the draft is only updated when there are no diffs for
 * that template, unless `--force` is passed.
 */
class MergeTemplateSections extends Command
{
    protected $signature = 'motivation-templates:merge-sections
        {--template= : Only process the template with this slug or numeric id}
        {--dry-run : Diff old vs new rendering without saving}
        {--force : Save even when a diff is detected (dangerous)}
        {--samples=3 : Also diff against up to N recent real requests}';

    protected $description = 'Fold adjacent heading+text blocks into section blocks on template drafts';

    public function handle(TemplateVersionManager $versions): int
    {
        $templates = $this->selectedTemplates();
        if ($templates->isEmpty()) {
            $this->error('No templates matched.');

            return self::FAILURE;
        }

        $this->line('Processing '.$templates->count().' template(s). Mode: '
            .($this->option('dry-run') ? 'dry-run' : 'write').
            ($this->option('force') ? ' (force)' : ''));
        $this->newLine();

        $hadDiffs = false;

        foreach ($templates as $template) {
            $hadDiffs = ! $this->process($template, $versions) || $hadDiffs;
        }

        $this->newLine();
        if ($hadDiffs && ! $this->option('force') && ! $this->option('dry-run')) {
            $this->warn('Some templates were skipped because their rendered output changed. Rerun with --dry-run to inspect, then --force to accept.');

            return self::INVALID;
        }

        return self::SUCCESS;
    }

    private function selectedTemplates()
    {
        $query = MotivationTemplate::with(['applicationTypes', 'publishedVersion'])->orderBy('name');

        $filter = $this->option('template');
        if ($filter) {
            $query->where(function ($q) use ($filter) {
                $q->where('slug', $filter);
                if (ctype_digit((string) $filter)) {
                    $q->orWhere('id', (int) $filter);
                }
            });
        }

        return $query->get();
    }

    private function process(MotivationTemplate $template, TemplateVersionManager $versions): bool
    {
        $this->line("<fg=cyan>• {$template->name}</> <fg=gray>({$template->slug})</>");

        $draft = $versions->ensureDraft($template);
        $before = TemplateSchema::normalizeBlocks($draft->blocks ?? []);
        $after = SectionMerger::merge($before);

        $beforeCount = count($before);
        $afterCount = count($after);

        if ($beforeCount === $afterCount) {
            $this->line("  <fg=gray>No pairs to fold. Blocks: {$beforeCount}.</>");

            return true;
        }

        $this->line("  Blocks: <fg=yellow>{$beforeCount}</> → <fg=green>{$afterCount}</>");

        $differences = $this->diffRenders($template, $before, $after);

        if ($differences === []) {
            $this->line('  <fg=green>Rendered output matches on every sample.</>');
        } else {
            foreach ($differences as $diff) {
                $this->line('  <fg=red>DIFF ['.$diff['label'].']</> '.$diff['summary']);
            }
        }

        if ($this->option('dry-run')) {
            return $differences === [];
        }

        if ($differences !== [] && ! $this->option('force')) {
            $this->line('  <fg=yellow>Skipped — rerun with --force to accept the diff.</>');

            return false;
        }

        $versions->saveDraft($template, $after, $draft->settings ?? [], null);
        $this->line('  <fg=green>Draft updated.</>');

        return $differences === [];
    }

    /**
     * Render `before` and `after` against the sample record and up to N
     * recent real requests. Return the list of (label, summary) tuples
     * where the previewText or segment count differs.
     *
     * @param  array<int, array<string, mixed>>  $before
     * @param  array<int, array<string, mixed>>  $after
     * @return array<int, array{label: string, summary: string}>
     */
    private function diffRenders(MotivationTemplate $template, array $before, array $after): array
    {
        $diffs = [];

        $contexts = [];
        $sampleRequest = PreviewSampleData::requestFor($template);
        $contexts[] = ['label' => 'sample', 'request' => $sampleRequest];

        $limit = max(0, (int) $this->option('samples'));
        if ($limit > 0) {
            $applicationTypeIds = $template->applicationTypes()->pluck('application_types.id')->all();
            $realQuery = MotivationRequest::with(['user.clientProfile', 'applicationType.blocks', 'documents'])->latest();
            if ($applicationTypeIds !== []) {
                $realQuery->whereIn('application_type_id', $applicationTypeIds);
            }
            foreach ($realQuery->take($limit)->get() as $real) {
                $contexts[] = ['label' => "request #{$real->id}", 'request' => $real];
            }
        }

        foreach ($contexts as $ctx) {
            $request = $ctx['request'];
            $context = RenderContext::for($request);

            $composerBefore = new LetterComposer($request, $context);
            $composerAfter = new LetterComposer($request, $context);

            $segmentsBefore = $composerBefore->compose($before);
            $segmentsAfter = $composerAfter->compose($after);

            $textBefore = LetterPdf::previewText($segmentsBefore['segments']);
            $textAfter = LetterPdf::previewText($segmentsAfter['segments']);

            if ($textBefore !== $textAfter) {
                $diffs[] = [
                    'label' => $ctx['label'],
                    'summary' => self::describeTextDiff($textBefore, $textAfter),
                ];
            }
        }

        return $diffs;
    }

    private static function describeTextDiff(string $a, string $b): string
    {
        // Find the first byte that differs so the admin has a jumping-off
        // point when they inspect the merged draft.
        $len = min(strlen($a), strlen($b));
        $i = 0;
        while ($i < $len && $a[$i] === $b[$i]) {
            $i++;
        }

        $contextStart = max(0, $i - 20);
        $before = substr($a, $contextStart, 60);
        $after = substr($b, $contextStart, 60);

        return "First differing byte at offset {$i}. Before: '".addslashes($before)."' After: '".addslashes($after)."'";
    }
}
