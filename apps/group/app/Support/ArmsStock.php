<?php

namespace App\Support;

use App\Models\ArmsListing;

class ArmsStock
{
    public static function next(): string
    {
        return self::format(self::highest() + 1);
    }

    public static function backfill(): int
    {
        $rows = ArmsListing::query()
            ->where(function ($query): void {
                $query->whereNull('stock_number')->orWhere('stock_number', '');
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $number = self::highest();
        $updated = 0;

        foreach ($rows as $listing) {
            $number++;
            $listing->stock_number = self::format($number);
            $listing->saveQuietly();
            $updated++;
        }

        return $updated;
    }

    public static function highest(): int
    {
        $max = 0;

        foreach (ArmsListing::query()->whereNotNull('stock_number')->pluck('stock_number') as $stockNumber) {
            if (preg_match('/^A-(\d+)$/', (string) $stockNumber, $matches) === 1) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $max;
    }

    public static function format(int $number): string
    {
        return sprintf('A-%04d', $number);
    }
}
