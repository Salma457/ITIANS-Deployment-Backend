<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationRequestRejected extends Mailable
{
    use Queueable, SerializesModels;

    protected JobApplication $application;

    public function __construct(JobApplication $application)
    {
        $this->application = $application;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We Appreciate Your Application!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration_request_rejected', // تأكد من وجود هذا الملف
            with: [
                'user' => $this->application->itian->user,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

