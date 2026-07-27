<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegisterBook extends Model
{
    protected $fillable = ['code', 'type', 'pages', 'positions_per_page', 'status'];

    protected function casts(): array
    {
        return [
            'pages' => 'integer',
            'positions_per_page' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(StorageItem::class);
    }

    /**
     * Total slot capacity of this physical book (pages × positions).
     */
    public function totalSlots(): int
    {
        return (int) $this->pages * (int) $this->positions_per_page;
    }

    /**
     * Number of slots already assigned to a firearm. Includes released
     * items, because a page/position is legally permanent once used.
     */
    public function occupiedSlots(): int
    {
        return $this->items()->count();
    }

    public function remainingSlots(): int
    {
        return max(0, $this->totalSlots() - $this->occupiedSlots());
    }

    /**
     * Suggest the next open (page, position) for this book, scanning in
     * page-then-position order. Returns null if the book is full.
     *
     * @return array{page: int, position: int}|null
     */
    public function nextOpenSlot(): ?array
    {
        $taken = $this->items()
            ->select('page', 'position')
            ->get()
            ->map(fn ($row) => $row->page.':'.$row->position)
            ->flip();

        for ($page = 1; $page <= $this->pages; $page++) {
            for ($pos = 1; $pos <= $this->positions_per_page; $pos++) {
                if (! isset($taken[$page.':'.$pos])) {
                    return ['page' => $page, 'position' => $pos];
                }
            }
        }

        return null;
    }

    public static function forType(string $type): ?self
    {
        return static::where('type', $type)->where('status', 'open')->orderBy('id')->first();
    }
}
