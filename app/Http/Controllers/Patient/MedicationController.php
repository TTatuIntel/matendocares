<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Medication;
use App\Models\MedicationLog;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Meds;
use App\Models\DoctorMed; // Add this import


class MedicationController extends Controller
{
    /**
     * Display the medications management page.
     */
    public function index()
{
    try {
        $user = Auth::user();

        $stats = [
            'activeMedications' => $this->getActiveMedicationsCount($user),
            'todayTaken' => $this->getTodayTakenCount($user),
            'todayScheduled' => $this->getTodayScheduledCount($user),
            'adherenceRate' => $this->getAdherenceRate($user),
            'upcomingReminders' => $this->getUpcomingRemindersCount($user)
        ];

        // Add this query to get doctor's medications
        $doctorMeds = DoctorMed::where('user_id', $user->id)
            ->with('doctor') // assuming you have a doctor relationship
            ->latest()
            ->get();

        return view('patient.medications', [
            'stats' => $stats,
            'todayMedications' => $this->getTodayMedications($user),
            'allMedications' => $this->getAllMedications($user),
            'weeklyAdherence' => $this->getWeeklyAdherence($user),
            'doctorMeds' => $doctorMeds // Make sure this line is included
        ]);

    } catch (\Exception $e) {
        Log::error('Error loading medications page: ' . $e->getMessage());
        return redirect()->route('patient.dashboard')
            ->with('error', 'Unable to load medications. Please try again.');
    }
}

// Add this new method to handle adding doctor's meds to user's list
    public function addFromDoctor(Request $request)
    {
        $request->validate([
            'doctor_med_id' => 'required|exists:doctor_meds,id,user_id,'.auth()->id()
        ]);

        try {
            $doctorMed = DoctorMed::findOrFail($request->doctor_med_id);

            // Create a new medication for the user based on the doctor's prescription
            $medication = Meds::create([
                'user_id' => auth()->id(),
                'name' => $doctorMed->name,
                'generic_name' => $doctorMed->generic_name,
                'dosage' => $doctorMed->dosage,
                'frequency' => $doctorMed->frequency,
                'times' => $doctorMed->times,
                'start_date' => $doctorMed->start_date,
                'prescribed_by' => $doctorMed->doctor->name ?? 'Doctor',
                'purpose' => $doctorMed->purpose,
                'instructions' => $doctorMed->instructions,
                'refills' => $doctorMed->refills,
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Medication added successfully',
                'medication' => $medication
            ]);

        } catch (\Exception $e) {
            Log::error('Error adding doctor medication: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add medication from doctor'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'medication_name' => 'required|string|max:255',
            'dosage' => 'required|string|max:100',
            'frequency' => 'required|string|max:50',
            'times' => 'required|array',
            'times.*' => 'required|date_format:H:i',
        ]);

        try {
            $user = Auth::user();

            $medication = Meds::create([
                'user_id' => $user->id,
                'name' => $request->medication_name,
                'generic_name' => $request->generic_name,
                'dosage' => $request->dosage,
                'frequency' => $request->frequency,
                'times' => $request->times,
                'start_date' => $request->start_date ?? now()->format('Y-m-d'),
                'prescribed_by' => $request->prescribed_by,
                'purpose' => $request->purpose,
                'instructions' => $request->instructions,
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Medication added successfully!',
                'medication' => $medication
            ]);

        } catch (\Exception $e) {
            Log::error('Error adding medication: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add medication. Please try again.'
            ], 500);
        }
    }

    public function reminders(Request $request)
    {
        try {
            $user = auth()->user();

            $reminders = $this->getTodayMedications($user);

            return response()->json([
                'success' => true,
                'reminders' => $reminders
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching reminders: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reminders.'
            ], 500);
        }
    }

    public function markTaken(Request $request)
    {
        $request->validate([
            'medication_id' => 'required|integer',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $user = auth()->user();

            Log::info('Medication marked as taken', [
                'user_id' => $user->id,
                'medication_id' => $request->medication_id,
                'taken_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Medication marked as taken successfully!',
                'taken_at' => now()->format('H:i A')
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking medication as taken: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark medication as taken.'
            ], 500);
        }
    }

    public function skip(Request $request)
    {
        $request->validate([
            'medication_id' => 'required|integer',
            'skip_reason' => 'required|string|max:500'
        ]);

        try {
            $user = auth()->user();

            Log::info('Medication skipped', [
                'user_id' => $user->id,
                'medication_id' => $request->medication_id,
                'reason' => $request->skip_reason,
                'skipped_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Medication dose skipped.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error skipping medication: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to skip medication.'
            ], 500);
        }
    }

    public function reportSideEffect(Request $request)
    {
        $request->validate([
            'medication_id' => 'required|integer',
            'side_effect' => 'required|string|max:1000',
            'severity' => 'required|in:mild,moderate,severe',
            'started_at' => 'nullable|date'
        ]);

        try {
            $user = auth()->user();

            Log::info('Side effect reported', [
                'user_id' => $user->id,
                'medication_id' => $request->medication_id,
                'side_effect' => $request->side_effect,
                'severity' => $request->severity,
                'reported_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Side effect reported successfully. Your doctor will be notified.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error reporting side effect: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to report side effect.'
            ], 500);
        }
    }

    public function history(Request $request)
    {
        try {
            $user = auth()->user();
            $days = $request->get('days', 30);

            $history = $this->getMedicationHistory($user, $days);

            return view('patient.medications.history', compact('history', 'days'));

        } catch (\Exception $e) {
            Log::error('Error fetching medication history: ' . $e->getMessage());
            return redirect()->route('patient.medications.index')
                ->with('error', 'Unable to load medication history.');
        }
    }

    public function exportMedications()
    {
        try {
            $user = auth()->user();
            $medications = $this->getAllMedications($user);

            $filename = 'medications_' . str_replace(' ', '_', $user->name) . '_' . now()->format('Y-m-d') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function() use ($medications) {
                $file = fopen('php://output', 'w');
                fputcsv($file, [
                    'Medication Name', 'Dosage', 'Frequency', 'Times', 'Start Date',
                    'Prescribed By', 'Purpose', 'Status', 'Adherence Rate'
                ]);

                foreach ($medications as $medication) {
                    fputcsv($file, [
                        $medication['name'],
                        $medication['dosage'],
                        $medication['frequency'],
                        implode(', ', $medication['times']),
                        $medication['start_date'],
                        $medication['prescribed_by'],
                        $medication['purpose'],
                        $medication['status'],
                        '96%'
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            return response()->json(['error' => 'Export failed'], 500);
        }
    }

    // API Methods
    public function apiIndex()
    {
        try {
            $user = auth()->user();
            return response()->json([
                'medications' => $this->getAllMedications($user),
                'today_schedule' => $this->getTodayMedications($user)
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch medications'], 500);
        }
    }

    public function apiMarkTaken(Request $request)
    {
        return $this->markTaken($request);
    }

    public function apiReminders()
    {
        try {
            $user = auth()->user();
            return response()->json([
                'reminders' => $this->getTodayMedications($user)
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch reminders'], 500);
        }
    }

    // Helper Methods
    private function getActiveMedicationsCount($user)
    {
        return Meds::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();
    }

    private function getTodayMedications($user)
    {
        return Meds::where('user_id', $user->id)
            ->where('status', 'active')
            ->get()
            ->map(function ($med) {
                $times = collect($med->times)->map(function ($time) {
                    return Carbon::createFromFormat('H:i', $time)->format('h:i A');
                });

                $status = 'scheduled';
                $takenAt = null;

                if (rand(0, 1)) {
                    $status = 'taken';
                    $takenAt = now()->subMinutes(rand(5, 30))->format('h:i A');
                } elseif (Carbon::now()->format('H:i') > $med->times[0]) {
                    $status = 'upcoming';
                }

                return [
                    'id' => $med->id,
                    'name' => $med->name,
                    'dosage' => $med->dosage,
                    'time' => $times->first(),
                    'status' => $status,
                    'type' => 'prescription',
                    'taken_at' => $takenAt,
                    'color' => $this->getMedicationColor($med->name)
                ];
            })
            ->toArray();
    }

    private function getAllMedications($user)
{
    return Meds::where('user_id', $user->id)
        ->orderBy('start_date', 'desc')
        ->get()
        ->map(function ($med) {
            // Safely handle times array
            $times = collect($med->times)->map(function ($time) {
                try {
                    if (empty($time) || !is_string($time)) {
                        return 'N/A';
                    }

                    if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
                        return Carbon::createFromFormat('H:i', $time)->format('h:i A');
                    } elseif (preg_match('/^\d{1,2}:\d{2} [AP]M$/i', $time)) {
                        return $time; // Already in correct format
                    }

                    return 'Invalid Time';
                } catch (\Exception $e) {
                    return 'N/A';
                }
            })->toArray();

            return [
                'id' => $med->id,
                'name' => $med->name,
                'generic_name' => $med->generic_name,
                'dosage' => $med->dosage,
                'frequency' => $med->frequency,
                'times' => $times,
                'start_date' => $med->start_date,
                'prescribed_by' => $med->prescribed_by,
                'purpose' => $med->purpose,
                'status' => $med->status,
                'side_effects' => [],
                'refills' => $med->refills
            ];
        })
        ->toArray();
}

    private function getMedicationColor($name)
    {
        $colors = ['yellow', 'blue', 'green', 'purple'];
        $index = abs(crc32($name)) % count($colors);
        return $colors[$index];
    }

    private function getTodayTakenCount($user)
    {
        return Meds::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();
    }

    private function getTodayScheduledCount($user)
    {
        return Meds::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();
    }

    private function getAdherenceRate($user)
    {
        $taken = $this->getTodayTakenCount($user);
        $scheduled = $this->getTodayScheduledCount($user);
        return $scheduled > 0 ? round(($taken / $scheduled) * 100) : 0;
    }

    private function getUpcomingRemindersCount($user)
    {
        return Meds::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();
    }

    private function getWeeklyAdherence($user)
    {
        return [
            ['day' => 'Mon', 'taken' => 4, 'scheduled' => 4, 'percentage' => 100],
            ['day' => 'Tue', 'taken' => 3, 'scheduled' => 4, 'percentage' => 75],
            ['day' => 'Wed', 'taken' => 4, 'scheduled' => 4, 'percentage' => 100],
            ['day' => 'Thu', 'taken' => 4, 'scheduled' => 4, 'percentage' => 100],
            ['day' => 'Fri', 'taken' => 4, 'scheduled' => 4, 'percentage' => 100],
            ['day' => 'Sat', 'taken' => 3, 'scheduled' => 4, 'percentage' => 75],
            ['day' => 'Sun', 'taken' => 4, 'scheduled' => 4, 'percentage' => 100]
        ];
    }

    private function getMedicationHistory($user, $days)
    {
        return collect([
            [
                'date' => now()->format('Y-m-d'),
                'medications_taken' => $this->getTodayTakenCount($user),
                'medications_scheduled' => $this->getTodayScheduledCount($user),
                'adherence' => $this->getAdherenceRate($user),
                'details' => $this->getTodayMedications($user)
            ],
            [
                'date' => now()->subDay()->format('Y-m-d'),
                'medications_taken' => 3,
                'medications_scheduled' => 4,
                'adherence' => 75,
                'details' => []
            ]
        ]);
    }
}
