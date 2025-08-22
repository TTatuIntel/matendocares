<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TempAccess extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'token',
        'doctor_name',
        'doctor_specialty',
        'doctor_facility',
        'doctor_phone',
        'doctor_verified',
        'expires_at',
        'accessed_at',
        'verified_at',
        'revoked_at',
        'is_active',
        'generated_by',
        'permissions',
        'notes',
        'access_reason',
        'verification_code',
        'access_count',
        'revocation_reason'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accessed_at' => 'datetime',
        'verified_at' => 'datetime',
        'revoked_at' => 'datetime',
        'doctor_verified' => 'boolean',
        'is_active' => 'boolean',
        'permissions' => 'array',
        'access_count' => 'integer'
    ];

    protected $hidden = [
        'verification_code'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = self::generateUniqueToken();
            }

            if (empty($model->verification_code)) {
                $model->verification_code = self::generateCleanCode();
            } else {
                $model->verification_code = self::normalizeCode($model->verification_code);
            }

            if (is_null($model->access_count)) {
                $model->access_count = 0;
            }
            if (is_null($model->doctor_verified)) {
                $model->doctor_verified = false;
            }
            if (is_null($model->is_active)) {
                $model->is_active = true;
            }
            if (empty($model->permissions)) {
                $model->permissions = $model->getDefaultPermissions();
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('verification_code')) {
                $model->verification_code = self::normalizeCode($model->verification_code);
            }
        });
    }

    /* ===========================
       Relationships
       =========================== */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /* ===========================
       Scopes
       =========================== */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('expires_at', '>', now());
    }

    public function scopeVerified($query)
    {
        return $query->where('doctor_verified', true);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /* ===========================
       Status Helpers
       =========================== */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function isActive()
    {
        return $this->is_active && !$this->isExpired();
    }

    public function canAccess()
    {
        return $this->isActive() && $this->doctor_verified;
    }

    public function isRevoked()
    {
        return !$this->is_active && $this->revoked_at !== null;
    }

    /* ===========================
       Action Methods
       =========================== */
    public function markAccessed()
    {
        $this->increment('access_count');
        $this->update(['accessed_at' => now()]);
        return $this;
    }

    public function verify($doctorData)
    {
        $this->update([
            'doctor_name' => $doctorData['doctor_name'],
            'doctor_specialty' => $doctorData['doctor_specialty'],
            'doctor_facility' => $doctorData['doctor_facility'],
            'doctor_phone' => $doctorData['doctor_phone'],
            'doctor_verified' => true,
            'verified_at' => now()
        ]);
        return $this;
    }

    public function revoke($reason = null)
    {
        $this->update([
            'is_active' => false,
            'revoked_at' => now(),
            'revocation_reason' => $reason
        ]);
        return $this;
    }

    public function extend($days = 1)
    {
        if ($this->isActive()) {
            $this->update([
                'expires_at' => $this->expires_at->addDays($days)
            ]);
        }
        return $this;
    }

    /* ===========================
       Attributes
       =========================== */
    public function getTimeRemainingAttribute()
    {
        if ($this->isExpired()) {
            return 'Expired';
        }
        return $this->expires_at->diffForHumans();
    }

    public function getStatusAttribute()
    {
        if ($this->isRevoked()) {
            return 'revoked';
        }
        if ($this->isExpired()) {
            return 'expired';
        }
        if (!$this->doctor_verified) {
            return 'pending_verification';
        }
        if ($this->isActive()) {
            return 'active';
        }
        return 'inactive';
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'active' => 'Active',
            'expired' => 'Expired',
            'revoked' => 'Revoked',
            'pending_verification' => 'Pending Verification',
            'inactive' => 'Inactive',
            default => 'Unknown'
        };
    }

    public function getFormattedVerificationCodeAttribute()
    {
        if (!$this->verification_code) {
            return null;
        }
        $clean = self::normalizeCode($this->verification_code);
        return strlen($clean) === 8
            ? substr($clean, 0, 4) . '-' . substr($clean, 4)
            : $clean;
    }

    /* ===========================
       Permissions
       =========================== */
    public function getDefaultPermissions()
    {
        return [
            'view_vitals' => true,
            'upload_documents' => true,
            'prescribe_medications' => true,
            'add_health_tips' => true,
            'view_medical_history' => true,
            'export_data' => false,
            'view_appointments' => true,
            'emergency_contact' => false
        ];
    }

    public function hasPermission($permission)
    {
        $permissions = $this->permissions ?? $this->getDefaultPermissions();
        return isset($permissions[$permission]) ? $permissions[$permission] : false;
    }

    public function grantPermission($permission)
    {
        $permissions = $this->permissions ?? $this->getDefaultPermissions();
        $permissions[$permission] = true;
        $this->update(['permissions' => $permissions]);
        return $this;
    }

    public function revokePermission($permission)
    {
        $permissions = $this->permissions ?? $this->getDefaultPermissions();
        $permissions[$permission] = false;
        $this->update(['permissions' => $permissions]);
        return $this;
    }

    /* ===========================
       Verification Code Logic
       =========================== */
    public function setVerificationCodeAttribute($value)
    {
        $this->attributes['verification_code'] = self::normalizeCode($value);
    }

    public function generateNewVerificationCode()
    {
        $this->verification_code = self::generateCleanCode();
        $this->save();
        return $this->verification_code;
    }

    public function verificationCodeMatches($inputCode)
    {
        $normInput = self::normalizeCode($inputCode);
        $stored = self::normalizeCode($this->verification_code);

        if (strlen($normInput) !== 8 || strlen($stored) !== 8) {
            return false;
        }

        return hash_equals($stored, $normInput);
    }

    /* ===========================
       Static Helpers
       =========================== */
    public static function generateUniqueToken()
    {
        do {
            $token = Str::random(64);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    private static function normalizeCode($value): string
    {
        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $value));
        if (strlen($clean) > 8) {
            $clean = substr($clean, 0, 8);
        }
        return $clean;
    }

    private static function generateCleanCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('verification_code', $code)->exists());

        return $code;
    }

    /* ===========================
       Statistics & Reports
       =========================== */
    public function getAccessStatistics()
    {
        return [
            'total_accesses' => $this->access_count,
            'first_access' => $this->accessed_at?->format('Y-m-d H:i:s'),
            'last_access' => $this->accessed_at?->format('Y-m-d H:i:s'),
            'verification_time' => $this->verified_at?->diffForHumans(),
            'days_active' => $this->verified_at ? $this->verified_at->diffInDays(now()) : 0,
            'expires_in' => $this->expires_at->diffForHumans(),
            'status' => $this->status_label
        ];
    }

    public function toArray()
    {
        $array = parent::toArray();
        
        // Remove sensitive data from array output
        unset($array['verification_code']);
        
        // Add computed attributes
        $array['status'] = $this->status;
        $array['status_label'] = $this->status_label;
        $array['time_remaining'] = $this->time_remaining;
        $array['formatted_verification_code'] = $this->formatted_verification_code;
        
        return $array;
    }

    /* ===========================
       Query Helpers
       =========================== */
    public static function findByToken($token)
    {
        return self::where('token', $token)->first();
    }

    public static function findActiveByToken($token)
    {
        return self::where('token', $token)->active()->first();
    }

    public static function getActiveForPatient($patientId)
    {
        return self::forPatient($patientId)->active()->latest('created_at')->first();
    }

    public static function revokeAllForPatient($patientId, $reason = null)
    {
        return self::forPatient($patientId)->where('is_active', true)->update([
            'is_active' => false,
            'revoked_at' => now(),
            'revocation_reason' => $reason ?? 'Batch revocation'
        ]);
    }
}