<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Notifications\DoctorAppointmentNotification;
use Illuminate\Support\Facades\DB;


class AppointmentController extends Controller
{
    public function index()
    {
        $doctorId = Auth::id();

        $data = [
            'pendingRequests' => Appointment::with(['patient.user'])
                ->where('user_id', $doctorId)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get(),

            'todaySchedule' => Appointment::with(['patient.user'])
                ->where('user_id', $doctorId)
                ->whereIn('status', ['confirmed', 'scheduled'])
                ->whereDate('scheduled_at', Carbon::today())
                ->orderBy('scheduled_at')
                ->get(),

            'upcomingAppointments' => Appointment::with(['patient.user'])
                ->where('user_id', $doctorId)
                ->whereIn('status', ['confirmed', 'scheduled'])
                ->where('scheduled_at', '>', Carbon::now())
                ->orderBy('scheduled_at')
                ->get(),

            'patients' => \App\Models\Patient::with('user')
                ->whereHas('appointments', fn($q) => $q->where('user_id', $doctorId))
                ->get()
        ];

        return view('doctor.appointments', $data);
    }

//     public function store(Request $request)
// {
//     $validated = $request->validate([
//         'patient_id' => 'required|exists:patients,id',
//         'date' => 'required|date',
//         'time' => 'required',
//         'type' => 'required|in:consultation,follow_up,emergency,routine_checkup',
//         'duration_minutes' => 'required|integer|min:15|max:120',
//         'notes' => 'nullable|string',
//         'is_telemedicine' => 'required|boolean',
//         'meeting_link' => 'nullable|url|required_if:is_telemedicine,true',
//         'priority' => 'nullable|in:low,normal,high,urgent'
//     ]);

//     $appointment = Appointment::create([
//         'user_id' => Auth::id(),
//         'patient_id' => $validated['patient_id'],
//         'doctor_id' => Auth::id(),
//         'title' => 'Scheduled Appointment',
//         'type' => $validated['type'],
//         'scheduled_at' => Carbon::parse($validated['date'] . ' ' . $validated['time']),
//         'duration_minutes' => $validated['duration_minutes'],
//         'status' => 'pending', // Changed from 'scheduled' to 'pending'
//         'notes' => $validated['notes'] ?? null,
//         'is_telemedicine' => $validated['is_telemedicine'],
//         'meeting_link' => $validated['is_telemedicine'] ? $validated['meeting_link'] : null,
//         'priority' => $validated['priority'] ?? 'normal',
//         'requested_at' => now()
//     ]);

// // $appointment->patient->user->notify(
// //         new DoctorAppointmentNotification($appointment, 'scheduled', [
// //             'initiated_by' => 'doctor',
// //             'priority' => $appointment->priority
// //         ])
// //     );

// try {
//         // Right after creating the appointment
// \Log::debug("Appointment created", [
//     'id' => $appointment->id,
//     'patient_user_id' => $appointment->patient->user->id
// ]);

// // When sending notification
// $result = $appointment->patient->user->notify(
//     new DoctorAppointmentNotification($appointment, 'scheduled')
// );

// \Log::debug("Notification result", ['result' => $result]);

//     } catch (\Exception $e) {
//         \Log::error('Notification failed: '.$e->getMessage());
//     }

//     return response()->json([
//         'success' => true,
//         'message' => 'Appointment scheduled successfully',
//         'data' => $appointment
//     ]);
// }


public function store(Request $request)
{
    DB::beginTransaction();

    try {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'date' => 'required|date',
            'time' => 'required',
            'type' => 'required|in:consultation,follow_up,emergency,routine_checkup',
            'duration_minutes' => 'required|integer|min:15|max:120',
            'notes' => 'nullable|string',
            'is_telemedicine' => 'required|boolean',
            'meeting_link' => 'nullable|url|required_if:is_telemedicine,true',
            'priority' => 'nullable|in:low,normal,high,urgent'
        ]);

        // Create appointment
        $appointment = Appointment::create([
            'user_id' => Auth::id(),
            'patient_id' => $validated['patient_id'],
            'doctor_id' => Auth::id(),
            'title' => 'Scheduled Appointment',
            'type' => $validated['type'],
            'scheduled_at' => Carbon::parse($validated['date'] . ' ' . $validated['time']),
            'duration_minutes' => $validated['duration_minutes'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'is_telemedicine' => $validated['is_telemedicine'],
            'meeting_link' => $validated['is_telemedicine'] ? $validated['meeting_link'] : null,
            'priority' => $validated['priority'] ?? 'normal',
            'requested_at' => now()
        ]);

        // Load relationships
        $patient = $appointment->patient()->with('user')->first();

        \Log::debug('Pre-notification check', [
            'patient_exists' => (bool)$patient,
            'user_exists' => $patient ? (bool)$patient->user : false
        ]);

        // DIRECT DATABASE INSERT VERIFICATION
        $testInsert = \DB::table('appointment_notifications')->insert([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->user->id,
            'type' => 'test',
            'message' => 'Test notification',
            'data' => json_encode(['test' => true]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!$testInsert) {
            throw new \Exception("Direct database insert failed");
        }

        \Log::debug('Test insert successful', ['rows' => $testInsert]);

        // Now try the actual notification
        $notification = new \App\Notifications\DoctorAppointmentNotification($appointment, 'scheduled');
        $patient->user->notify($notification);

        // VERIFY WITH RAW QUERY
        $stored = \DB::table('appointment_notifications')
            ->where('appointment_id', $appointment->id)
            ->first();

        if (!$stored) {
            throw new \Exception("Final verification failed - no record found");
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Appointment scheduled successfully',
            'data' => $appointment,
            'notification_id' => $stored->id
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("FINAL ERROR: " . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Appointment failed: ' . $e->getMessage(),
            'debug' => [
                'patient_id' => $request->patient_id,
                'user_id' => $patient->user->id ?? null,
                'appointment_id' => $appointment->id ?? null
            ]
        ], 500);
    }
}

    public function accept(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            // 'type' => 'required|in:consultation,follow_up,emergency,routine',
        'type' => 'required|in:consultation,follow_up,emergency,routine_checkup', // Match DB ENUM

            'duration_minutes' => 'required|integer|min:15|max:120',
            'notes' => 'nullable|string',
            'is_telemedicine' => 'required|boolean',
            'meeting_link' => 'nullable|url|required_if:is_telemedicine,true'
        ]);

        $appointment = Appointment::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $appointment->update([
            'scheduled_at' => Carbon::parse($validated['date'] . ' ' . $validated['time']),
            'type' => $validated['type'],
            'duration_minutes' => $validated['duration_minutes'],
            'notes' => $validated['notes'] ?? $appointment->notes,
            'is_telemedicine' => $validated['is_telemedicine'],
            'meeting_link' => $validated['is_telemedicine'] ? $validated['meeting_link'] : null,
            'status' => 'confirmed',
            'responded_at' => now()
        ]);

$appointment->patient->user->notify(
        new DoctorAppointmentNotification($appointment, 'confirmed', [
            'initiated_by' => 'doctor',
            'previous_status' => 'pending'
        ])
    );
        return response()->json([
            'success' => true,
            'message' => 'Appointment accepted successfully',
            'data' => $appointment
        ]);
    }

    public function reject($id)
    {
        $appointment = Appointment::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $appointment->update([
            'status' => 'cancelled',
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
            'cancellation_reason' => 'Doctor rejected appointment request',
            'responded_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment request rejected'
        ]);
    }

    public function reschedule(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'reschedule_reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:15|max:120',
            'is_telemedicine' => 'nullable|boolean',
            'meeting_link' => 'nullable|url|required_if:is_telemedicine,true'
        ]);

        $appointment = Appointment::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $updateData = [
            'scheduled_at' => Carbon::parse($validated['date'] . ' ' . $validated['time']),
            'status' => 'confirmed' // Reset status if it was pending
        ];

        if (isset($validated['notes'])) {
            $updateData['notes'] = $validated['notes'];
        }

        if (isset($validated['duration_minutes'])) {
            $updateData['duration_minutes'] = $validated['duration_minutes'];
        }

        if (isset($validated['is_telemedicine'])) {
            $updateData['is_telemedicine'] = $validated['is_telemedicine'];
            $updateData['meeting_link'] = $validated['is_telemedicine'] ? ($validated['meeting_link'] ?? null) : null;
        }

        if ($validated['reschedule_reason']) {
            $updateData['notes'] = $appointment->notes
                ? $appointment->notes . "\nRescheduled: " . $validated['reschedule_reason']
                : "Rescheduled: " . $validated['reschedule_reason'];
        }

        $appointment->update($updateData);

$appointment->patient->user->notify(
        new DoctorAppointmentNotification($appointment, 'rescheduled', [
            'initiated_by' => 'doctor',
            'reason' => $validated['reschedule_reason'] ?? null,
            'original_date' => $appointment->getOriginal('scheduled_at')
        ])
    );

        return response()->json([
            'success' => true,
            'message' => 'Appointment rescheduled successfully',
            'data' => $appointment
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10'
        ]);

        $appointment = Appointment::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $appointment->update([
            'status' => 'cancelled',
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['reason']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled successfully'
        ]);
    }

    public function start($id)
    {
        $appointment = Appointment::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $appointment->update([
            'status' => 'in_progress',
            'started_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment started',
            'data' => $appointment
        ]);
    }

    public function complete(Request $request, $id)
    {
        $validated = $request->validate([
            'diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'prescriptions' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $appointment = Appointment::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $appointment->update([
            'status' => 'completed',
            'completed_at' => now(),
            'diagnosis' => $validated['diagnosis'] ?? null,
            'treatment_plan' => $validated['treatment_plan'] ?? null,
            'prescriptions' => $validated['prescriptions'] ?? null,
            'notes' => $validated['notes'] ?? $appointment->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment completed successfully',
            'data' => $appointment
        ]);
    }
}
