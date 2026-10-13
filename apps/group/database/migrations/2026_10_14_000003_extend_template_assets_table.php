<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('template_assets', function (Blueprint $table) {
            $table->string('category', 20)->default('other')->after('name');
            $table->string('alt')->nullable()->after('mime');
            $table->unsignedInteger('width')->nullable()->after('alt');
            $table->unsignedInteger('height')->nullable()->after('width');
            $table->unsignedInteger('size')->nullable()->after('height');
            $table->foreignId('created_by')->nullable()->after('size')->constrained('users')->nullOnDelete();
        });

        // Backfill dimensions / size for any existing public-disk assets so
        // the builder's asset browser can show thumbnails without an N+1.
        $disk = Storage::disk('public');
        foreach (DB::table('template_assets')->get() as $row) {
            if (! $disk->exists($row->path)) {
                continue;
            }
            try {
                $info = @getimagesizefromstring((string) $disk->get($row->path));
                $size = $disk->size($row->path);
                DB::table('template_assets')->where('id', $row->id)->update([
                    'width' => $info[0] ?? null,
                    'height' => $info[1] ?? null,
                    'size' => $size ?: null,
                ]);
            } catch (Throwable) {
                // Non-fatal — the asset is still usable, just without dimensions.
            }
        }
    }

    public function down(): void
    {
        Schema::table('template_assets', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['category', 'alt', 'width', 'height', 'size', 'created_by']);
        });
    }
};
