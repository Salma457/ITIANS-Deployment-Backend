<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovedForInterviewMail extends Mailable
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
            subject: 'You Are Approved for the Next Step!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.approved', // Blade file path: resources/views/emails/approved.blade.php
            with: [
                'user' => $this->application->itian->user, // تمرير المستخدم فقط
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
