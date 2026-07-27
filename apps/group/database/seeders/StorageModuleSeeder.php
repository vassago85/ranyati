<?php

namespace Database\Seeders;

use App\Models\RegisterBook;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Bootstraps the two physical register books used from day one, plus the
 * defaults for storage settings. Safe to re-run — every write is an
 * updateOrCreate/updateOrCreate-equivalent.
 */
class StorageModuleSeeder extends Seeder
{
    public function run(): void
    {
        RegisterBook::updateOrCreate(
            ['code' => 'D01'],
            [
                'type'               => 'deceased_estate',
                'pages'              => 101,
                'positions_per_page' => 26,
                'status'             => 'open',
            ],
        );

        RegisterBook::updateOrCreate(
            ['code' => 'S01'],
            [
                'type'               => 'self_storage',
                'pages'              => 101,
                'positions_per_page' => 26,
                'status'             => 'open',
            ],
        );

        // Default self-storage rate — editable per agreement at intake.
        if (Setting::get('storage.default_rate') === null) {
            Setting::set('storage.default_rate', '100.00', 'storage');
        }
    }
}
