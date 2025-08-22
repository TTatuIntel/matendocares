<?php



// app/Models/Medication.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medication extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'prescribed_by',
        'medication_name',
        'dosage',
        'frequency',
        'frequency_type',
        'frequency_times',
        'schedule_times',
        'instructions',
        'side_effects',
        'contraindications',
        'start_date',
        'end_date',
        'duration_days',
        'status',
        'discontinuation_reason',
        'discontinued_by',
        'discontinued_at',
        'requires_monitoring',
        'monitoring_instructions',
        'priority',
        'reminders_enabled'
    ];

    protected $casts = [
        'schedule_times' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'discontinued_at' => 'datetime',
        'requires_monitoring' => 'boolean',
        'reminders_enabled' => 'array'
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function prescribedBy()
    {
        return $this->belongsTo(Doctor::class, 'prescribed_by');
    }

    public function discontinuedBy()
    {
        return $this->belongsTo(User::class, 'discontinued_by');
    }

    public function medicationLogs()
    {
        return $this->hasMany(MedicationLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCurrent($query)
    {
        return $query->where('status', 'active')
                    ->where('start_date', '<=', today())
                    ->where(function($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', today());
                    });
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', today());
    }

    public function scopeRequiringMonitoring($query)
    {
        return $query->where('requires_monitoring', true);
    }

    // Accessors
    public function getIsActiveAttribute()
    {
        return $this->status === 'active' && 
               $this->start_date <= today() && 
               ($this->end_date === null || $this->end_date >= today());
    }

    public function getIsExpiredAttribute()
    {
        return $this->end_date && $this->end_date < today();
    }

    public function getDaysRemainingAttribute()
    {
        if (!$this->end_date) return null;
        return max(0, today()->diffInDays($this->end_date, false));
    }

    public function getComplianceRateAttribute()
    {
        $totalLogs = $this->medicationLogs()->count();
        if ($totalLogs === 0) return 0;
        
        $takenLogs = $this->medicationLogs()->where('status', 'taken')->count();
        return round(($takenLogs / $totalLogs) * 100, 1);
    }

    // Helper Methods
    public function discontinue($reason = null, $discontinuedBy = null)
    {
        $this->update([
            'status' => 'discontinued',
            'discontinuation_reason' => $reason,
            'discontinued_by' => $discontinuedBy ?? auth()->id(),
            'discontinued_at' => now()
        ]);
    }

    public function generateSchedule($fromDate = null, $toDate = null)
    {
        $fromDate = $fromDate ?? $this->start_date;
        $toDate = $toDate ?? ($this->end_date ?? now()->addDays(30));

        $schedule = [];
        $currentDate = $fromDate;

        while ($currentDate <= $toDate) {
            if ($this->schedule_times) {
                foreach ($this->schedule_times as $time) {
                    $scheduledTime = $currentDate->copy()->setTimeFromTimeString($time);
                    
                    // Only add future times
                    if ($scheduledTime > now()) {
                        $schedule[] = [
                            'medication_id' => $this->id,
                            'patient_id' => $this->patient_id,
                            'scheduled_time' => $scheduledTime,
                            'status' => 'scheduled'
                        ];
                    }
                }
            }
            
            $currentDate = $currentDate->addDay();
        }

        return $schedule;
    }
}
