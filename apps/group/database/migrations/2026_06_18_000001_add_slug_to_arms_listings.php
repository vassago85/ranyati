<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('arms_listings', 'slug')) {
            Schema::table('arms_listings', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('title');
            });
        }

        // Backfill slugs for any existing rows.
        $rows = DB::table('arms_listings')->select('id', 'make', 'model', 'calibre')->get();
        $used = collect(DB::table('arms_listings')->whereNotNull('slug')->pluck('slug'))->flip();

        foreach ($rows as $row) {
            $base = Str::slug(trim(($row->make ?? '').' '.($row->model ?? '').' '.($row->calibre ?? ''))) ?: 'listing';
            $candidate = $base.'-'.Str::lower(Str::random(6));

            // Extremely unlikely, but guard against collisions on the random suffix.
            while ($used->has($candidate)) {
                $candidate = $base.'-'.Str::lower(Str::random(6));
            }
            $used->put($candidate, true);

            DB::table('arms_listings')->where('id', $row->id)->update(['slug' => $candidate]);
        }

        // Now that every row has a slug, enforce uniqueness + non-null.
        Schema::table('arms_listings', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug', 'arms_listings_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('arms_listings', function (Blueprint $table) {
            if (Schema::hasColumn('arms_listings', 'slug')) {
                try { $table->dropUnique('arms_listings_slug_unique'); } catch (\Throwable $e) { /* ignore */ }
                $table->dropColumn('slug');
            }
        });
    }
};
