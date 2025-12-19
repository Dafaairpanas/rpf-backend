<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $contactMessage
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Contact Message from ' . $this->contactMessage->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-message',
            with: [
                'contact' => $this->contactMessage,
            ],
        );
    }
}
