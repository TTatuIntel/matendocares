<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Appointment;

class DoctorAppointmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment, public string $action) {}

    public function via($notifiable)
    {
        return ['database', 'mail']; // Use both channels
    }

    public function toDatabase($notifiable)
    {
        return [
            'appointment_id' => $this->appointment->id,
            'action' => $this->action,
            'message' => "Your appointment has been {$this->action}",
            'scheduled_at' => $this->appointment->scheduled_at->toDateTimeString(),
            'doctor_name' => $this->appointment->doctor->user->name,
            'is_telemedicine' => $this->appointment->is_telemedicine,
            'meeting_link' => $this->appointment->meeting_link
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
