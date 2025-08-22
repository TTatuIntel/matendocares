<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Models\AppointmentNotification;
use Illuminate\Support\Facades\Log;

class AppointmentNotificationChannel
{
    public function send($notifiable, Notification $notification)
    {
        try {
            Log::debug('Starting notification storage', [
                'notifiable_id' => $notifiable->id,
                'notification_type' => get_class($notification)
            ]);

            $data = $notification->toArray($notifiable);

            Log::debug('Notification data prepared', $data);

            // MANUAL INSERT to bypass any model issues
            $id = \DB::table('appointment_notifications')->insertGetId([
                'appointment_id' => $data['appointment_id'],
                'user_id' => $notifiable->id,
                'type' => $data['action'],
                'message' => $data['message'],
                'data' => json_encode($data),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::debug('Notification stored manually', ['id' => $id]);

            return true;

        } catch (\Exception $e) {
            Log::error('Channel failed completely', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
