<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    /**
     * Display the appointments page with pending requests and upcoming appointments
     */
    public function index()
{
    $patient = Auth::user()->patient;

    if (!$patient) {
        return redirect()->back()->with('error', 'Patient profile not found');
    }

    $pendingRequests = Appointment::with('doctor')
        ->where('patient_id', $patient->id)
        ->where('status', 'pending')
        ->orderBy('created_at', 'desc')
        ->get();

    $upcomingAppointments = Appointment::where('patient_id', $patient->id)
        ->whereIn('status', ['scheduled', 'confirmed'])
        ->where('scheduled_at', '>', now())
        ->orderBy('scheduled_at')
        ->get();

    $completedAppointments = Appointment::where('patient_id', $patient->id)
        ->where('status', 'completed')
        ->count();

    $lastCompletedAppointment = Appointment::where('patient_id', $patient->id)
        ->where('status', 'completed')
        ->orderBy('scheduled_at', 'desc')
        ->first();

    $uniqueDoctorsCount = Appointment::where('patient_id', $patient->id)
        ->distinct('doctor_id')
        ->count('doctor_id');

    $doctors = User::where('role', 'doctor')
        ->get(['id', 'name'])
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'specialty' => 'General Physician' // Default value
            ];
        });

    // Calculate days until next appointment
    $nextAppointment = $upcomingAppointments->first();
    $daysUntilNext = $nextAppointment ? now()->diffInDays($nextAppointment->scheduled_at) : null;
    $nextAppointmentDate = $nextAppointment ? $nextAppointment->scheduled_at->format('M d, Y') : null;

    return view('patient.appointments', compact(
        'pendingRequests',
        'upcomingAppointments',
        'doctors',
        'completedAppointments',
        'lastCompletedAppointment',
        'uniqueDoctorsCount',
        'daysUntilNext',
        'nextAppointmentDate'
    ));
}
    /**
     * Store a new appointment request
     */
public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id,role,doctor', // Ensure user is a doctor
            'description' => 'required|string|min:10|max:1000',
            'priority' => 'required|in:low,normal,high,urgent'
        ]);

        $patient = Auth::user()->patient;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found'
            ], 400);
        }

        // Create appointment without checking doctors table
        $appointment = Appointment::create([
            'user_id' => $validated['user_id'],    // The doctor's user_id
            'patient_id' => $patient->id,
            'doctor_id' => $validated['user_id'],  // Same as user_id (since doctors table is empty)
            'title' => 'Appointment Request',
            'description' => $validated['description'],
            'type' => 'consultation',
            'scheduled_at' => $this->calculateScheduledTime($validated['priority']),
            'duration_minutes' => 30,
            'status' => 'pending',
            'priority' => $validated['priority'],
            'is_telemedicine' => false,
            'requested_at' => now(),
        ]);




        return response()->json([
            'success' => true,
            'message' => 'Appointment request submitted successfully!',
            'data' => $appointment
        ]);

    } catch (\Exception $e) {
        Log::error('Appointment request error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'An error occurred. Please try again.',
            'error' => $e->getMessage()
        ], 500);
    }
}

private function calculateScheduledTime($priority)
{
    switch ($priority) {
        case 'urgent': return now()->addHours(24);
        case 'high': return now()->addDays(5);
        case 'normal': return now()->addDays(14);
        case 'low': return now()->addDays(28);
        default: return now()->addDays(14);
    }
}

    /**
     * Cancel an appointment
     */
    public function cancel(Request $request, $id)
    {
        try {
            $patient = Auth::user()->patient;

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient profile not found'
                ], 400);
            }

            $appointment = Appointment::where('id', $id)
                ->where('patient_id', $patient->id)
                ->first();

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Appointment not found'
                ], 404);
            }

            $appointment->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
                'cancellation_reason' => $request->input('reason', 'Patient cancelled')
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Appointment cancelled successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Cancel appointment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while cancelling the appointment'
            ], 500);
        }
    }
}
