<?php

namespace App\Support\Motivations\Templates;

use App\Models\MotivationTemplate;
use App\Models\MotivationTemplateVersion;
use Illuminate\Support\Facades\DB;

/**
 * Owns the draft / publish / restore lifecycle of a template's versions.
 *
 * A template has at most one draft (an unpublished row). Editing writes to
 * that draft; publishing stamps it and advances `published_version_id`.
 * Restoring an older version makes a fresh draft that copies its blocks.
 */
class TemplateVersionManager
{
    /**
     * Return the current draft version, creating it from the latest version
     * (or an empty default) if none exists.
     */
    public function ensureDraft(MotivationTemplate $template, ?int $actorId = null): MotivationTemplateVersion
    {
        $draft = $template->draftVersion();
        if ($draft) {
            return $draft;
        }

        $latest = $template->latestVersion();

        return MotivationTemplateVersion::create([
            'template_id' => $template->id,
            'version' => ($latest?->version ?? 0) + 1,
            'blocks' => $latest ? $latest->blocks : [],
            'settings' => $latest ? $latest->settings : MotivationTemplateVersion::defaultSettings(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    /**
     * Save the given blocks + settings to the draft (creating one if needed).
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, mixed>  $settings
     */
    public function saveDraft(
        MotivationTemplate $template,
        array $blocks,
        array $settings,
        ?int $actorId = null,
    ): MotivationTemplateVersion {
        return DB::transaction(function () use ($template, $blocks, $settings, $actorId) {
            $draft = $this->ensureDraft($template, $actorId);

            $draft->update([
                'blocks' => TemplateSchema::normalizeBlocks($blocks),
                'settings' => TemplateSchema::normalizeSettingsJson($settings),
                'updated_by' => $actorId,
            ]);

            $template->update(['updated_by' => $actorId]);

            return $draft->fresh();
        });
    }

    /**
     * Freeze the draft as a published version and advance the pointer.
     */
    public function publish(MotivationTemplate $template, ?int $actorId = null): MotivationTemplateVersion
    {
        return DB::transaction(function () use ($template, $actorId) {
            $draft = $template->draftVersion();
            if (! $draft) {
                $draft = $this->ensureDraft($template, $actorId);
            }

            $draft->update([
                'published_at' => now(),
                'published_by' => $actorId,
            ]);

            $template->update([
                'status' => MotivationTemplate::STATUS_PUBLISHED,
                'published_version_id' => $draft->id,
                'updated_by' => $actorId,
            ]);

            return $draft->fresh();
        });
    }

    /**
     * Copy an older version's blocks into a new draft. Returns the draft.
     */
    public function restore(MotivationTemplate $template, MotivationTemplateVersion $source, ?int $actorId = null): MotivationTemplateVersion
    {
        return DB::transaction(function () use ($template, $source, $actorId) {
            // If there is a draft, overwrite it; otherwise create a new one.
            $draft = $template->draftVersion();
            if ($draft) {
                $draft->update([
                    'blocks' => $source->blocks,
                    'settings' => $source->settings,
                    'updated_by' => $actorId,
                ]);

                return $draft->fresh();
            }

            $latest = $template->latestVersion();

            return MotivationTemplateVersion::create([
                'template_id' => $template->id,
                'version' => ($latest?->version ?? 0) + 1,
                'blocks' => $source->blocks,
                'settings' => $source->settings,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
        });
    }
}
