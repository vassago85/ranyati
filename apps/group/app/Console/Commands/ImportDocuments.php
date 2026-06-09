<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportDocuments extends Command
{
    protected $signature = 'documents:import
                            {source : Absolute path to a folder containing the files to import}
                            {--category= : Category to assign to all imported documents}';

    protected $description = 'Import PDF/Word/Excel files from a folder into the downloadable documents library';

    public function handle(): int
    {
        $source = rtrim($this->argument('source'), "/\\");
        $category = $this->option('category');

        if (! is_dir($source)) {
            $this->error("Source folder not found: {$source}");

            return self::FAILURE;
        }

        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $files = collect(scandir($source) ?: [])
            ->reject(fn ($f) => in_array($f, ['.', '..']))
            ->filter(fn ($f) => is_file($source.DIRECTORY_SEPARATOR.$f))
            ->filter(fn ($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $allowed))
            ->values();

        if ($files->isEmpty()) {
            $this->warn('No importable files found.');

            return self::SUCCESS;
        }

        $order = (int) (Document::max('sort_order') ?? 0);
        $imported = 0;
        $skipped = 0;

        foreach ($files as $original) {
            if (Document::where('original_name', $original)->exists()) {
                $this->line("  <fg=yellow>skip</> {$original} (already imported)");
                $skipped++;

                continue;
            }

            $fullPath = $source.DIRECTORY_SEPARATOR.$original;
            $contents = file_get_contents($fullPath);
            $storedName = uniqid('doc_', true).'.'.strtolower(pathinfo($original, PATHINFO_EXTENSION));
            $storedPath = 'documents/'.$storedName;

            Storage::disk('public')->put($storedPath, $contents);

            Document::create([
                'title' => Document::titleFromFilename($original),
                'category' => $category,
                'path' => $storedPath,
                'original_name' => $original,
                'mime' => mime_content_type($fullPath) ?: null,
                'size' => filesize($fullPath) ?: 0,
                'sort_order' => ++$order,
                'is_published' => true,
            ]);

            $this->line("  <fg=green>ok</>   {$original}");
            $imported++;
        }

        $this->newLine();
        $this->info("Imported {$imported}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
