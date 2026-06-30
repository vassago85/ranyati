<?php

namespace App\Mail;

use App\Models\FirearmApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FirearmStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public FirearmApplication $application,
        public ?string $previousStatus = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'SAPS status update — '.$this->application->displayName(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.firearm-status-changed',
        );
    }
}
