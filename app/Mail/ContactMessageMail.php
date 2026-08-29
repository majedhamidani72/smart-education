<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly array $contact)
    {
    }

    public function envelope(): Envelope
    {
        $replyTo = filled($this->contact['email'] ?? null)
            ? [new Address($this->contact['email'], $this->contact['name'])]
            : [];

        return new Envelope(
            subject: 'پیام جدید سایت درسکا: '.$this->contact['subject'],
            replyTo: $replyTo,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.contact-message');
    }
}
