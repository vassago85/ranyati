<?php

namespace App\Support;

/**
 * Single source of truth for the services & fees offered by Ranyati Firearm
 * Motivations. The public enquire form, the admin enquiry-show view, and the
 * notification email all read labels and prices from this registry — change
 * a price here and it propagates everywhere automatically.
 *
 * Prices are stored as integer Rands (no cents) — fees never need fractional
 * values in this domain.
 */
class MotivationServices
{
    /**
     * @return array<int, array{key: string, label: string, price: int, description: string, group: string}>
     */
    public static function all(): array
    {
        return [
            [
                'key'         => 'motivation',
                'label'       => 'Motivation',
                'price'       => 920,
                'description' => 'A full motivation per firearm. Each motivation includes the reasons to counter past CFR refusals plus your supporting documentation, printed and bound in book form for submission to your DFO.',
                'group'       => 'Motivations',
            ],
            [
                'key'         => 'motivation_72h',
                'label'       => 'Motivation within 72 Hours',
                'price'       => 1150,
                'description' => 'Same as the standard motivation, turned around within 72 working hours. Monday to Friday only.',
                'group'       => 'Motivations',
            ],
            [
                'key'         => 'motivation_24h',
                'label'       => 'Motivation within 24 Hours',
                'price'       => 1380,
                'description' => 'Same as the standard motivation, turned around within 24 working hours. Monday to Friday only. Courier service is not available with this turn-around.',
                'group'       => 'Motivations',
            ],
            [
                'key'         => 'competency_form',
                'label'       => 'Competency Application Form',
                'price'       => 405,
                'description' => 'We complete the SAPS517 / SAPS517A competency application form on your behalf, with the relevant supporting documents, printed and bound in book form.',
                'group'       => 'Application Forms',
            ],
            [
                'key'         => 'review_form',
                'label'       => 'Reviewing a completed application form',
                'price'       => 175,
                'description' => 'You complete the SAPS application form yourself and we verify the correctness. (Not applicable to motivations written by yourself or a different company — only for completed application forms.)',
                'group'       => 'Application Forms',
            ],
            [
                'key'         => 'courier_gauteng',
                'label'       => 'Courier within Gauteng',
                'price'       => 200,
                'description' => 'Door-to-door courier delivery within Gauteng. No deliveries to farms or post boxes. Not operational over weekends and not available with the 24-hour service.',
                'group'       => 'Other',
            ],
            [
                'key'         => 'courier_outside',
                'label'       => 'Courier outside Gauteng',
                'price'       => 250,
                'description' => 'Door-to-door courier delivery outside Gauteng. No deliveries to farms or post boxes. Not operational over weekends and not available with the 24-hour service.',
                'group'       => 'Other',
            ],
            [
                'key'         => 'reprint',
                'label'       => 'Reprint and collation of an existing Ranyati motivation',
                'price'       => 405,
                'description' => 'We re-print and re-bind your complete motivation and supporting documents as previously supplied, after reviewing again that everything is correct and up to date. Not applicable to motivations older than one year.',
                'group'       => 'Other',
            ],
            [
                'key'         => 'appeal',
                'label'       => 'Appeal (via Clark Attorneys)',
                'price'       => 4025,
                'description' => 'Per appeal, per firearm. Handled through Clark Attorneys; we liaise on your behalf. Only applicable if your original motivation was completed by Ranyati Firearm Motivations.',
                'group'       => 'Other',
            ],
        ];
    }

    /**
     * @return array<int, string> The list of valid service keys for validation.
     */
    public static function validKeys(): array
    {
        return array_map(fn ($s) => $s['key'], self::all());
    }

    /**
     * Resolve an array of submitted keys back into the full registry rows,
     * preserving the registry's display order and dropping any unknown keys.
     *
     * @param  array<int, string>|null  $keys
     * @return array<int, array{key: string, label: string, price: int, description: string, group: string}>
     */
    public static function resolve(?array $keys): array
    {
        if (! $keys) {
            return [];
        }

        $set = array_flip(array_filter($keys, 'is_string'));

        return array_values(array_filter(
            self::all(),
            fn ($row) => isset($set[$row['key']]),
        ));
    }

    /**
     * Sum the price of all selected services (in whole Rands).
     */
    public static function total(?array $keys): int
    {
        return array_sum(array_map(fn ($r) => $r['price'], self::resolve($keys)));
    }

    /**
     * Banking details displayed alongside the services section.
     */
    public static function bankDetails(): array
    {
        return [
            'account_name' => 'Ranyati Firearm Motivations',
            'bank'         => 'First National Bank',
            'branch'       => 'Menlyn Square',
            'branch_code'  => '252-445',
            'account_no'   => '62428359443',
            'proof_email'  => 'info@firearmmotivations.co.za',
        ];
    }
}
