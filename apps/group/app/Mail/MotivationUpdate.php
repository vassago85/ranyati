<?php

namespace App\Mail;

use App\Models\MotivationRequest;
use App\Support\Motivations\DocumentKinds;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * One mailable for every stage of a motivation request. The envelope and
 * body swap on `$stage` so the template library stays tiny. Internal stages
 * (new submission, proof of payment uploaded) target the Ranyati inbox.
 */
class MotivationUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public const STAGE_SUBMITTED = 'submitted';

    public const STAGE_CHANGES_REQUESTED = 'changes_requested';

    public const STAGE_INVOICED = 'invoiced';

    public const STAGE_PAID = 'paid';

    public const STAGE_READY_FOR_COLLECTION = 'ready_for_collection';

    public const STAGE_SHIPPED = 'shipped';

    public const STAGE_COMPLETED = 'completed';

    public const STAGE_NEW_SUBMISSION_INTERNAL = 'new_submission_internal';

    public const STAGE_PROOF_OF_PAYMENT_INTERNAL = 'proof_of_payment_internal';

    public function __construct(
        public MotivationRequest $motivation,
        public string $stage,
        public ?string $note = null,
    ) {}

    public function envelope(): Envelope
    {
        $this->motivation->loadMissing(['user.clientProfile', 'applicationType']);
        $contact = config('motivations.contact');
        $from = new Address(
            config('mail.from.address', 'noreply@firearmmotivations.co.za'),
            $contact['company'] ?? 'Ranyati Firearm Motivations',
        );

        if ($this->isInternal()) {
            $inbox = config('motivations.inbox') ?: config('mail.from.address');

            return new Envelope(
                from: $from,
                to: [new Address($inbox, $contact['company'] ?? 'Ranyati Inbox')],
                subject: $this->subjectLine(),
            );
        }

        return new Envelope(
            from: $from,
            to: [new Address($this->motivation->user->email, $this->motivation->user->name)],
            replyTo: $contact['email'] ? [new Address($contact['email'], $contact['company'] ?? 'Ranyati')] : [],
            subject: $this->subjectLine(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.motivations.update',
            with: [
                'motivation' => $this->motivation,
                'stage' => $this->stage,
                'note' => $this->note,
                'contact' => config('motivations.contact'),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if ($this->stage !== self::STAGE_INVOICED) {
            return [];
        }

        $invoice = $this->motivation->documents()->where('kind', DocumentKinds::INVOICE)->latest()->first();
        if (! $invoice) {
            return [];
        }

        $bytes = $invoice->contents();
        if ($bytes === null) {
            return [];
        }

        return [
            Attachment::fromData(fn () => $bytes, $invoice->original_name ?: 'invoice.pdf')
                ->withMime($invoice->mime ?: 'application/pdf'),
        ];
    }

    public function isInternal(): bool
    {
        return in_array($this->stage, [
            self::STAGE_NEW_SUBMISSION_INTERNAL,
            self::STAGE_PROOF_OF_PAYMENT_INTERNAL,
        ], true);
    }

    private function subjectLine(): string
    {
        $ref = $this->motivation->reference();
        $type = $this->motivation->applicationType?->name ?? 'motivation';

        return match ($this->stage) {
            self::STAGE_SUBMITTED => "We received your {$type} motivation ({$ref})",
            self::STAGE_CHANGES_REQUESTED => "More information needed for {$ref}",
            self::STAGE_INVOICED => "Invoice for your motivation ({$ref})",
            self::STAGE_PAID => "Payment received for {$ref}",
            self::STAGE_READY_FOR_COLLECTION => "Your motivation is ready for collection ({$ref})",
            self::STAGE_SHIPPED => "Your motivation has been shipped ({$ref})",
            self::STAGE_COMPLETED => "Your motivation is complete ({$ref})",
            self::STAGE_NEW_SUBMISSION_INTERNAL => "New motivation submitted — {$ref}",
            self::STAGE_PROOF_OF_PAYMENT_INTERNAL => "Proof of payment uploaded — {$ref}",
            default => "Update on your motivation ({$ref})",
        };
    }
}
