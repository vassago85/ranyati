<?php

namespace App\Mail;

use App\Models\ArmsEnquiry;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewArmsEnquiry extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ArmsEnquiry $enquiry,
    ) {}

    public function envelope(): Envelope
    {
        $listing = $this->enquiry->listing;
        $subject = "Enquiry: {$listing->make} {$listing->model} ({$listing->calibre}) — from {$this->enquiry->name}";
        $recipient = Setting::get('arms_enquiry_email', 'info@firearmstorage.co.za');

        return new Envelope(
            to: array_map('trim', explode(',', $recipient)),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-arms-enquiry',
        );
    }
}
