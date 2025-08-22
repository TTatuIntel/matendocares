<?php


// app/Models/Alert.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alert extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'triggered_by',
        'alert_type',
        'severity',
        'title',
        'message',
        'data',
        'trigger_conditions',
        'auto_generated',
        'triggered_at',
        'status',
        'acknowledged_by',
        'acknowledged_at',
        'acknowledgment_notes',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
        'notifications_sent',
        'requires_doctor_attention',
        'requires_emergency_contact',
        'escalation_rules',
        'escalated_at'
    ];

    protected $casts = [
        'data' => 'array',
        'trigger_conditions' => 'array',
        'auto_generated' => 'boolean',
        'triggered_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'notifications_sent' => 'array',
        'requires_doctor_attention' => 'boolean',
        'requires_emergency_contact' => 'boolean',
        'escalation_rules' => 'array',
        'escalated_at' => 'datetime'
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function vitalSign()
    {
        return $this->belongsTo(VitalSign::class, 'triggered_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeEmergency($query)
    {
        return $query->where('severity', 'emergency');
    }

    public function scopeUnacknowledged($query)
    {
        return $query->whereNull('acknowledged_at');
    }

    public function scopeRequiringDoctorAttention($query)
    {
        return $query->where('requires_doctor_attention', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    // Accessors
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function getIsAcknowledgedAttribute()
    {
        return !is_null($this->acknowledged_at);
    }

    public function getIsResolvedAttribute()
    {
        return !is_null($this->resolved_at);
    }

    public function getPriorityScoreAttribute()
    {
        $scores = [
            'info' => 1,
            'warning' => 2,
            'critical' => 3,
            'emergency' => 4
        ];

        return $scores[$this->severity] ?? 1;
    }

    public function getTimeElapsedAttribute()
    {
        return $this->triggered_at->diffForHumans();
    }

    // Helper Methods
    public function acknowledge($user = null, $notes = null)
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_by' => $user?->id ?? auth()->id(),
            'acknowledged_at' => now(),
            'acknowledgment_notes' => $notes
        ]);
    }

    public function resolve($user = null, $notes = null)
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $user?->id ?? auth()->id(),
            'resolved_at' => now(),
            'resolution_notes' => $notes
        ]);
    }

    public function markAsFalseAlarm($user = null, $notes = null)
    {
        $this->update([
            'status' => 'false_alarm',
            'resolved_by' => $user?->id ?? auth()->id(),
            'resolved_at' => now(),
            'resolution_notes' => $notes
        ]);
    }

    public function escalate()
    {
        $this->update([
            'escalated_at' => now(),
            'requires_emergency_contact' => true
        ]);

        // Trigger escalation notifications
        // event(new AlertEscalated($this));
    }
}
