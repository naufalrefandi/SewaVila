<?php

namespace App\Mail;

use App\Models\PropertySubmission;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PropertySubmissionApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public PropertySubmission $submission
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $siteName = Setting::getSiteName();
        return new Envelope(
            subject: "[{$siteName}] Titip Properti Diverifikasi & Disetujui - {$this->submission->payment_code}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.property-submission-approved',
            with: [
                'submission' => $this->submission,
                'siteName' => Setting::getSiteName(),
                'invoiceUrl' => $this->submission->payment_code
                    ? route('titip-properti.invoice', $this->submission->payment_code)
                    : route('titip-properti'),
            ],
        );
    }
}

