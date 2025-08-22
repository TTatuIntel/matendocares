<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Appointment;

class AppointmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;
    protected $action;

    public function __construct(Appointment $appointment, string $action)
    {
        $this->appointment = $appointment;
        $this->action = $action;
    }

    public function via($notifiable)
    {
        return ['database', 'mail']; // Send via both database and email
    }

    public function toMail($notifiable)
    {
        $subject = "Appointment {$this->action}";
        $message = (new MailMessage)
                    ->subject($subject)
                    ->line("Your appointment has been {$this->action}.")
                    ->line("Date: " . $this->appointment->scheduled_at->format('F j, Y, g:i a'))
                    ->line("Doctor: " . $this->appointment->doctor->user->name);

        if ($this->appointment->is_telemedicine) {
            $message->line("Meeting Link: " . $this->appointment->meeting_link);
        }

        return $message;
    }

    public function toArray($notifiable)
    {
        return [
            'appointment_id' => $this->appointment->id,
            'action' => $this->action,
            'scheduled_at' => $this->appointment->scheduled_at,
            'message' => "Your appointment has been {$this->action} for " . $this->appointment->scheduled_at->format('F j, Y, g:i a'),
            'doctor_name' => $this->appointment->doctor->user->name,
            'is_telemedicine' => $this->appointment->is_telemedicine,
            'meeting_link' => $this->appointment->meeting_link
        ];
    }
}
