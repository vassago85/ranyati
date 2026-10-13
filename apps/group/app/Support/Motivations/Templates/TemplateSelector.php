<?php

namespace App\Support\Motivations\Templates;

use App\Models\ApplicationType;
use App\Models\MotivationRequest;
use App\Models\MotivationTemplateVersion;
use App\Support\Motivations\ConditionEvaluator;

/**
 * Picks the published MotivationTemplateVersion to use for a request.
 *
 * Resolution order:
 *   1. The first pivot row whose condition passes against the request's
 *      answers (ordered by `sort`, then id).
 *   2. The pivot row flagged as `is_default`.
 *   3. The first pivot row with no condition.
 *
 * In every case the service picks the template's **published** version. If
 * the picked template has no published version yet, it is skipped so the
 * caller falls through to the next candidate.
 */
class TemplateSelector
{
    /**
     * Pick a template version for the given request, or null when nothing matches.
     */
    public static function for(MotivationRequest $request): ?MotivationTemplateVersion
    {
        $type = $request->applicationType;
        if (! $type instanceof ApplicationType) {
            return null;
        }

        $context = RenderContext::for($request);

        $pivots = $type->templates()
            ->with('publishedVersion')
            ->where('motivation_templates.status', '!=', 'archived')
            ->get();

        if ($pivots->isEmpty()) {
            return null;
        }

        // 1. Conditional matches (non-default rows whose condition passes).
        foreach ($pivots as $template) {
            $pivot = $template->pivot;
            $condition = self::condition($pivot?->condition);
            if ($condition === null) {
                continue;
            }
            if (ConditionEvaluator::passes($condition, $context) && $template->publishedVersion) {
                return $template->publishedVersion;
            }
        }

        // 2. The default row.
        $default = $pivots->first(fn ($t) => (bool) ($t->pivot->is_default ?? false));
        if ($default && $default->publishedVersion) {
            return $default->publishedVersion;
        }

        // 3. First unconditional, otherwise the very first assigned template.
        $plain = $pivots->first(fn ($t) => self::condition($t->pivot?->condition) === null);
        if ($plain && $plain->publishedVersion) {
            return $plain->publishedVersion;
        }

        $first = $pivots->first();

        return $first?->publishedVersion;
    }

    /**
     * Normalise a stored pivot condition into an array (or null if empty).
     *
     * @return array<string, mixed>|null
     */
    private static function condition(mixed $raw): ?array
    {
        if (is_array($raw) && $raw !== []) {
            return $raw;
        }
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && $decoded !== []) {
                return $decoded;
            }
        }

        return null;
    }
}
