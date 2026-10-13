<?php

namespace App\Support\Motivations;

use App\Models\MotivationRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pre-filled Power of Attorney page the member downloads, prints, signs and
 * uploads back as a required document.
 */
class PowerOfAttorneyGenerator
{
    public static function stream(MotivationRequest $request): Response
    {
        $pdf = Pdf::loadView('motivations.poa', [
            'request' => $request,
            'profile' => $request->profileData(),
        ])->setPaper('a4', 'portrait');

        $filename = 'power-of-attorney-'.$request->reference().'.pdf';

        return $pdf->download($filename);
    }

    public static function reference(MotivationRequest $request): Response
    {
        $pdf = Pdf::loadView('motivations.reference-letter', [
            'request' => $request,
            'profile' => $request->profileData(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('reference-letter-'.$request->reference().'.pdf');
    }
}
