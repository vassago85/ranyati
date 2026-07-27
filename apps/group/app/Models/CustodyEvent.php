<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * Append-only custody ledger. A custody event, once written, is the legal
 * record for that step in the firearm's chain of custody — updating or
 * deleting one would compromise that record. The booted() guards below
 * enforce immutability at the model layer regardless of caller.
 *
 * Corrections MUST be recorded as new events with event_type = 'correction'
 * that reference the earlier event in notes.
 */
class CustodyEvent extends Model
{
    public const TYPE_INTAKE            = 'intake';
    public const TYPE_INSPECTION        = 'inspection';
    public const TYPE_TRANSFER_INTERNAL = 'transfer_internal';
    public const TYPE_RELEASE           = 'release';
    public const TYPE_CORRECTION        = 'correction';
    public const TYPE_NOTE              = 'note';

    protected $fillable = [
        'storage_item_id',
        'user_id',
        'event_type',
        'notes',
        'released_to_name',
        'released_to_id_number',
        'old_tag',
        'new_tag',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(StorageItem::class, 'storage_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public static function types(): array
    {
        return [
            self::TYPE_INTAKE            => 'Intake',
            self::TYPE_INSPECTION        => 'Inspection',
            self::TYPE_TRANSFER_INTERNAL => 'Internal transfer',
            self::TYPE_RELEASE           => 'Release',
            self::TYPE_CORRECTION        => 'Correction',
            self::TYPE_NOTE              => 'Note',
        ];
    }

    public function label(): string
    {
        return self::types()[$this->event_type] ?? ucfirst((string) $this->event_type);
    }

    protected static function booted(): void
    {
        static::updating(function (self $event): void {
            throw new RuntimeException(
                'CustodyEvent is append-only. Record a new event with event_type = "correction" instead of updating id='.$event->id.'.'
            );
        });

        static::deleting(function (self $event): void {
            throw new RuntimeException(
                'CustodyEvent is append-only and cannot be deleted (id='.$event->id.').'
            );
        });
    }
}
