<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorMed extends Model
{
    use HasFactory;

    protected $table = 'doctor_meds';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'doctor_id',
        'name',
        'generic_name',
        'dosage',
        'frequency',
        'times',
        'start_date',
        'prescribed_by',
        'purpose',
        'instructions',
        'refills',
        'status',
        'health_tips'
    ];

    protected $casts = [
        'start_date' => 'date',
        'times' => 'array'
    ];

    // Relationship to patient
    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship to doctor
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    // Accessor for times display
    public function getTimesDisplayAttribute()
    {
        return $this->times ? implode(', ', json_decode($this->times, true)) : '';
    }
}
