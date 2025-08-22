<?php
// app/Models/DoctorDocument.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DoctorDocument extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'doctor_id',
        'user_id',
        'title',
        'category',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
            'file_content', // Add this
        'description'
    ];

    // In DoctorDocument.php
protected $hidden = [
    'file_content' // Exclude from JSON responses
];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
        });
    }

    // Relationship to User
public function user()
{
    return $this->belongsTo(User::class);
}

// Relationship to Doctor remains the same
public function doctor()
{
    return $this->belongsTo(Doctor::class);
}
}