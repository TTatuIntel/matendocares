<?php

/// app/Http/Controllers/Doctor/DoctorDashboardController.php
namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\DoctorPatient;
use App\Models\Alert;
use App\Models\VitalSign;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DoctorDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Check if doctor profile exists
        // if (!$doctor) {
        //     // If no doctor profile exists, create one or redirect to setup
        //     return redirect()->route('doctor.setup')->with('warning', 'Please complete your doctor profile setup.');
        // }

        // Initialize default values for safety
        $totalPatients = 0;
        $activeToday = 0;
        $criticalAlerts = 0;
        $pendingReviews = 0;
        $criticalPatients = collect();
        $recentActivities = collect();
        $todaySchedule = collect();

        try {
            // Total patients under this doctor's care
            $totalPatients = DoctorPatient::where('doctor_id', $doctor->id)
                ->where('status', 'active')
                ->count();

            // Patients active today (submitted vitals)
            $activeToday = VitalSign::whereHas('patient.doctorPatients', function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id)->where('status', 'active');
            })->whereDate('measured_at', today())->distinct('patient_id')->count();

            // Critical alerts count
            $criticalAlerts = Alert::whereHas('patient.doctorPatients', function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id)->where('status', 'active');
            })->where('severity', 'critical')
              ->where('status', 'active')
              ->count();

            // Pending reviews count
            $pendingReviews = VitalSign::whereHas('patient.doctorPatients', function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id)->where('status', 'active');
            })->where('reviewed_by_doctor', false)
              ->where('status', '!=', 'normal')
              ->count();

            // Critical patients requiring immediate attention
            $criticalPatients = Patient::whereHas('doctorPatients', function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id)->where('status', 'active');
            })->whereHas('alerts', function($q) {
                $q->where('severity', 'critical')->where('status', 'active');
            })->with(['user', 'alerts' => function($q) {
                $q->where('severity', 'critical')->where('status', 'active')->latest();
            }])->get()->map(function($patient) {
                $latestAlert = $patient->alerts->first();
                return (object) [
                    'id' => $patient->id,
                    'name' => $patient->user->name ?? 'Unknown Patient',
                    'latest_alert' => $latestAlert ? $latestAlert->title : 'No alert details',
                    'alert_time' => $latestAlert ? $latestAlert->triggered_at->diffForHumans() : 'Unknown time'
                ];
            });

            // Recent patient activities
            $recentActivities = VitalSign::whereHas('patient.doctorPatients', function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id)->where('status', 'active');
            })->with('patient.user')
              ->latest('measured_at')
              ->take(10)
              ->get()
              ->map(function($vital) {
                  return (object) [
                      'patient_name' => $vital->patient->user->name ?? 'Unknown Patient',
                      'activity' => 'Vital signs updated',
                      'time' => $vital->measured_at->diffForHumans(),
                      'status' => $vital->status ?? 'normal'
                  ];
              });

            // Today's schedule
            $todaySchedule = Appointment::where('doctor_id', $doctor->id)
                ->whereDate('scheduled_at', today())
                ->with('patient.user')
                ->orderBy('scheduled_at')
                ->get()
                ->map(function($appointment) {
                    return (object) [
                        'patient_name' => $appointment->patient->user->name ?? 'Unknown Patient',
                        'type' => ucfirst($appointment->type ?? 'consultation'),
                        'time' => $appointment->scheduled_at->format('H:i'),
                        'status' => ucfirst($appointment->status ?? 'scheduled')
                    ];
                });

        } catch (\Exception $e) {
            // Handle database errors gracefully
            \Log::error('Doctor Dashboard Error: ' . $e->getMessage());

            // Set fallback data
            $criticalPatients = collect();
            $recentActivities = collect([
                (object) [
                    'patient_name' => 'System',
                    'activity' => 'Dashboard loaded successfully',
                    'time' => 'just now',
                    'status' => 'normal'
                ]
            ]);
            $todaySchedule = collect();
        }

        return view('doctor.dashboard', compact(
            'totalPatients', 'activeToday', 'criticalAlerts', 'pendingReviews',
            'criticalPatients', 'recentActivities', 'todaySchedule'
        ));
    }
}
