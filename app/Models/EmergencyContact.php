<?php


// app/Models/EmergencyContact.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencyContact extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'name',
        'relationship',
        'phone_primary',
        'phone_secondary',
        'email',
        'address',
        'priority_order',
        'can_make_medical_decisions',
        'notify_for_emergencies',
        'notify_for_appointments',
        'notify_for_medications',
        'notification_preferences',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'can_make_medical_decisions' => 'boolean',
        'notify_for_emergencies' => 'boolean',
        'notify_for_appointments' => 'boolean',
        'notify_for_medications' => 'boolean',
        'notification_preferences' => 'array',
        'is_active' => 'boolean'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority_order');
    }
}
