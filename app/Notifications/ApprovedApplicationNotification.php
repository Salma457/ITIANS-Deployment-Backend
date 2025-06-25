<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApprovedApplicationNotification extends Notification
{
    use Queueable;

    protected $application;

    public function __construct($application)
    {
        $this->application = $application;
    }

    public function via($notifiable)
    {
        return ['database'];
        return ['mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'You have been approved for a job',
            'message' => 'You have been accepted for the job: ' . $this->application->job->title,
            'job_id' => $this->application->job->id,
        ];
    }
}
