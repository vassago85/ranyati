<?php

namespace App\Support\Motivations;

/**
 * Catalogue of supporting-document kinds a member (or admin) can upload
 * against a motivation request. Application types reference these keys in
 * their document_requirements, and book `document_insert` blocks pull every
 * upload of the selected kinds into the printed book.
 */
class DocumentKinds
{
    public const POWER_OF_ATTORNEY = 'power_of_attorney';
    public const ENDORSEMENT = 'endorsement';
    public const REFERENCE = 'reference';
    public const INVOICE = 'invoice';
    public const PROOF_OF_PAYMENT = 'proof_of_payment';
    public const OTHER = 'other';

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            self::POWER_OF_ATTORNEY => 'Signed Power of Attorney',
            'id_document' => 'ID document',
            'proof_of_residence' => 'Proof of residence',
            'residence_confirmation' => 'Residence confirmation / rental agreement',
            'safe_photos' => 'Photos of safe',
            'current_licences' => 'Current firearm licence cards',
            'proficiency_certificate' => 'Proficiency / competency training certificate',
            'pftc_results' => 'PFTC Statement of Results',
            'saps_competency' => 'SAPS competency certificate',
            'saps_271' => 'SAPS 271 (new firearm from dealer)',
            'seller_id_licence' => 'Private seller ID and licence',
            'estate_executorship' => 'Letter of executorship',
            'estate_executor_id' => 'Executor ID and executor letter',
            'estate_transfer_letter' => 'Executor transfer letter',
            'estate_death_certificate' => 'Death certificate and deceased ID',
            'estate_deceased_licence' => 'Deceased firearm licence card',
            'membership_certificate' => 'Confirmation of membership certificate',
            'dedicated_certificate' => 'Dedicated hunter / sport shooter certificate',
            self::ENDORSEMENT => 'Club / association endorsement',
            'compliance_history' => 'Compliance history (activity log / scores)',
            'sport_proof' => 'Sport shooting proof',
            'hunting_proof' => 'Hunting proof',
            'case_numbers' => 'Case numbers / police reports',
            'crime_clippings' => 'Newspaper clippings of crime in the area',
            self::REFERENCE => 'Reference letter / testimonial',
            self::INVOICE => 'Invoice',
            self::PROOF_OF_PAYMENT => 'Proof of payment',
            self::OTHER => 'Other',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function label(string $kind): string
    {
        return self::all()[$kind] ?? ucfirst(str_replace('_', ' ', $kind));
    }

    public static function exists(string $kind): bool
    {
        return array_key_exists($kind, self::all());
    }
}
