<?php

namespace App\Http\Controllers\Admin\Storage;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

/**
 * Thin bridge that mirrors what App\Support\ImageOptimizer does but
 * returns raw JPEG bytes for callers that don't want to write to a
 * Laravel disk (i.e. we hand the bytes to Cloudflare R2 ourselves).
 *
 * Same output profile as ImageOptimizer: EXIF-oriented, scaled to fit
 * within 1600 × 1600, quality-82 progressive JPEG with EXIF stripped.
 */
class ImageOptimizerBridge
{
    public const MAX_EDGE = 1600;
    public const QUALITY  = 82;

    public static function optimize(UploadedFile $file): string
    {
        $manager = new ImageManager(GdDriver::class);
        $image = $manager->decode($file->getRealPath());
        $image->orient()->scaleDown(width: self::MAX_EDGE, height: self::MAX_EDGE);

        return (string) $image->encode(new JpegEncoder(
            quality: self::QUALITY,
            progressive: true,
            strip: true,
        ));
    }
}
