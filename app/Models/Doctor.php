<?php

// app/Models/Doctor.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;



class Doctor extends Authenticatable  // Changed from Model to Authenticatable
{



use Notifiable;
    use HasFactory, HasUuids, SoftDeletes;

    protected $guard = 'doctor';

    protected $fillable = [
        'user_id',
        'license_number',
        'specialization',
        'qualifications',
        'hospital_affiliation',
        'years_experience',
        'bio',
        'available_hours',
        'verification_status',
        'verified_at',
        'verified_by',
        'consultation_fee',
        'accepts_emergency_calls'
    ];

    protected $casts = [
        'available_hours' => 'array',
        'verified_at' => 'datetime',
        'consultation_fee' => 'decimal:2',
        'accepts_emergency_calls' => 'boolean'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function doctorPatients()
    {
        return $this->hasMany(DoctorPatient::class);
    }

    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'doctor_patients')
                    ->withPivot(['relationship_type', 'assigned_at', 'status'])
                    ->withTimestamps();
    }

    public function activePatients()
    {
        return $this->patients()->wherePivot('status', 'active');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function prescribedMedications()
    {
        return $this->hasMany(Medication::class, 'prescribed_by');
    }

    public function reviewedVitalSigns()
    {
        return $this->hasMany(VitalSign::class, 'reviewed_by');
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeBySpecialization($query, $specialization)
    {
        return $query->where('specialization', $specialization);
    }

    public function scopeAcceptingEmergencies($query)
    {
        return $query->where('accepts_emergency_calls', true);
    }

    public function scopeAvailableNow($query)
    {
        $currentHour = now()->hour;
        return $query->whereJsonContains('available_hours', $currentHour);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return 'Dr. ' . $this->user->name;
    }

    public function getIsVerifiedAttribute()
    {
        return $this->verification_status === 'verified';
    }

    public function getActivePatientsCountAttribute()
    {
        return $this->activePatients()->count();
    }

    // Helper Methods
    public function verify($verifiedBy = null)
    {
        $this->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $verifiedBy ?? auth()->id()
        ]);
    }

    public function assignPatient(Patient $patient, $relationshipType = 'primary')
    {
        return $this->doctorPatients()->firstOrCreate([
            'patient_id' => $patient->id,
            'relationship_type' => $relationshipType
        ], [
            'assigned_at' => now(),
            'assigned_by' => auth()->id(),
            'status' => 'active'
        ]);
    }

    public function getTodaysAppointments()
    {
        return $this->appointments()
                    ->whereDate('scheduled_at', today())
                    ->orderBy('scheduled_at')
                    ->with('patient.user')
                    ->get();
    }

    public function getCriticalPatients()
    {
        return $this->activePatients()
                    ->whereHas('alerts', function($q) {
                        $q->where('severity', 'critical')->where('status', 'active');
                    })
                    ->with('alerts')
                    ->get();
    }

public function documents()
{
    return $this->hasMany(DoctorDocument::class);
}
}
