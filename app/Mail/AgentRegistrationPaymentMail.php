<?php

namespace App\Mail;

use App\Models\AgentApplication;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgentRegistrationPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public AgentApplication $application
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $siteName = Setting::getSiteName();
        return new Envelope(
            subject: "[{$siteName}] Kode Pembayaran Pendaftaran Agen - {$this->application->payment_code}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.agent-registration-payment',
            with: [
                'application' => $this->application,
                'siteName' => Setting::getSiteName(),
                'registrationFee' => (float) ($this->application->payment_amount ?: Setting::get('agent_registration_fee', 100000)),
                'qrisImage' => Setting::getQrisUrl(),
                'merchantName' => Setting::get('qris_merchant_name', 'YProperti Official QRIS'),
                'bcaAccount' => Setting::getBcaAccount(),
                'otherBankAccount' => Setting::getOtherBankAccount(),
                'instructions' => Setting::get('payment_instructions'),
                'invoiceUrl' => route('agents.join.invoice', $this->application->payment_code),
            ],
        );
    }
}

