<?php

// app/Models/User.php - IMMEDIATE FIX
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{


    // REMOVE HasApiTokens for now since Sanctum is not installed
    use HasFactory, Notifiable, HasUuids, SoftDeletes;
use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'last_activity'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'last_activity' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function uploadedDocuments()
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeRecentlyActive($query, $hours = 24)
    {
        return $query->where('last_activity', '>=', Carbon::now()->subHours($hours));
    }

    // Accessors & Mutators
    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function getIsOnlineAttribute()
    {
        return $this->last_activity && $this->last_activity->diffInMinutes() < 5;
    }

    // Helper Methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isDoctor()
    {
        return $this->role === 'doctor';
    }

    public function isPatient()
    {
        return $this->role === 'patient';
    }

    public function updateLastActivity()
    {
        $this->update(['last_activity' => now()]);
    }

    public function getUnreadNotificationsCount()
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

// In User model
public function meds()
{
    return $this->hasMany(Meds::class);
}

// Add this if you need to query only patient medications
public function patientMeds()
{
    return $this->morphMany(Meds::class, 'user')->where('role', 'patient');
}

protected static function booted()
    {
        static::updated(function ($user) {
            if ($user->role === 'doctor' && $user->wasChanged('role')) {
                Doctor::firstOrCreate(
                    ['user_id' => $user->id],
                    ['specialization' => 'General']
                );
            }
        });

        static::created(function ($user) {
            if ($user->role === 'doctor') {
                Doctor::create([
                    'user_id' => $user->id,
                    'specialization' => 'General'
                ]);
            }
        });
    }



public function doctorDocuments()
{
    return $this->hasMany(DoctorDocument::class, 'doctor_id');
}

public function patientDocuments()
{
    return $this->hasMany(DoctorDocument::class, 'user_id');
}
public function appointmentNotifications()
{
    return $this->hasMany(AppointmentNotification::class, 'user_id');
}

public function unreadAppointmentNotifications()
{
    return $this->appointmentNotifications()->whereNull('read_at');
}

}


