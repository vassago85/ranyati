<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Personal particulars shared by every questionnaire. Filled once by the
 * member and copied into each motivation request when an admin confirms it,
 * so later profile edits never rewrite a book that has already been printed.
 */
class ClientProfile extends Model
{
    public const FIELDS = [
        'full_names' => 'Full names',
        'surname' => 'Surname',
        'id_number' => 'ID number',
        'physical_address' => 'Physical address',
        'postal_address' => 'Postal address',
        'employer' => 'Name of employer / company',
        'self_employed' => 'Self-employed',
        'business_address' => 'Company / business address',
        'occupation' => 'Occupation',
        'cell' => 'Cell',
        'fax' => 'Fax',
        'tel_work' => 'Tel (W)',
        'tel_home' => 'Tel (H)',
        'email' => 'E-mail address',
        'spouse_details' => "Spouse's ID and full names",
        'facebook_consent' => 'May we send you a Facebook friend request?',
        'facebook_name' => 'Facebook name',
        'association_name' => 'Association name',
        'association_number' => 'Association membership number',
    ];

    /** Fields that must be filled before a request can be submitted. */
    public const REQUIRED = ['full_names', 'surname', 'id_number', 'physical_address', 'occupation', 'cell', 'email'];

    protected $fillable = [
        'user_id',
        'full_names',
        'surname',
        'id_number',
        'physical_address',
        'postal_address',
        'employer',
        'self_employed',
        'business_address',
        'occupation',
        'cell',
        'fax',
        'tel_work',
        'tel_home',
        'email',
        'spouse_details',
        'facebook_consent',
        'facebook_name',
        'association_name',
        'association_number',
    ];

    protected function casts(): array
    {
        return [
            'self_employed' => 'boolean',
            'facebook_consent' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Format a 13-digit SA ID as `YYMMDD SSSS CC A` — the spacing used on
     * the running header of a printed firearm motivation.
     */
    public static function formatSaIdNumber(?string $idNumber): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $idNumber);
        if (strlen($digits) !== 13) {
            $raw = trim((string) $idNumber);

            return $raw !== '' ? $raw : null;
        }

        return substr($digits, 0, 6).' '.substr($digits, 6, 4).' '.substr($digits, 10, 2).' '.substr($digits, 12, 1);
    }

    /**
     * Age derived from the YYMMDD prefix of a South African ID number.
     */
    public static function ageFromIdNumber(?string $idNumber): ?int
    {
        $digits = preg_replace('/\D/', '', (string) $idNumber);
        if (strlen($digits) < 6) {
            return null;
        }

        $yy = (int) substr($digits, 0, 2);
        $mm = (int) substr($digits, 2, 2);
        $dd = (int) substr($digits, 4, 2);
        if (! checkdate($mm, $dd, 2000)) {
            return null;
        }

        $currentYy = (int) now()->format('y');
        $year = $yy > $currentYy ? 1900 + $yy : 2000 + $yy;

        try {
            return Carbon::create($year, $mm, $dd)->age;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Flat array used for snapshots, placeholders and conditions.
     *
     * @return array<string, mixed>
     */
    public function toPlaceholderArray(): array
    {
        $data = $this->only(array_keys(self::FIELDS));

        return self::withDerived($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function withDerived(array $data): array
    {
        $data['full_name'] = trim(($data['full_names'] ?? '').' '.($data['surname'] ?? ''));
        $data['age'] = self::ageFromIdNumber($data['id_number'] ?? null);
        $data['id_number_spaced'] = self::formatSaIdNumber($data['id_number'] ?? null);

        return $data;
    }

    /**
     * @return array<int, string> Labels of required fields that are still blank.
     */
    public function missingRequired(): array
    {
        $missing = [];
        foreach (self::REQUIRED as $field) {
            if (blank($this->{$field})) {
                $missing[] = self::FIELDS[$field];
            }
        }

        return $missing;
    }
}
