<?php

use App\Models\ApplicationType;
use App\Support\Motivations\Templates\LegacyBlockConverter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $converter = new LegacyBlockConverter;

        ApplicationType::query()
            ->whereHas('blocks')
            ->orderBy('id')
            ->each(function (ApplicationType $type) use ($converter) {
                $converter->convert($type);
            });
    }

    public function down(): void
    {
        // The conversion is designed to be idempotent and non-destructive.
        // Removing the templates on down() would also drop any new work
        // done on them — if a rollback is really needed, drop the tables.
    }
};
