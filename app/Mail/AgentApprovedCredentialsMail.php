<?php

namespace App\Mail;

use App\Models\Agent;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgentApprovedCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Agent $agent,
        public string $plainPassword
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $siteName = Setting::getSiteName();
        return new Envelope(
            subject: "[{$siteName}] Pendaftaran Agen Disetujui - Akses Login & Password Sementara",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.agent-approved-credentials',
            with: [
                'agent' => $this->agent,
                'plainPassword' => $this->plainPassword,
                'siteName' => Setting::getSiteName(),
                'loginUrl' => route('login'),
                'profileUrl' => route('admin.profile'),
            ],
        );
    }
}

