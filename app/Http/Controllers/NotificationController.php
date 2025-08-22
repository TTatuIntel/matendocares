<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\VitalSign;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Display notifications dashboard for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get notifications based on user role
        $query = $user->notifications()->latest();

        // Filter by priority if requested
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        // Filter by read status
        if ($request->has('status')) {
            if ($request->status === 'unread') {
                $query->unread();
            } elseif ($request->status === 'read') {
                $query->read();
            }
        }

        $notifications = $query->paginate(20);

        // Get notification counts for dashboard
        $counts = $this->getNotificationCounts();

        return view('notifications.index', compact('notifications', 'counts'));
    }

    /**
     * Get unread notifications for real-time updates
     */
    public function getUnread()
    {
        $user = Auth::user();

        $unreadNotifications = $user->notifications()
            ->unread()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'priority' => $notification->priority,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'created_at_iso' => $notification->created_at->toISOString(),
                ];
            });

        return response()->json([
            'notifications' => $unreadNotifications,
            'count' => $unreadNotifications->count()
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();

        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'counts' => $this->getNotificationCounts()
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();

        $updated = $user->notifications()
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "All notifications marked as read",
            'updated_count' => $updated,
            'counts' => $this->getNotificationCounts()
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
            'counts' => $this->getNotificationCounts()
        ]);
    }

    /**
     * Get notification counts by priority and status
     */
    public function getNotificationCounts()
    {
        $user = Auth::user();

        return [
            'total' => $user->notifications()->count(),
            'unread' => $user->notifications()->unread()->count(),
            'critical' => $user->notifications()->unread()->where('priority', 'critical')->count(),
            'high' => $user->notifications()->unread()->where('priority', 'high')->count(),
            'medium' => $user->notifications()->unread()->where('priority', 'medium')->count(),
            'low' => $user->notifications()->unread()->where('priority', 'low')->count(),
            'read_today' => $user->notifications()
                ->read()
                ->whereDate('read_at', today())
                ->count(),
        ];
    }

    /**
     * Get notifications for medical dashboard (doctors)
     */
    public function getDashboardNotifications()
    {
        $user = Auth::user();

        if (!$user->hasRole('doctor')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get critical medical notifications
        $criticalNotifications = $user->notifications()
            ->unread()
            ->where('priority', 'critical')
            ->whereIn('type', [
                'VitalSignAlert',
                'EmergencyAlert',
                'CriticalValueAlert'
            ])
            ->latest()
            ->limit(10)
            ->get();

        // Get recent patient updates
        $recentUpdates = $user->notifications()
            ->whereIn('type', [
                'PatientUpdate',
                'VitalSignUpdate',
                'DocumentUpload'
            ])
            ->latest()
            ->limit(15)
            ->get();

        return response()->json([
            'critical' => $criticalNotifications,
            'recent_updates' => $recentUpdates,
            'counts' => $this->getNotificationCounts()
        ]);
    }

    /**
     * Get patient-specific notifications for doctors
     */
    public function getPatientNotifications($patientId)
    {
        $user = Auth::user();

        if (!$user->hasRole('doctor')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verify doctor has access to this patient
        $patient = Patient::findOrFail($patientId);
        if (!$user->doctor->patients()->where('patients.id', $patientId)->exists()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $notifications = $user->notifications()
            ->whereJsonContains('data->patient_id', $patientId)
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    /**
     * Create vital sign alert notification
     */
    public function createVitalSignAlert($patientId, $vitalSign, $alertType = 'high')
    {
        $patient = Patient::with('user')->findOrFail($patientId);

        // Get all doctors assigned to this patient
        $doctors = $patient->doctors()->with('user')->get();

        foreach ($doctors as $doctor) {
            Notification::create([
                'type' => 'VitalSignAlert',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $doctor->user->id,
                'data' => [
                    'patient_id' => $patientId,
                    'patient_name' => $patient->user->name,
                    'vital_sign_type' => $vitalSign->type ?? 'unknown',
                    'value' => $vitalSign->value ?? 'N/A',
                    'alert_type' => $alertType,
                    'message' => "Critical vital sign alert for {$patient->user->name}",
                    'url' => route('doctor.patient.show', $patientId)
                ],
                'priority' => $alertType === 'critical' ? 'critical' : 'high',
                'channel' => 'database'
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get real-time notification polling endpoint
     */
    public function poll()
    {
        $user = Auth::user();
        $lastCheck = request('last_check');

        $query = $user->notifications()->unread();

        if ($lastCheck) {
            $query->where('created_at', '>', Carbon::parse($lastCheck));
        }

        $newNotifications = $query->latest()->get();

        return response()->json([
            'notifications' => $newNotifications,
            'count' => $newNotifications->count(),
            'total_unread' => $user->notifications()->unread()->count(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Bulk mark notifications as read
     */
    public function bulkMarkAsRead(Request $request)
    {
        $user = Auth::user();
        $notificationIds = $request->input('notification_ids', []);

        if (empty($notificationIds)) {
            return response()->json(['error' => 'No notifications selected'], 400);
        }

        $updated = $user->notifications()
            ->whereIn('id', $notificationIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "Marked {$updated} notifications as read",
            'counts' => $this->getNotificationCounts()
        ]);
    }

    /**
     * Get notification preferences
     */
    public function getPreferences()
    {
        $user = Auth::user();

        // Get user's notification preferences (you might want to create a preferences table)
        $preferences = [
            'email_notifications' => $user->email_notifications ?? true,
            'sms_notifications' => $user->sms_notifications ?? false,
            'push_notifications' => $user->push_notifications ?? true,
            'critical_only' => $user->critical_notifications_only ?? false,
            'quiet_hours_start' => $user->quiet_hours_start ?? '22:00',
            'quiet_hours_end' => $user->quiet_hours_end ?? '07:00'
        ];

        return response()->json($preferences);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'critical_only' => 'boolean',
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i'
        ]);

        $user->update($request->only([
            'email_notifications',
            'sms_notifications',
            'push_notifications',
            'critical_notifications_only',
            'quiet_hours_start',
            'quiet_hours_end'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated'
        ]);
    }




/**
 * Get count of unread appointment notifications
 */
public function unreadCount()
{
    $count = auth()->user()->unreadNotifications()
                ->where('type', 'App\Notifications\DoctorAppointmentNotification')
                ->count();

    return response()->json(['count' => $count]);
}

/**
 * Mark appointment notification as read
 */
public function markAppointmentAsRead($id)
{
    $notification = auth()->user()->notifications()
                      ->where('id', $id)
                      ->where('type', 'App\Notifications\DoctorAppointmentNotification')
                      ->firstOrFail();

    $notification->markAsRead();

    return response()->json([
        'success' => true,
        'unread_count' => auth()->user()
                            ->unreadNotifications()
                            ->where('type', 'App\Notifications\DoctorAppointmentNotification')
                            ->count()
    ]);
}
}
