<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotivationEnquiry extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_AWAITING_DOCS = 'awaiting_docs';
    public const STATUS_CLOSED = 'closed';

    /**
     * Ordered map of workflow states → human labels. Ordering matches the
     * typical lifecycle so pickers/filters render in a sensible sequence.
     *
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_IN_PROGRESS => 'In progress',
            self::STATUS_AWAITING_DOCS => 'Awaiting docs',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function statusKeys(): array
    {
        return array_keys(self::statusLabels());
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'endorsement_type',
        'saps_station',
        'purpose',
        'services',
        'membership_number',
        'message',
        'source',
        'read_at',
        'status',
        'replied_at',
    ];

    protected $attributes = [
        'status' => self::STATUS_NEW,
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'replied_at' => 'datetime',
            'services' => 'array',
        ];
    }

    /**
     * True when the enquiry hasn't been replied to yet and isn't closed —
     * used across the queue as the "needs reply" indicator.
     */
    public function needsReply(): bool
    {
        return $this->replied_at === null && $this->status !== self::STATUS_CLOSED;
    }
}
