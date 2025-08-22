<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentNotification extends Model
{
    protected $fillable = [
        'appointment_id',
        'user_id',
        'type',
        'message',
        'data',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
