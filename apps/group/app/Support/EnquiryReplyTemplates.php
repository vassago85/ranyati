<?php

namespace App\Support;

use App\Models\MotivationEnquiry;

/**
 * Reply-template library used by the admin enquiry-show page.
 *
 * Replies are still sent via `mailto:` so the operator's email client handles
 * the actual send (and their signature) — this class only assembles the
 * subject and pre-filled body so staff aren't retyping context that's
 * already on the screen.
 *
 * Templates are intentionally hard-coded here rather than DB-editable — they
 * change rarely and living in code keeps them under version control alongside
 * the receipt mail they mirror in tone.
 */
class EnquiryReplyTemplates
{
    public const TEMPLATE_ACKNOWLEDGEMENT = 'acknowledgement';
    public const TEMPLATE_RENEWAL = 'renewal';
    public const TEMPLATE_REQUEST_DOCS = 'request_docs';
    public const TEMPLATE_QUOTE = 'quote';

    /**
     * Ordered list of available templates. Each entry has a stable `key`
     * (used in URLs / hidden inputs), a `label` for the picker button, and
     * a `subject` prefix that becomes the reply subject line.
     *
     * @return array<int, array{key: string, label: string, subject: string}>
     */
    public static function all(): array
    {
        return [
            [
                'key' => self::TEMPLATE_ACKNOWLEDGEMENT,
                'label' => 'Acknowledgement',
                'subject' => 'Your firearm motivation enquiry — Ranyati',
            ],
            [
                'key' => self::TEMPLATE_RENEWAL,
                'label' => 'Renewal',
                'subject' => 'Your firearm licence renewal — Ranyati',
            ],
            [
                'key' => self::TEMPLATE_REQUEST_DOCS,
                'label' => 'Request documents',
                'subject' => 'A few more documents needed — Ranyati',
            ],
            [
                'key' => self::TEMPLATE_QUOTE,
                'label' => 'Quote confirmation',
                'subject' => 'Your motivation quote — Ranyati',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_map(fn ($row) => $row['key'], self::all());
    }

    /**
     * Resolve a template row by key, defaulting to the acknowledgement
     * template when the caller passes null / an unknown key so the caller
     * always gets back a usable subject + label.
     *
     * @return array{key: string, label: string, subject: string}
     */
    public static function resolve(?string $key): array
    {
        $key = $key ?: self::TEMPLATE_ACKNOWLEDGEMENT;

        foreach (self::all() as $row) {
            if ($row['key'] === $key) {
                return $row;
            }
        }

        return self::all()[0];
    }

    /**
     * The personalised context block that leads every reply body. Pulls
     * name, endorsement type, purpose, and the itemised services + total
     * straight off the enquiry so staff don't have to retype it.
     */
    public static function contextBody(MotivationEnquiry $enquiry): string
    {
        $lines = [];
        $lines[] = 'Hi ' . trim($enquiry->name ?: 'there') . ',';
        $lines[] = '';
        $lines[] = 'Thanks for your enquiry. Here is a quick summary of what you sent us so we\'re on the same page:';
        $lines[] = '';

        if (! empty($enquiry->endorsement_type)) {
            $lines[] = '  • Endorsement type: ' . $enquiry->endorsement_type;
        }

        if (! empty($enquiry->purpose)) {
            $lines[] = '  • Purpose: ' . $enquiry->purpose;
        }

        if (! empty($enquiry->saps_station)) {
            $lines[] = '  • SAPS station: ' . $enquiry->saps_station;
        }

        if (! empty($enquiry->membership_number)) {
            $lines[] = '  • NRAPA membership: ' . $enquiry->membership_number;
        }

        $services = MotivationServices::resolve($enquiry->services ?? []);
        if (! empty($services)) {
            $lines[] = '';
            $lines[] = 'Services requested:';
            foreach ($services as $svc) {
                $lines[] = '  • ' . $svc['label'] . ' — R' . number_format($svc['price'], 0, '.', ',');
            }

            $total = MotivationServices::total($enquiry->services ?? []);
            $lines[] = '';
            $lines[] = 'Estimated total: R' . number_format($total, 0, '.', ',');
            $lines[] = '(Indicative — final pricing confirmed by our office.)';
        }

        return implode("\n", $lines);
    }

    /**
     * Full pre-filled body for the chosen template. Every template opens
     * with the context block and then adds template-specific copy so the
     * operator can just tweak and hit send.
     */
    public static function body(MotivationEnquiry $enquiry, ?string $templateKey): string
    {
        $context = self::contextBody($enquiry);
        $extra = self::templateExtras($templateKey);

        return $context . "\n\n" . $extra;
    }

    /**
     * Template-specific body copy appended after the shared context block.
     */
    protected static function templateExtras(?string $templateKey): string
    {
        $key = self::resolve($templateKey)['key'];

        switch ($key) {
            case self::TEMPLATE_RENEWAL:
                return <<<TEXT
For your renewal, please send us the following as soon as possible so we can start on your motivation:

  • A copy of the licence you're renewing (both sides).
  • A copy of your ID.
  • Proof of your current membership / accreditation.

Renewals should be lodged with SAPS 90 days before your licence expires — the earlier we start, the more comfortable that window is.

Let me know if you'd like us to courier the completed motivation to you and I'll add it to your quote.

Warm regards,
Ranyati Firearm Motivations
TEXT;

            case self::TEMPLATE_REQUEST_DOCS:
                return <<<TEXT
Before we can finalise your motivation, we still need a few supporting documents from you:

  • A clear copy of your ID (both sides).
  • Proof of address (not older than 3 months).
  • [Add any additional documents specific to this application]

Please reply to this email with the documents attached, or drop them via WhatsApp on +27 87 151 0987. As soon as they're in we'll finalise your motivation and let you know when it's ready for collection or courier.

Warm regards,
Ranyati Firearm Motivations
TEXT;

            case self::TEMPLATE_QUOTE:
                $bank = MotivationServices::bankDetails();

                return <<<TEXT
Please treat the amounts above as confirmation of your quote. To proceed, EFT the total to:

  Account name:  {$bank['account_name']}
  Bank:          {$bank['bank']}
  Branch:        {$bank['branch']} ({$bank['branch_code']})
  Account no:    {$bank['account_no']}
  Reference:     Your surname + licence number

Email proof of payment to {$bank['proof_email']} and we'll start immediately. Standard turn-around is 7–10 working days; expedited options are available on request.

Warm regards,
Ranyati Firearm Motivations
TEXT;

            case self::TEMPLATE_ACKNOWLEDGEMENT:
            default:
                return <<<TEXT
We've received your enquiry and one of our team will be in touch shortly to confirm final pricing and walk you through what we'll need from you next. Standard motivations turn around in 7–10 working days; expedited (24 or 72 hour) options are available if you're in a rush.

If anything above looks wrong, just reply to this email and we'll update it.

Warm regards,
Ranyati Firearm Motivations
TEXT;
        }
    }

    /**
     * Full `mailto:` URL ready to drop into an anchor tag. Handles subject +
     * body encoding so the operator's email client opens with everything
     * pre-filled.
     */
    public static function mailtoUrl(MotivationEnquiry $enquiry, ?string $templateKey): string
    {
        $template = self::resolve($templateKey);
        // Only the query part is percent-encoded — the addr-spec is left as-is
        // so `@` and `.` render naturally in the mail client's To field.
        $subject = rawurlencode($template['subject']);
        $body = rawurlencode(self::body($enquiry, $templateKey));

        return 'mailto:' . $enquiry->email . '?subject=' . $subject . '&body=' . $body;
    }
}
