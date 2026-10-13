<?php

namespace App\Support\Motivations;

use App\Support\CustodyDisk;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Private storage for member documents and generated books. Uses the R2
 * custody bucket when it is configured and falls back to the private local
 * disk otherwise. Each stored row records which disk it went to so files
 * stay readable if R2 is configured later.
 */
class MotivationStorage
{
    public const DISK_CUSTODY = 'custody';

    public const DISK_LOCAL = 'local';

    public static function defaultDisk(): string
    {
        return match (config('motivations.storage', 'auto')) {
            self::DISK_CUSTODY => self::DISK_CUSTODY,
            self::DISK_LOCAL => self::DISK_LOCAL,
            default => CustodyDisk::isConfigured() ? self::DISK_CUSTODY : self::DISK_LOCAL,
        };
    }

    public static function disk(string $name): Filesystem
    {
        return $name === self::DISK_CUSTODY ? CustodyDisk::disk() : Storage::disk('local');
    }

    public static function documentPath(int $requestId, string $kind, string $extension): string
    {
        return sprintf('motivations/%d/%s-%s.%s', $requestId, $kind, Str::uuid(), ltrim(strtolower($extension), '.'));
    }

    public static function bookPath(int $requestId): string
    {
        return sprintf('motivations/%d/book-%s.pdf', $requestId, Str::uuid());
    }

    /**
     * @return string The disk name the contents were written to.
     */
    public static function put(string $path, string $contents): string
    {
        $disk = self::defaultDisk();
        self::disk($disk)->put($path, $contents);

        return $disk;
    }

    public static function get(string $disk, string $path): ?string
    {
        try {
            return self::disk($disk)->get($path);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function delete(string $disk, ?string $path): void
    {
        if (! $path) {
            return;
        }

        try {
            self::disk($disk)->delete($path);
        } catch (\Throwable) {
            // Missing objects or an unconfigured bucket must not block row deletion.
        }
    }

    /**
     * Short-lived redirect for R2, a streamed response for the local disk.
     */
    public static function response(string $disk, string $path, string $filename, bool $inline = true): Response
    {
        if ($disk === self::DISK_CUSTODY) {
            return redirect()->away(self::disk($disk)->temporaryUrl($path, now()->addMinutes(5), [
                'ResponseContentDisposition' => ($inline ? 'inline' : 'attachment').'; filename="'.addslashes($filename).'"',
            ]));
        }

        $local = Storage::disk('local');
        abort_unless($local->exists($path), 404);

        return $inline
            ? $local->response($path, $filename)
            : $local->download($path, $filename);
    }
}
