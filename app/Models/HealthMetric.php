<?php



// app/Models/HealthMetric.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthMetric extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'patient_id',
        'date',
        'health_score',
        'cardiovascular_score',
        'respiratory_score',
        'mental_health_score',
        'activity_score',
        'sleep_score',
        'nutrition_score',
        'risk_factors',
        'recommendations',
        'trend',
        'calculation_metadata',
        'calculated_at',
        'calculated_by',
        'is_active'
    ];

    protected $casts = [
        'date' => 'date',
        'health_score' => 'decimal:2',
        'cardiovascular_score' => 'decimal:2',
        'respiratory_score' => 'decimal:2',
        'mental_health_score' => 'decimal:2',
        'activity_score' => 'decimal:2',
        'sleep_score' => 'decimal:2',
        'nutrition_score' => 'decimal:2',
        'risk_factors' => 'array',
        'recommendations' => 'array',
        'calculation_metadata' => 'array',
        'calculated_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function calculatedBy()
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }
}