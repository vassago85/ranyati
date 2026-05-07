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

        $recipients = Setting::parseEmailList(Setting::get('arms_enquiry_email'));
        if (empty($recipients)) {
            $recipients = ['info@firearmstorage.co.za'];
        }

        return new Envelope(
            to: $recipients,
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
