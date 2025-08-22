<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    // Notification types for medical monitoring system
    const TYPE_VITAL_SIGN_ALERT = 'VitalSignAlert';
    const TYPE_PATIENT_UPDATE = 'PatientUpdate';
    const TYPE_DOCUMENT_UPLOAD = 'DocumentUpload';
    const TYPE_APPOINTMENT_REMINDER = 'AppointmentReminder';
    const TYPE_MEDICATION_REMINDER = 'MedicationReminder';
    const TYPE_EMERGENCY_ALERT = 'EmergencyAlert';
    const TYPE_CRITICAL_VALUE = 'CriticalValueAlert';
    const TYPE_SYSTEM_NOTIFICATION = 'SystemNotification';
    const TYPE_COMMENT_NOTIFICATION = 'CommentNotification';
    const TYPE_ACCESS_GRANTED = 'AccessGranted';
    const TYPE_ACCESS_EXPIRED = 'AccessExpired';

    // Priority levels
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    // Notification channels
    const CHANNEL_DATABASE = 'database';
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_SMS = 'sms';
    const CHANNEL_PUSH = 'push';

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'priority',
        'channel',
        'sent',
        'sent_at',
        'metadata',
    ];

    protected $casts = [
        'data' => 'array',
        'metadata' => 'array',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
        'sent' => 'boolean',
    ];

    protected $appends = [
        'is_read',
        'time_ago',
        'priority_label',
        'type_label'
    ];

    /**
     * Get the notifiable entity that the notification belongs to.
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Get the patient related to this notification if applicable
     */
    public function patient()
    {
        if (isset($this->data['patient_id'])) {
            return $this->belongsTo(Patient::class, 'data->patient_id');
        }
        return null;
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query for critical notifications
     */
    public function scopeCritical($query)
    {
        return $query->where('priority', self::PRIORITY_CRITICAL);
    }

    /**
     * Scope a query for medical alerts
     */
    public function scopeMedicalAlerts($query)
    {
        return $query->whereIn('type', [
            self::TYPE_VITAL_SIGN_ALERT,
            self::TYPE_EMERGENCY_ALERT,
            self::TYPE_CRITICAL_VALUE
        ]);
    }

    /**
     * Scope for recent notifications (last 24 hours)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDay());
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
        return $this;
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread()
    {
        if (!is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
        return $this;
    }

    /**
     * Determine if a notification has been read.
     */
    public function read()
    {
        return $this->read_at !== null;
    }

    /**
     * Determine if a notification has not been read.
     */
    public function unread()
    {
        return $this->read_at === null;
    }

    /**
     * Get the is_read attribute
     */
    public function getIsReadAttribute()
    {
        return $this->read_at !== null;
    }

    /**
     * Get human readable time difference
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get priority label with styling
     */
    public function getPriorityLabelAttribute()
    {
        $labels = [
            self::PRIORITY_LOW => ['label' => 'Low', 'class' => 'success'],
            self::PRIORITY_MEDIUM => ['label' => 'Medium', 'class' => 'info'],
            self::PRIORITY_HIGH => ['label' => 'High', 'class' => 'warning'],
            self::PRIORITY_CRITICAL => ['label' => 'Critical', 'class' => 'danger'],
        ];

        return $labels[$this->priority] ?? ['label' => 'Unknown', 'class' => 'secondary'];
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute()
    {
        $types = [
            self::TYPE_VITAL_SIGN_ALERT => 'Vital Sign Alert',
            self::TYPE_PATIENT_UPDATE => 'Patient Update',
            self::TYPE_DOCUMENT_UPLOAD => 'Document Upload',
            self::TYPE_APPOINTMENT_REMINDER => 'Appointment Reminder',
            self::TYPE_MEDICATION_REMINDER => 'Medication Reminder',
            self::TYPE_EMERGENCY_ALERT => 'Emergency Alert',
            self::TYPE_CRITICAL_VALUE => 'Critical Value Alert',
            self::TYPE_SYSTEM_NOTIFICATION => 'System Notification',
            self::TYPE_COMMENT_NOTIFICATION => 'New Comment',
            self::TYPE_ACCESS_GRANTED => 'Access Granted',
            self::TYPE_ACCESS_EXPIRED => 'Access Expired',
        ];

        return $types[$this->type] ?? 'Unknown';
    }

    /**
     * Get notification icon based on type
     */
    public function getIconAttribute()
    {
        $icons = [
            self::TYPE_VITAL_SIGN_ALERT => 'heart-pulse',
            self::TYPE_PATIENT_UPDATE => 'user-check',
            self::TYPE_DOCUMENT_UPLOAD => 'file-upload',
            self::TYPE_APPOINTMENT_REMINDER => 'calendar-clock',
            self::TYPE_MEDICATION_REMINDER => 'pill',
            self::TYPE_EMERGENCY_ALERT => 'alert-triangle',
            self::TYPE_CRITICAL_VALUE => 'alert-circle',
            self::TYPE_SYSTEM_NOTIFICATION => 'info',
            self::TYPE_COMMENT_NOTIFICATION => 'message-circle',
            self::TYPE_ACCESS_GRANTED => 'key',
            self::TYPE_ACCESS_EXPIRED => 'key-off',
        ];

        return $icons[$this->type] ?? 'bell';
    }

    /**
     * Create a vital sign alert notification
     */
    public static function createVitalSignAlert($userId, $patientData, $vitalSignData, $priority = self::PRIORITY_HIGH)
    {
        return self::create([
            'type' => self::TYPE_VITAL_SIGN_ALERT,
            'notifiable_type' => User::class,
            'notifiable_id' => $userId,
            'data' => [
                'patient_id' => $patientData['id'],
                'patient_name' => $patientData['name'],
                'vital_sign_type' => $vitalSignData['type'],
                'value' => $vitalSignData['value'],
                'normal_range' => $vitalSignData['normal_range'] ?? null,
                'message' => $vitalSignData['message'],
                'url' => route('doctor.patient.show', $patientData['id'])
            ],
            'priority' => $priority,
            'channel' => self::CHANNEL_DATABASE
        ]);
    }

    /**
     * Create a patient update notification
     */
    public static function createPatientUpdate($userId, $patientData, $updateType, $message)
    {
        return self::create([
            'type' => self::TYPE_PATIENT_UPDATE,
            'notifiable_type' => User::class,
            'notifiable_id' => $userId,
            'data' => [
                'patient_id' => $patientData['id'],
                'patient_name' => $patientData['name'],
                'update_type' => $updateType,
                'message' => $message,
                'url' => route('doctor.patient.show', $patientData['id'])
            ],
            'priority' => self::PRIORITY_MEDIUM,
            'channel' => self::CHANNEL_DATABASE
        ]);
    }

    /**
     * Get notifications summary for dashboard
     */
    public static function getSummaryForUser($userId)
    {
        $notifications = self::where('notifiable_id', $userId)
            ->where('notifiable_type', User::class);

        return [
            'total' => $notifications->count(),
            'unread' => $notifications->unread()->count(),
            'critical' => $notifications->unread()->critical()->count(),
            'medical_alerts' => $notifications->unread()->medicalAlerts()->count(),
            'recent' => $notifications->recent()->count()
        ];
    }
}