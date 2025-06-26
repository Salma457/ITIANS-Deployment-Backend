<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RoundEndedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $messageText;
    public $companyName;

    public function __construct($messageText, $companyName)
    {
        $this->messageText = $messageText;
        $this->companyName = $companyName;
    }

    public function build()
    {
        return $this->subject('Training Round Ended')
                    ->view('emails.round_ended');
    }
}

