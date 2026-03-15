<?php

namespace App\Mail;

use App\Models\MotivationEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewMotivationEnquiry extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MotivationEnquiry $enquiry,
    ) {}

    public function envelope(): Envelope
    {
        $subject = 'New Motivation Enquiry from ' . $this->enquiry->name;

        if ($this->enquiry->membership_number) {
            $subject .= ' (NRAPA ' . $this->enquiry->membership_number . ')';
        }

        return new Envelope(
            to: ['info@firearmmotivations.co.za'],
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-motivation-enquiry',
        );
    }
}
