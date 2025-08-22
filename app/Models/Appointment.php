<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',          // Doctor's user_id
        'patient_id',       // Patient's id
        'doctor_id',       // Optional - if you have separate doctors table
        'title',
        'description',
        'type',
        'scheduled_at',
        'duration_minutes',
        'status',
        'requested_at',
        'responded_at',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'notes',
        'vital_signs_taken',
        'diagnosis',
        'treatment_plan',
        'prescriptions',
        'next_appointment_recommended',
        'priority',
        'is_telemedicine',
        'meeting_link',
        'reminders_sent'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'requested_at' => 'datetime',
        'responded_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'next_appointment_recommended' => 'datetime',
        'notes' => 'array',
        'vital_signs_taken' => 'array',
        'reminders_sent' => 'array',
        'is_telemedicine' => 'boolean',
        'duration_minutes' => 'integer'
    ];

    // Status constants for better code readability
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    // Type constants
    public const TYPE_CONSULTATION = 'consultation';
    public const TYPE_FOLLOW_UP = 'follow_up';
    public const TYPE_EMERGENCY = 'emergency';
    public const TYPE_ROUTINE = 'routine';

    // Priority constants
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    /**
     * Relationship to the patient
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withDefault([
            'name' => 'Unknown Patient'
        ]);
    }

    /**
     * Relationship to the user (doctor) through user_id
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Unknown Doctor'
        ]);
    }

    /**
     * Relationship to the doctor (if using separate doctors table)
     */
    // public function doctor(): BelongsTo
    // {
    //     return $this->belongsTo(Doctor::class)->withDefault([
    //         'name' => 'Unknown Doctor'
    //     ]);
    // }

    /**
     * Scope for pending appointments
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for confirmed appointments
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope for upcoming appointments
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now());
    }

    /**
     * Scope for today's appointments
     */
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    /**
     * Scope for doctor's appointments
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('user_id', $doctorId);
    }

    /**
     * Check if appointment is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if appointment is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if appointment is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if appointment is virtual/telemedicine
     */
    public function isVirtual(): bool
    {
        return $this->is_telemedicine;
    }

// In your Appointment model
public function doctor()
{
    return $this->belongsTo(User::class, 'user_id'); // or 'doctor_id' depending on which you use
}
}
