<?php

namespace App\Mail;

use App\Models\MotivationEnquiry;
use App\Models\Setting;
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

        $recipients = Setting::parseEmailList(Setting::get('notification_email'));
        if (empty($recipients)) {
            $recipients = ['info@firearmmotivations.co.za'];
        }

        return new Envelope(
            to: $recipients,
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
