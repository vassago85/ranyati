<?php

namespace App\Http\Controllers\Admin\Storage;

use App\Http\Controllers\Controller;
use App\Models\StorageItem;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * 62mm × 100mm portrait label PDF for the Brother QL series printer
 * running with continuous 62mm tape. dompdf takes millimetre paper sizes
 * via a [width, height] point tuple (1mm = 2.83465 pt).
 */
class LabelController extends Controller
{
    public function show(StorageItem $item)
    {
        $item->load(['agreement', 'book']);

        $qrPngDataUri = $this->buildQrDataUri(route('admin.storage.items.show', $item));

        $pdf = Pdf::loadView('admin.storage.labels.item', [
            'item' => $item,
            'qrPngDataUri' => $qrPngDataUri,
        ])->setPaper([
            0.0,
            0.0,
            62 * 2.83465,   // 62 mm wide
            100 * 2.83465,  // 100 mm tall
        ], 'portrait');

        $filename = 'label-'.$item->register_ref.'.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Encode the item's admin URL as a QR PNG and return it as a data URI
     * that dompdf can drop straight into an <img src>.
     */
    private function buildQrDataUri(string $payload): string
    {
        $options = new QROptions([
            'version'         => 5,
            'outputInterface' => QRGdImagePNG::class,
            'eccLevel'        => EccLevel::M,
            'scale'           => 5,
            'outputBase64'    => true,
        ]);

        return (new QRCode($options))->render($payload);
    }
}
