<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempLink extends Model
{
    protected $fillable = ['patient_id', 'token', 'expires_at'];

    public function patient()
    {
        return $this->belongsTo(\App\Models\Patient::class);
    }
}
