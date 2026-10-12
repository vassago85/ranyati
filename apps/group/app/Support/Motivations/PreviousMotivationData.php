<?php

namespace App\Support\Motivations;

use App\Models\ApplicationType;
use App\Models\MotivationDocument;
use App\Models\MotivationRequest;
use App\Models\User;

/**
 * Returning customers shouldn't retype their motivation from scratch when
 * they come back for a renewal or a second licence. This service finds the
 * best data to pre-fill a new draft with:
 *
 *   • answers from the most recent motivation of the same type
 *   • supporting documents that are still fresh enough to satisfy the new
 *     type's requirements (proof-of-residence older than 90 days is skipped
 *     automatically, for example)
 *
 * One-off documents (POA, endorsement, invoice, proof of payment) are never
 * reused — they belong to the motivation they were created for.
 */
class PreviousMotivationData
{
    /**
     * Latest previous motivation of the same type, if any.
     */
    public static function latestOfType(User $user, ApplicationType $type, ?MotivationRequest $except = null): ?MotivationRequest
    {
        $query = $user->motivationRequests()
            ->where('application_type_id', $type->id)
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at');

        if ($except?->id) {
            $query->where('id', '!=', $except->id);
        }

        return $query->first();
    }

    /**
     * Prior answers to pre-fill, keyed by field name. Empty answers are
     * stripped so the new form doesn't look overwritten.
     *
     * @return array<string, mixed>
     */
    public static function answersFor(User $user, ApplicationType $type, ?MotivationRequest $except = null): array
    {
        $previous = self::latestOfType($user, $type, $except);
        if (! $previous) {
            return [];
        }

        return array_filter($previous->answers ?? [], fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    /**
     * Firearm particulars (make, calibre, serial…) carry across every
     * application type — the same handgun will typically appear on a
     * Self-Defence, Occ Hunt, Occ Sport, Ded Hunt and Ded Sport motivation
     * and we don't want the member retyping its serial five times.
     *
     * Returns a map of firearms_table field name → the most recent list of
     * rows found across *any* submitted motivation.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function firearmsAcrossTypes(User $user, ApplicationType $type, ?MotivationRequest $except = null): array
    {
        $firearmsFieldNames = [];
        foreach (QuestionSchema::fields($type->sections()) as $field) {
            if (($field['type'] ?? null) === 'firearms_table') {
                $firearmsFieldNames[] = $field['name'];
            }
        }
        if ($firearmsFieldNames === []) {
            return [];
        }

        $query = $user->motivationRequests()
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at');
        if ($except?->id) {
            $query->where('id', '!=', $except->id);
        }

        $out = [];
        foreach ($query->get() as $previous) {
            $answers = $previous->answers ?? [];
            foreach ($firearmsFieldNames as $name) {
                if (isset($out[$name])) {
                    continue;
                }
                $rows = self::firearmRowsFromAnswers($answers, $name);
                if ($rows !== []) {
                    $out[$name] = $rows;
                }
            }
            if (count($out) === count($firearmsFieldNames)) {
                break;
            }
        }

        return $out;
    }

    /**
     * Pull a firearms_table-shaped value from an answers blob. We don't
     * assume the field name matched — any firearms_table field in the
     * previous motivation with the same name as the current one is fair
     * game. Empty rows are stripped.
     *
     * @param  array<string, mixed>  $answers
     * @return array<int, array<string, mixed>>
     */
    private static function firearmRowsFromAnswers(array $answers, string $name): array
    {
        $value = $answers[$name] ?? null;
        if (! is_array($value) || $value === []) {
            return [];
        }

        $firearmColumns = array_keys(QuestionSchema::FIREARM_COLUMNS);
        $rows = [];
        foreach ($value as $row) {
            if (! is_array($row)) {
                continue;
            }
            // Must look like a firearm row (at least one of the known columns).
            if (array_intersect(array_keys($row), $firearmColumns) === []) {
                continue;
            }
            if (implode('', array_map(fn ($v) => trim((string) $v), $row)) === '') {
                continue;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * For every requirement on this type, find the most recent still-valid
     * document across all this member's prior motivations. Returns a map
     * keyed by document kind.
     *
     * @param  array<int, array<string, mixed>>  $requirements  normalised requirements from ApplicationType::requirements()
     * @return array<string, MotivationDocument>
     */
    public static function reusableDocumentsFor(User $user, array $requirements, ?MotivationRequest $except = null): array
    {
        $kinds = array_values(array_filter(array_map(
            fn ($r) => $r['kind'] ?? null,
            $requirements,
        )));
        $kinds = array_values(array_diff($kinds, MotivationDocument::nonReusableKinds()));

        if ($kinds === []) {
            return [];
        }

        $reqByKind = [];
        foreach ($requirements as $r) {
            if (isset($r['kind'])) {
                $reqByKind[$r['kind']] = $r;
            }
        }

        $query = MotivationDocument::query()
            ->whereIn('kind', $kinds)
            ->whereHas('motivationRequest', function ($q) use ($user, $except) {
                $q->where('user_id', $user->id);
                if ($except?->id) {
                    $q->where('id', '!=', $except->id);
                }
            })
            ->orderByDesc('created_at');

        $out = [];
        foreach ($query->get() as $doc) {
            if (isset($out[$doc->kind])) {
                continue; // keep only the most recent per kind
            }
            if (! $doc->isStillValid($reqByKind[$doc->kind] ?? null)) {
                continue;
            }
            $out[$doc->kind] = $doc;
        }

        return $out;
    }

    /**
     * Pre-fill a brand-new draft motivation with the member's previous
     * answers and still-valid documents. Returns a summary of what was
     * carried over so the UI can tell the member what still needs re-upload.
     *
     * @return array{answers_reused: bool, firearms_reused: bool, documents_reused: array<int, string>, stale_kinds: array<int, string>}
     */
    public static function applyTo(MotivationRequest $target, ?int $uploadedBy = null): array
    {
        $target->loadMissing(['user', 'applicationType']);
        $user = $target->user;
        $type = $target->applicationType;

        $answers = self::answersFor($user, $type, $target);
        $answersReused = $answers !== [];

        // The same handgun is motivated under Self-Defence, Occ Hunt, Occ
        // Sport, Dedicated Hunt and Dedicated Sport. Firearms_table fields
        // therefore carry across *every* type, not just same-type, so the
        // member only enters the serial / make / calibre once.
        $firearmsReused = false;
        foreach (self::firearmsAcrossTypes($user, $type, $target) as $name => $rows) {
            if (empty($answers[$name])) {
                $answers[$name] = $rows;
                $firearmsReused = true;
            }
        }

        if ($answers !== []) {
            $target->update(['answers' => $answers]);
        }

        $requirements = $type->requirements();
        $reusable = self::reusableDocumentsFor($user, $requirements, $target);

        $documentsReused = [];
        foreach ($reusable as $kind => $doc) {
            if ($doc->copyTo($target, $uploadedBy)) {
                $documentsReused[] = $doc->kindLabel();
            }
        }

        // Also surface which requirements the member *has* uploaded before
        // but can't carry over because they're stale — that's what the
        // reminder banner on the edit page uses.
        $staleKinds = self::staleKinds($user, $requirements, $reusable, $target);

        return [
            'answers_reused' => $answersReused,
            'firearms_reused' => $firearmsReused,
            'documents_reused' => $documentsReused,
            'stale_kinds' => $staleKinds,
        ];
    }

    /**
     * Labels of required document kinds the member has uploaded before but
     * cannot reuse — typically proof-of-residence older than 90 days or an
     * expired licence.
     *
     * @param  array<int, array<string, mixed>>  $requirements
     * @param  array<string, MotivationDocument>  $alreadyReused
     * @return array<int, string>
     */
    private static function staleKinds(User $user, array $requirements, array $alreadyReused, MotivationRequest $except): array
    {
        $reusedKinds = array_keys($alreadyReused);
        $labels = [];

        foreach ($requirements as $req) {
            $kind = $req['kind'] ?? null;
            if (! $kind || in_array($kind, MotivationDocument::nonReusableKinds(), true)) {
                continue;
            }
            if (in_array($kind, $reusedKinds, true)) {
                continue;
            }

            $hasPrior = MotivationDocument::whereHas('motivationRequest', function ($q) use ($user, $except) {
                $q->where('user_id', $user->id)->where('id', '!=', $except->id);
            })->where('kind', $kind)->exists();

            if ($hasPrior) {
                $labels[] = $req['label'] ?? DocumentKinds::label($kind);
            }
        }

        return $labels;
    }
}
