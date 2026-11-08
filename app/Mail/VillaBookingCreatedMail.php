<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\VillaBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VillaBookingCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public VillaBooking $booking
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $siteName = Setting::getSiteName();

        return new Envelope(
            subject: "[{$siteName}] Tagihan Sewa Vila & Pembayaran QRIS - {$this->booking->booking_code}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.villa-booking-created',
            with: [
                'booking' => $this->booking,
                'siteName' => Setting::getSiteName(),
                'paymentUrl' => route('villas.payment', $this->booking->booking_code),
                'qrisImage' => $this->booking->getQrisCodeUrl(),
                'merchantName' => Setting::get('qris_merchant_name', Setting::getSiteName() . ' Official QRIS'),
                'bcaAccount' => Setting::getBcaAccount(),
                'otherBankAccount' => Setting::getOtherBankAccount(),
                'instructions' => Setting::get('payment_instructions'),
            ],
        );
    }
}

