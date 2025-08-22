<?php


// app/Models/MedicationLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'medication_id',
        'patient_id',
        'scheduled_time',
        'taken_at',
        'status',
        'notes',
        'skip_reason',
        'side_effects_reported',
        'confirmed_by',
        'dosage_taken',
        'dosage_notes'
    ];

    protected $casts = [
        'scheduled_time' => 'datetime',
        'taken_at' => 'datetime',
        'side_effects_reported' => 'array'
    ];

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}