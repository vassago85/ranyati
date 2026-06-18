<?php

namespace App\Mail;

use App\Models\MotivationEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Customer-facing acknowledgement sent to the requester after they submit the
 * motivations enquiry form. Confirms receipt, lists the services they ticked
 * with indicative pricing, and reassures them that the office will be in touch
 * to confirm final pricing — no payment is expected at this stage.
 */
class MotivationEnquiryReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MotivationEnquiry $enquiry,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [new Address($this->enquiry->email, $this->enquiry->name)],
            replyTo: [new Address('info@firearmmotivations.co.za', 'Ranyati Firearm Motivations')],
            subject: 'We have received your motivation enquiry — Ranyati',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.motivation-enquiry-receipt',
        );
    }
}
