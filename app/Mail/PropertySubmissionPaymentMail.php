<?php

namespace App\Mail;

use App\Models\PropertySubmission;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PropertySubmissionPaymentMail extends Mailable
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
            subject: "[{$siteName}] Kode Pembayaran Titip Properti - {$this->submission->payment_code}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.property-submission-payment',
            with: [
                'submission' => $this->submission,
                'siteName' => Setting::getSiteName(),
                'submissionFee' => (float) ($this->submission->fee_amount ?: Setting::get('submission_fee', 50000)),
                'qrisImage' => Setting::getQrisUrl(),
                'merchantName' => Setting::get('qris_merchant_name', 'YProperti Official QRIS'),
                'bcaAccount' => Setting::getBcaAccount(),
                'otherBankAccount' => Setting::getOtherBankAccount(),
                'instructions' => Setting::get('payment_instructions'),
                'invoiceUrl' => route('titip-properti.invoice', $this->submission->payment_code),
            ],
        );
    }
}

