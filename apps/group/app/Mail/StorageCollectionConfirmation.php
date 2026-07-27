<?php

namespace App\Mail;

use App\Models\StorageItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Self-storage collection confirmation. Sent to the client email on file
 * for the storage agreement when a firearm is checked out. Estates skip
 * this — executor collection details live on the release custody event.
 */
class StorageCollectionConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public StorageItem $item,
        public string $collectedByName,
        public string $collectedByIdNumber,
        public string $feeAmount,
    ) {}

    public function envelope(): Envelope
    {
        $to = $this->item->agreement?->email;

        return new Envelope(
            to: $to ? [$to] : [],
            subject: 'Firearm Collection Confirmation — '.$this->item->register_ref,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.storage-collection-confirmation',
        );
    }
}
