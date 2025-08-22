<?php


// app/Models/VitalSign.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VitalSign extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'systolic_bp',
        'diastolic_bp',
        'blood_pressure',
        'heart_rate',
        'resting_heart_rate',
        'temperature',
        'temperature_unit',
        'weight',
        'weight_unit',
        'height',
        'height_unit',
        'oxygen_saturation',
        'blood_glucose',
        'bmi',
        'respiratory_rate',
        'steps',
        'sleep_hours',
        'mood',
        'energy_level',
        'pain_level',
        'symptoms',
        'notes',
        'entry_method',
        'device_type',
        'device_metadata',
        'measured_at',
        'recorded_by',
        'status',
        'reviewed_by_doctor',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'systolic_bp' => 'decimal:2',
        'diastolic_bp' => 'decimal:2',
        'heart_rate' => 'decimal:2',
        'resting_heart_rate' => 'decimal:2',
        'temperature' => 'decimal:2',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'oxygen_saturation' => 'decimal:2',
        'blood_glucose' => 'decimal:2',
        'bmi' => 'decimal:2',
        'sleep_hours' => 'decimal:2',
        'energy_level' => 'integer',
        'pain_level' => 'integer',
        'symptoms' => 'array',
        'device_metadata' => 'array',
        'measured_at' => 'datetime',
        'reviewed_by_doctor' => 'boolean',
        'reviewed_at' => 'datetime'
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(Doctor::class, 'reviewed_by');
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class, 'triggered_by');
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('measured_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('measured_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCritical($query)
    {
        return $query->where('status', 'critical');
    }

    public function scopeUnreviewed($query)
    {
        return $query->where('reviewed_by_doctor', false);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('measured_at', [$startDate, $endDate]);
    }

    // Accessors
    public function getBloodPressureStatusAttribute()
    {
        if (!$this->blood_pressure) return 'unknown';

        list($systolic, $diastolic) = explode('/', $this->blood_pressure);
        $systolic = (int)$systolic;
        $diastolic = (int)$diastolic;

        if ($systolic >= 180 || $diastolic >= 120) return 'critical';
        if ($systolic >= 140 || $diastolic >= 90) return 'warning';
        if ($systolic < 90 || $diastolic < 60) return 'warning';
        return 'normal';
    }

    public function getHeartRateStatusAttribute()
    {
        if (!$this->heart_rate) return 'unknown';

        if ($this->heart_rate > 120 || $this->heart_rate < 50) return 'critical';
        if ($this->heart_rate > 100 || $this->heart_rate < 60) return 'warning';
        return 'normal';
    }

    public function getTemperatureStatusAttribute()
    {
        if (!$this->temperature) return 'unknown';

        if ($this->temperature >= 103 || $this->temperature <= 95) return 'critical';
        if ($this->temperature >= 100.4 || $this->temperature <= 97) return 'warning';
        return 'normal';
    }

    public function getOxygenStatusAttribute()
    {
        if (!$this->oxygen_saturation) return 'unknown';

        if ($this->oxygen_saturation < 90) return 'critical';
        if ($this->oxygen_saturation < 95) return 'warning';
        return 'normal';
    }

    public function getOverallRiskLevelAttribute()
    {
        $statuses = [
            $this->blood_pressure_status,
            $this->heart_rate_status,
            $this->temperature_status,
            $this->oxygen_status
        ];

        if (in_array('critical', $statuses)) return 'critical';
        if (in_array('warning', $statuses)) return 'warning';
        return 'normal';
    }

    // Helper Methods
    public function markAsReviewed($reviewedBy = null)
    {
        $this->update([
            'reviewed_by_doctor' => true,
            'reviewed_by' => $reviewedBy ?? auth()->user()->doctor?->id,
            'reviewed_at' => now()
        ]);
    }

    public function calculateBMI()
    {
        if ($this->height && $this->weight) {
            $heightInMeters = $this->height / 100;
            $this->bmi = round($this->weight / ($heightInMeters * $heightInMeters), 1);
            $this->save();
        }
    }

    public static function getAverageForPatient($patientId, $field, $days = 30)
    {
        return static::where('patient_id', $patientId)
                    ->where('measured_at', '>=', now()->subDays($days))
                    ->avg($field);
    }


}
