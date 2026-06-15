<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

/**
 * Image optimizer for user-uploaded photos (arms listings, etc.).
 *
 * The goal here is to make the public arms page snappy: phone photos arrive
 * at 4-8 MB each, but a 4:3 card on a phone never benefits from anything
 * larger than ~1600 px on the long edge. We auto-orient using EXIF, scale
 * down (never up), and re-encode as a quality-82 progressive JPEG with EXIF
 * stripped. A typical phone photo ends up under 400 KB with no visible quality
 * loss at the size we display.
 */
class ImageOptimizer
{
    public const MAX_EDGE = 1600;

    public const QUALITY = 82;

    /**
     * Store an uploaded image in the given folder, optimised. Returns the
     * disk-relative path (e.g. "arms/abc123.jpg").
     */
    public static function storeUpload(
        UploadedFile $file,
        string $folder = 'arms',
        string $disk = 'public',
        int $maxEdge = self::MAX_EDGE,
        int $quality = self::QUALITY,
    ): string {
        $bytes = self::encodeFromPath($file->getRealPath(), $maxEdge, $quality);
        $path = trim($folder, '/').'/'.Str::random(40).'.jpg';
        Storage::disk($disk)->put($path, $bytes);

        return $path;
    }

    /**
     * Re-encode an existing file on the given disk in place. Useful for
     * back-filling historic uploads that were saved un-compressed. Returns
     * the (possibly renamed, always .jpg) path. The old file is removed
     * if the extension changed.
     */
    public static function optimizeExisting(
        string $diskPath,
        string $disk = 'public',
        int $maxEdge = self::MAX_EDGE,
        int $quality = self::QUALITY,
    ): string {
        $storage = Storage::disk($disk);
        if (! $storage->exists($diskPath)) {
            return $diskPath;
        }

        $absolute = $storage->path($diskPath);
        $bytes = self::encodeFromPath($absolute, $maxEdge, $quality);

        $folder = trim((string) pathinfo($diskPath, PATHINFO_DIRNAME), '.\\/');
        $name = pathinfo($diskPath, PATHINFO_FILENAME);
        $newPath = ($folder !== '' ? $folder.'/' : '').$name.'.jpg';

        $storage->put($newPath, $bytes);

        if ($newPath !== $diskPath) {
            $storage->delete($diskPath);
        }

        return $newPath;
    }

    /**
     * Decode the file at $absolutePath, orient via EXIF, scale down to fit
     * within $maxEdge × $maxEdge, then encode as progressive JPEG with EXIF
     * stripped. Returns the raw JPEG bytes.
     */
    protected static function encodeFromPath(string $absolutePath, int $maxEdge, int $quality): string
    {
        $originalMemory = ini_get('memory_limit');
        if (self::memoryLimitBytes($originalMemory) < 384 * 1024 * 1024) {
            ini_set('memory_limit', '512M');
        }

        try {
            $manager = new ImageManager(GdDriver::class);
            $image = $manager->decode($absolutePath);

            $image->orient()->scaleDown(width: $maxEdge, height: $maxEdge);

            $encoded = $image->encode(new JpegEncoder(
                quality: $quality,
                progressive: true,
                strip: true,
            ));

            return (string) $encoded;
        } finally {
            ini_set('memory_limit', $originalMemory);
        }
    }

    protected static function memoryLimitBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
