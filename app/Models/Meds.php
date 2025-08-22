<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Meds extends Model
{
    use HasFactory;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'meds';

    protected $fillable = [
        'user_id',
        'patient_id',
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
        'status'
    ];

    protected $casts = [
        'times' => 'array',
        'start_date' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
