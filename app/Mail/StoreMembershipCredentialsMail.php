<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StoreMembershipCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * The plaintext password is intentionally synced (no ShouldQueue) so it
     * never stays serialized in a queue backend. Only this one-time message
     * carries it.
     */
    public function __construct(
        public string $storeName,
        public string $inviterName,
        public string $memberName,
        public string $memberEmail,
        public string $password,
        public string $loginUrl,
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.store_membership_credentials.subject', ['store' => $this->storeName]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.store-membership-credentials',
        );
    }
}