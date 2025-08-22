<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PatientVitalsUpdated extends Notification
{
    use Queueable;

    public $patient;
    public $vital;

    public function __construct($patient, $vital)
    {
        $this->patient = $patient;
        $this->vital = $vital;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'patient_vitals',
            'patient_id' => $this->patient->id,
            'vital_id' => $this->vital->id,
            'message' => "New vitals from {$this->patient->user->name}",
            'status' => $this->vital->status,
            'url' => "/doctor/patients/{$this->patient->id}/vitals/{$this->vital->id}",
            'is_critical' => $this->vital->status === 'critical',
            'timestamp' => now()->toISOString()
        ];
    }

// Add this method to set the user_id when creating the notification
    public function toArray($notifiable)
    {
        return array_merge($this->toDatabase($notifiable), [
            'user_id' => $this->patient->user->id
        ]);
    }
}
