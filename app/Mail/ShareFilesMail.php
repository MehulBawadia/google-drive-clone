<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShareFilesMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sharedWithUser;

    public $sharedByUser;

    public $content;

    /**
     * Create a new message instance.
     */
    public function __construct($sharedWithUser, $sharedByUser, $content)
    {
        $this->sharedWithUser = $sharedWithUser;
        $this->sharedByUser = $sharedByUser;
        $this->content = $content;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name').': Shared files',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.share-file',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
