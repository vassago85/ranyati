<?php

namespace App\Support;

use App\Models\AdminActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Thin helper around `admin_activity_log` so controllers and jobs can record
 * an action without re-stating the actor / subject / timestamp plumbing.
 */
class ActivityLog
{
    public const ACTION_TEMPLATE_PUBLISHED = 'template.published';

    public const ACTION_TEMPLATE_RESTORED = 'template.restored';

    public const ACTION_TEMPLATE_ARCHIVED = 'template.archived';

    public const ACTION_TEMPLATE_ASSIGNED = 'template.assigned';

    public const ACTION_TEMPLATE_UNASSIGNED = 'template.unassigned';

    public const ACTION_LETTER_GENERATED = 'letter.generated';

    public const ACTION_LETTER_REGENERATED = 'letter.regenerated';

    public const ACTION_LETTER_FINALISED = 'letter.finalised';

    public const ACTION_LETTER_EDITED = 'letter.edited';

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function record(string $action, ?Model $subject = null, array $meta = [], ?int $userId = null): AdminActivityLog
    {
        return AdminActivityLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'meta' => $meta ?: null,
            'created_at' => now(),
        ]);
    }
}
