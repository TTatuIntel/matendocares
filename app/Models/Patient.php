<?php


// app/Models/Patient.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'medical_record_number',
        'blood_type',
        'height',
        'current_weight',
        'allergies',
        'chronic_conditions',
        'current_medications',
        'insurance_provider',
        'insurance_policy_number',
        'family_medical_history',
        'activity_level',
        'smoker',
        'alcohol_consumption',
        'dietary_restrictions',
        'emergency_contacts',
        'baseline_heart_rate',
        'baseline_blood_pressure',
        'baseline_temperature'
    ];

    protected $casts = [
        'height' => 'decimal:2',
        'current_weight' => 'decimal:2',
        'emergency_contacts' => 'array',
        'smoker' => 'boolean',
        'alcohol_consumption' => 'integer',
        'baseline_heart_rate' => 'decimal:2',
        'baseline_temperature' => 'decimal:2'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctorPatients()
    {
        return $this->hasMany(DoctorPatient::class);
    }

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_patients')
                    ->withPivot(['relationship_type', 'assigned_at', 'status'])
                    ->withTimestamps();
    }

    public function primaryDoctors()
    {
        return $this->doctors()->wherePivot('relationship_type', 'primary')
                    ->wherePivot('status', 'active');
    }

    public function vitalSigns()
    {
        return $this->hasMany(VitalSign::class);
    }

    public function latestVitalSigns()
    {
        return $this->hasOne(VitalSign::class)->latestOfMany('measured_at');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function activeAlerts()
    {
        return $this->hasMany(Alert::class)->where('status', 'active');
    }

    public function criticalAlerts()
    {
        return $this->activeAlerts()->where('severity', 'critical');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function medications()
    {
        return $this->hasMany(Medication::class);
    }

    public function activeMedications()
    {
        return $this->medications()->where('status', 'active');
    }

    public function medicationLogs()
    {
        return $this->hasMany(MedicationLog::class);
    }

    public function healthMetrics()
    {
        return $this->hasMany(HealthMetric::class);
    }

    public function latestHealthMetric()
    {
        return $this->hasOne(HealthMetric::class)->latestOfMany('date');
    }

    public function emergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    // Scopes
    public function scopeWithCriticalAlerts($query)
    {
        return $query->whereHas('alerts', function($q) {
            $q->where('severity', 'critical')->where('status', 'active');
        });
    }

    public function scopeRecentlyActive($query, $days = 7)
    {
        return $query->whereHas('vitalSigns', function($q) use ($days) {
            $q->where('measured_at', '>=', now()->subDays($days));
        });
    }

    public function scopeByBloodType($query, $bloodType)
    {
        return $query->where('blood_type', $bloodType);
    }

    // Accessors
    public function getBmiAttribute()
    {
        if ($this->height && $this->current_weight) {
            $heightInMeters = $this->height / 100;
            return round($this->current_weight / ($heightInMeters * $heightInMeters), 1);
        }
        return null;
    }

    public function getCurrentHealthScoreAttribute()
    {
        return $this->latestHealthMetric?->health_score ?? null;
    }

    public function getLastActivityAttribute()
    {
        return $this->vitalSigns()->max('measured_at') ??
               $this->user->last_activity;
    }

    public function getHasCriticalAlertsAttribute()
    {
        return $this->criticalAlerts()->exists();
    }

    // Helper Methods
    public function calculateHealthScore()
    {
        $score = 100;
        $factors = [];

        // Get latest vital signs
        $latestVitals = $this->latestVitalSigns;
        if (!$latestVitals) return 50; // No data

        // Blood pressure scoring
        if ($latestVitals->blood_pressure) {
            list($systolic, $diastolic) = explode('/', $latestVitals->blood_pressure);
            if ($systolic >= 140 || $diastolic >= 90) {
                $score -= 15;
                $factors[] = 'High blood pressure';
            }
        }

        // Heart rate scoring
        if ($latestVitals->heart_rate) {
            if ($latestVitals->heart_rate > 100 || $latestVitals->heart_rate < 60) {
                $score -= 10;
                $factors[] = 'Irregular heart rate';
            }
        }

        // BMI scoring
        $bmi = $this->bmi;
        if ($bmi) {
            if ($bmi >= 30) {
                $score -= 15;
                $factors[] = 'Obesity';
            } elseif ($bmi >= 25) {
                $score -= 8;
                $factors[] = 'Overweight';
            }
        }

        // Activity scoring
        $recentActivity = $this->vitalSigns()
            ->where('measured_at', '>=', now()->subDays(7))
            ->count();

        if ($recentActivity < 3) {
            $score -= 10;
            $factors[] = 'Low activity tracking';
        }

        // Critical alerts penalty
        if ($this->hasCriticalAlerts) {
            $score -= 20;
            $factors[] = 'Active critical alerts';
        }

        return max(0, min(100, $score));
    }

    public function getTodaysMedications()
    {
        return $this->medicationLogs()
                    ->whereDate('scheduled_time', today())
                    ->where('status', 'scheduled')
                    ->with('medication')
                    ->orderBy('scheduled_time')
                    ->get();
    }

    public function getUpcomingAppointments($limit = 5)
    {
        return $this->appointments()
                    ->where('scheduled_at', '>', now())
                    ->where('status', 'scheduled')
                    ->orderBy('scheduled_at')
                    ->take($limit)
                    ->with('doctor.user')
                    ->get();
    }

    public function hasDoctor(Doctor $doctor)
    {
        return $this->doctors()->where('doctors.id', $doctor->id)
                    ->wherePivot('status', 'active')
                    ->exists();
    }


public function meds()
{
    // Since meds uses user_id, we need to relate through the user
    return $this->hasManyThrough(
        Meds::class,
        User::class,
        'id',          // Foreign key on users table
        'user_id',     // Foreign key on meds table
        'user_id',     // Local key on patients table
        'id'           // Local key on users table
    );
}



}
