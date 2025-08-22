<?php

// app/Models/MedicalRecord.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'record_type',
        'data',
        'notes',
        'severity',
        'requires_attention',
        'recorded_at',
        'recorded_by',
        'status',
        'attachments'
    ];

    protected $casts = [
        'data' => 'array',
        'requires_attention' => 'boolean',
        'recorded_at' => 'datetime',
        'attachments' => 'array'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}