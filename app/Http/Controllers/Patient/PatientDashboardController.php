<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\VitalSign;
use App\Models\Medication;
use App\Models\MedicationLog;
use App\Models\Appointment;
use App\Models\HealthMetric;
use App\Models\MedicalRecord;
use App\Models\Document;
use App\Models\Patient;
use App\Models\TempAccess;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use App\Models\DoctorMed;

class PatientDashboardController extends Controller
{
    /**
     * Display the patient dashboard
     */
    public function index()
    {
        try {
            $patient = auth()->user()->patient;

            if (!$patient) {
                return redirect()->route('patient.profile.complete')
                    ->with('error', 'Please complete your patient profile first.');
            }

            // Get latest vital signs
            $latestVitals = $this->getLatestVitals($patient);

            // Calculate health score
            $healthScore = $this->calculateHealthScore($patient);

            // Get medication reminders for today
            $medicationReminders = $this->getMedicationReminders($patient);

            // Get upcoming appointments
            $upcomingAppointments = $this->getUpcomingAppointments($patient);

            // Health trends for chart (last 30 days)
            $trendsData = $this->getTrendsData($patient);

            // Recent updates/activity
            $recentUpdates = $this->getRecentUpdates($patient);

            // Health insights
            $healthInsights = $this->generateHealthInsights($patient, $latestVitals);

            // Get doctor's health tips
            $doctorMeds = DoctorMed::where('user_id', auth()->id())
                ->whereNotNull('health_tips')
                ->with('doctor')
                ->latest()
                ->get();

            return view('patient.dashboard', compact(
                'patient',
                'latestVitals',
                'healthScore',
                'medicationReminders',
                'upcomingAppointments',
                'trendsData',
                'recentUpdates',
                'healthInsights',
                'doctorMeds'
            ));

        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('patient.dashboard', [
                'patient' => null,
                'latestVitals' => null,
                'healthScore' => 75,
                'medicationReminders' => collect(),
                'upcomingAppointments' => collect(),
                'trendsData' => ['labels' => [], 'systolic' => [], 'diastolic' => [], 'heart_rate' => [], 'weight' => []],
                'recentUpdates' => collect(),
                'healthInsights' => collect(),
                'doctorMeds' => collect()
            ])->with('warning', 'Some dashboard data could not be loaded. Please try refreshing the page.');
        }
    }

    /**
     * Store vital signs data
     */
    public function storeVitals(Request $request)
    {
        $request->validate([
            'blood_pressure' => 'nullable|string|regex:/^\d{2,3}\/\d{2,3}$/',
            'heart_rate' => 'nullable|numeric|min:30|max:300',
            'temperature' => 'nullable|numeric|min:90|max:110',
            'weight' => 'nullable|numeric|min:50|max:1000',
            'blood_glucose' => 'nullable|numeric|min:50|max:500',
            'mood' => 'nullable|in:excellent,good,fair,poor,very_poor',
            'pain_level' => 'nullable|numeric|min:0|max:10',
            'energy_level' => 'nullable|numeric|min:1|max:10',
            'symptoms' => 'nullable|array',
            'symptoms.*' => 'string|in:headache,fever,fatigue,nausea,dizziness,chest_pain,shortness_of_breath,muscle_pain,joint_pain',
            'notes' => 'nullable|string|max:1000'
        ]);

        $patient = auth()->user()->patient;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found. Please complete your profile first.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Parse blood pressure if provided
            $systolic = null;
            $diastolic = null;
            if ($request->blood_pressure && strpos($request->blood_pressure, '/') !== false) {
                list($systolic, $diastolic) = explode('/', $request->blood_pressure);
                $systolic = (int)$systolic;
                $diastolic = (int)$diastolic;
            }

            // Create vital signs record
            $vitalSigns = VitalSign::create([
                'id' => Str::uuid(),
                'patient_id' => $patient->id,
                'blood_pressure' => $request->blood_pressure,
                'systolic_bp' => $systolic,
                'diastolic_bp' => $diastolic,
                'heart_rate' => $request->heart_rate,
                'temperature' => $request->temperature,
                'weight' => $request->weight,
                'blood_glucose' => $request->blood_glucose,
                'mood' => $request->mood,
                'pain_level' => $request->pain_level,
                'energy_level' => $request->energy_level,
                'symptoms' => $request->symptoms ? json_encode($request->symptoms) : null,
                'notes' => $request->notes,
                'measured_at' => now(),
                'recorded_by' => auth()->id(),
                'entry_method' => 'manual',
                'status' => $this->calculateVitalStatus($request)
            ]);

            // Create medical record
            $this->createMedicalRecord($patient, $request, $vitalSigns);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Health information updated successfully',
                'status' => $vitalSigns->status,
                'id' => $vitalSigns->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing vital signs: ' . $e->getMessage(), [
                'patient_id' => $patient->id,
                'request_data' => $request->except(['_token']),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save vital signs data. Please try again.'
            ], 500);
        }
    }

    // Private helper methods

    private function getLatestVitals($patient)
    {
        try {
            return VitalSign::where('patient_id', $patient->id)
                ->latest('measured_at')
                ->first();
        } catch (\Exception $e) {
            Log::warning('Error fetching latest vitals: ' . $e->getMessage());
            return null;
        }
    }

    private function getMedicationReminders($patient)
    {
        try {
            $today = now()->format('Y-m-d');
            $currentTime = now()->format('H:i:s');

            return Medication::where('patient_id', $patient->id)
                ->where('status', 'active')
                ->with(['logs' => function($query) use ($today, $currentTime) {
                    $query->whereDate('scheduled_time', $today)
                          ->whereTime('scheduled_time', '>=', $currentTime)
                          ->where('status', 'pending')
                          ->orderBy('scheduled_time');
                }])
                ->get()
                ->flatMap(function($medication) {
                    return $medication->logs->map(function($log) use ($medication) {
                        return (object) [
                            'id' => $log->id,
                            'medication_id' => $medication->id,
                            'medication' => $medication->name,
                            'dosage' => $medication->dosage,
                            'time' => Carbon::parse($log->scheduled_time)->format('g:i A'),
                            'is_due' => Carbon::parse($log->scheduled_time)->isPast(),
                            'status' => $log->status
                        ];
                    });
                })
                ->sortBy('time')
                ->take(5);
        } catch (\Exception $e) {
            Log::error('Error fetching medication reminders: ' . $e->getMessage());
            return collect();
        }
    }

    private function getUpcomingAppointments($patient)
    {
        try {
            return Appointment::where('patient_id', $patient->id)
                ->where('scheduled_at', '>=', now())
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->with(['doctor.user'])
                ->orderBy('scheduled_at')
                ->take(3)
                ->get()
                ->map(function($appointment) {
                    return (object) [
                        'id' => $appointment->id,
                        'doctor_name' => $appointment->doctor->user->name ?? 'Unknown Doctor',
                        'type' => $appointment->type,
                        'date' => $appointment->scheduled_at->format('M j, Y'),
                        'time' => $appointment->scheduled_at->format('g:i A'),
                        'is_today' => $appointment->scheduled_at->isToday(),
                        'is_urgent' => in_array($appointment->priority, ['urgent', 'emergency']),
                        'status' => $appointment->status
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error fetching upcoming appointments: ' . $e->getMessage());
            return collect();
        }
    }

    private function getTrendsData($patient)
    {
        try {
            $vitals = VitalSign::where('patient_id', $patient->id)
                ->where('measured_at', '>=', Carbon::now()->subDays(30))
                ->orderBy('measured_at')
                ->select(['measured_at', 'systolic_bp', 'diastolic_bp', 'heart_rate', 'weight'])
                ->get();

            return $this->processTrendData($vitals);
        } catch (\Exception $e) {
            Log::warning('Error fetching trend data: ' . $e->getMessage());
            return [
                'labels' => [],
                'systolic' => [],
                'diastolic' => [],
                'heart_rate' => [],
                'weight' => []
            ];
        }
    }

    private function calculateHealthScore($patient)
    {
        try {
            // Get the latest health metric or calculate from recent vitals
            $latestMetric = DB::table('health_metrics')
                ->where('patient_id', $patient->id)
                ->latest('calculated_at')
                ->first();

            if ($latestMetric) {
                return (int) $latestMetric->health_score;
            }

            // Calculate basic score from recent vitals
            $recentVitals = VitalSign::where('patient_id', $patient->id)
                ->where('measured_at', '>=', Carbon::now()->subDays(7))
                ->get();

            if ($recentVitals->isEmpty()) {
                return 75; // Default score
            }

            $score = 100;

            // Analyze blood pressure
            $bpReadings = $recentVitals->whereNotNull('systolic_bp');
            if ($bpReadings->count() > 0) {
                $avgSystolic = $bpReadings->avg('systolic_bp');
                $avgDiastolic = $recentVitals->whereNotNull('diastolic_bp')->avg('diastolic_bp');

                if ($avgSystolic > 140 || $avgDiastolic > 90) {
                    $score -= 15;
                } elseif ($avgSystolic > 130 || $avgDiastolic > 85) {
                    $score -= 8;
                }
            }

            // Analyze heart rate
            $hrReadings = $recentVitals->whereNotNull('heart_rate');
            if ($hrReadings->count() > 0) {
                $avgHR = $hrReadings->avg('heart_rate');
                if ($avgHR > 100 || $avgHR < 60) {
                    $score -= 10;
                }
            }

            return max(0, min(100, $score));
        } catch (\Exception $e) {
            Log::warning('Error calculating health score: ' . $e->getMessage());
            return 75;
        }
    }

    private function processTrendData($vitalSigns)
    {
        if ($vitalSigns->isEmpty()) {
            return [
                'labels' => [],
                'systolic' => [],
                'diastolic' => [],
                'heart_rate' => [],
                'weight' => []
            ];
        }

        $groupedData = $vitalSigns->groupBy(function($vital) {
            return Carbon::parse($vital->measured_at)->format('M d');
        });

        $labels = $groupedData->keys()->toArray();

        $systolic = $groupedData->map(function($dayVitals) {
            return $dayVitals->whereNotNull('systolic_bp')->avg('systolic_bp') ?: null;
        })->values()->toArray();

        $diastolic = $groupedData->map(function($dayVitals) {
            return $dayVitals->whereNotNull('diastolic_bp')->avg('diastolic_bp') ?: null;
        })->values()->toArray();

        $heartRate = $groupedData->map(function($dayVitals) {
            return $dayVitals->whereNotNull('heart_rate')->avg('heart_rate') ?: null;
        })->values()->toArray();

        $weight = $groupedData->map(function($dayVitals) {
            return $dayVitals->whereNotNull('weight')->avg('weight') ?: null;
        })->values()->toArray();

        return [
            'labels' => $labels,
            'systolic' => $systolic,
            'diastolic' => $diastolic,
            'heart_rate' => $heartRate,
            'weight' => $weight
        ];
    }

    private function getRecentUpdates($patient)
    {
        try {
            $updates = collect();

            // Recent vital signs
            $recentVitals = VitalSign::where('patient_id', $patient->id)
                ->latest('measured_at')
                ->take(5)
                ->get();

            foreach ($recentVitals as $vital) {
                $updates->push((object) [
                    'type' => 'Vital Signs Update',
                    'description' => 'BP: ' . ($vital->blood_pressure ?? '--') . ', HR: ' . ($vital->heart_rate ?? '--') . ' bpm',
                    'status' => $vital->status ?? 'normal',
                    'created_at' => $vital->measured_at,
                    'icon' => 'heart'
                ]);
            }

            return $updates->sortByDesc('created_at')->take(10);
        } catch (\Exception $e) {
            Log::warning('Error fetching recent updates: ' . $e->getMessage());
            return collect();
        }
    }

    private function generateHealthInsights($patient, $latestVitals)
    {
        try {
            $insights = collect();

            if (!$latestVitals) {
                $insights->push([
                    'type' => 'info',
                    'title' => 'Start Tracking Your Health',
                    'message' => 'Record your first vital signs to begin monitoring your health trends.',
                    'action' => 'Record Vitals'
                ]);
                return $insights;
            }

            // Blood pressure insights
            if ($latestVitals->systolic_bp && $latestVitals->diastolic_bp) {
                if ($latestVitals->systolic_bp >= 140 || $latestVitals->diastolic_bp >= 90) {
                    $insights->push([
                        'type' => 'warning',
                        'title' => 'Elevated Blood Pressure',
                        'message' => 'Your recent blood pressure reading is elevated. Consider consulting your healthcare provider.',
                        'action' => 'Schedule Appointment'
                    ]);
                } elseif ($latestVitals->systolic_bp < 120 && $latestVitals->diastolic_bp < 80) {
                    $insights->push([
                        'type' => 'positive',
                        'title' => 'Healthy Blood Pressure',
                        'message' => 'Your blood pressure is in the optimal range. Keep up the good work!',
                        'action' => 'View Trends'
                    ]);
                }
            }

            return $insights->take(3);
        } catch (\Exception $e) {
            Log::warning('Error generating health insights: ' . $e->getMessage());
            return collect();
        }
    }

    private function calculateVitalStatus($request)
    {
        $criticalFlags = 0;
        $warningFlags = 0;

        // Blood pressure analysis
        if ($request->blood_pressure && strpos($request->blood_pressure, '/') !== false) {
            list($systolic, $diastolic) = explode('/', $request->blood_pressure);
            $systolic = (int)$systolic;
            $diastolic = (int)$diastolic;

            if ($systolic >= 180 || $diastolic >= 120) {
                $criticalFlags++;
            } elseif ($systolic >= 140 || $diastolic >= 90) {
                $warningFlags++;
            }
        }

        // Heart rate analysis
        if ($request->heart_rate) {
            $hr = (int)$request->heart_rate;
            if ($hr > 120 || $hr < 50) {
                $criticalFlags++;
            } elseif ($hr > 100 || $hr < 60) {
                $warningFlags++;
            }
        }

        // Temperature analysis
        if ($request->temperature) {
            $temp = (float)$request->temperature;
            if ($temp >= 103 || $temp <= 95) {
                $criticalFlags++;
            } elseif ($temp >= 100.4 || $temp <= 97) {
                $warningFlags++;
            }
        }

        // Pain level consideration
        if ($request->pain_level && $request->pain_level >= 8) {
            $criticalFlags++;
        } elseif ($request->pain_level && $request->pain_level >= 6) {
            $warningFlags++;
        }

        // Symptom analysis
        if ($request->symptoms && is_array($request->symptoms)) {
            $criticalSymptoms = ['chest_pain', 'shortness_of_breath'];
            if (array_intersect($criticalSymptoms, $request->symptoms)) {
                $criticalFlags++;
            }
        }

        if ($criticalFlags > 0) return 'critical';
        if ($warningFlags > 1) return 'warning';
        return 'normal';
    }

    private function createMedicalRecord($patient, $request, $vitalSigns)
    {
        try {
            MedicalRecord::create([
                'id' => Str::uuid(),
                'patient_id' => $patient->id,
                'record_type' => 'vital_signs',
                'data' => [
                    'vital_signs_id' => $vitalSigns->id,
                    'measurements' => $request->except(['_token', 'notes']),
                    'symptoms' => $request->symptoms ?? [],
                    'wellbeing' => [
                        'mood' => $request->mood,
                        'energy_level' => $request->energy_level,
                        'pain_level' => $request->pain_level,
                    ]
                ],
                'notes' => $request->notes,
                'recorded_at' => now(),
                'recorded_by' => auth()->id(),
                'severity' => $vitalSigns->status === 'critical' ? 'high' : ($vitalSigns->status === 'warning' ? 'medium' : 'low'),
                'status' => 'active'
            ]);
        } catch (\Exception $e) {
            Log::info('Medical record creation failed: ' . $e->getMessage());
        }
    }



}
