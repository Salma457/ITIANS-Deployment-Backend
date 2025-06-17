<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\ItianRegistrationRequest;

class RegistrationRequestReviewed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected ItianRegistrationRequest $request
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your ITian Registration Request Has Been Reviewed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration_request_reviewed',
            with: [
                'status' => $this->request->status,
                'user' => $this->request->user,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
