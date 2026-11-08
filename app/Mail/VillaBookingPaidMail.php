<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\VillaBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VillaBookingPaidMail extends Mailable
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
            subject: "[{$siteName}] Pembayaran Berhasil! Voucher Sewa Vila - {$this->booking->booking_code}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.villa-booking-paid',
            with: [
                'booking' => $this->booking,
                'siteName' => Setting::getSiteName(),
                'voucherUrl' => route('villas.voucher', $this->booking->booking_code),
            ],
        );
    }
}

