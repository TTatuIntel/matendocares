<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\VitalSign;
use App\Models\Patient;
use App\Models\Doctor;
use App\Notifications\PatientVitalsUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class VitalSignsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $patient = auth()->user()->patient;
            if (!$patient) {
                return redirect()->route('patient.dashboard')
                    ->with('error', 'Please complete your patient profile first.');
            }

            $period = $request->get('period', 30);
            $recentVitals = VitalSign::where('patient_id', $patient->id)
                ->orderBy('measured_at', 'desc')
                ->take(50)
                ->get();

            $stats = $this->calculateVitalStatistics($patient, $period);

            return view('patient.vitals', array_merge([
                'recentVitals' => $recentVitals,
                'period' => $period
            ], $stats));

        } catch (\Exception $e) {
            Log::error('Vitals index error: ' . $e->getMessage());
            return view('patient.vitals', [
                'recentVitals' => collect(),
                'totalRecords' => 0,
                'weeklyRecords' => 0,
                'avgBP' => null,
                'avgHR' => null,
                'todayRecords' => 0,
                'period' => $period ?? 30
            ])->with('warning', 'Some data could not be loaded.');
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'blood_pressure' => 'nullable|string|regex:/^\d{2,3}\/\d{2,3}$/|max:10',
            'heart_rate' => 'nullable|numeric|min:1|max:500',
            'blood_glucose' => 'nullable|numeric|min:0.1|max:1000',
            'glucose_unit_selected' => 'nullable|in:mg/dl,mmol/l',
            'glucose_type_selected' => 'nullable|in:fasting,random',
            'temperature' => 'nullable|numeric|min:50|max:150',
            'weight' => 'nullable|numeric|min:1|max:2000',
            'oxygen_saturation' => 'nullable|numeric|min:1|max:100',
            'steps' => 'nullable|integer|min:0|max:200000',
            'sleep_hours' => 'nullable|numeric|min:0|max:24',
            'energy_level' => 'nullable|integer|min:1|max:10',
            'pain_level' => 'nullable|integer|min:0|max:10',
            'mood' => 'nullable|in:excellent,good,fair,poor,very_poor',
            'symptoms' => 'nullable|array',
            'symptoms.*' => 'string|max:50',
            'notes' => 'nullable|string|max:5000',
        ]);

        $patient = auth()->user()->patient;
        if (!$patient) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Patient profile not found.'], 400);
            }
            return redirect()->route('patient.dashboard')->with('error', 'Patient profile not found.');
        }

        DB::beginTransaction();

        try {
            $systolic = null;
            $diastolic = null;
            if (!empty($validated['blood_pressure'])) {
                $bpParts = explode('/', $validated['blood_pressure']);
                if (count($bpParts) === 2) {
                    $systolic = (float)$bpParts[0];
                    $diastolic = (float)$bpParts[1];
                }
            }

            $bloodGlucose = $validated['blood_glucose'] ?? null;
            $originalGlucoseUnit = $validated['glucose_unit_selected'] ?? 'mg/dl';
            $glucoseType = $validated['glucose_type_selected'] ?? 'fasting';

            $vitalData = [
                'patient_id' => $patient->id,
                'blood_pressure' => $validated['blood_pressure'] ?? null,
                'systolic_bp' => $systolic,
                'diastolic_bp' => $diastolic,
                'heart_rate' => $validated['heart_rate'] ?? null,
                'blood_glucose' => $bloodGlucose,
                'glucose_unit' => $originalGlucoseUnit,
                'glucose_type' => $glucoseType,
                'original_glucose_unit' => $originalGlucoseUnit,
                'temperature' => $validated['temperature'] ?? null,
                'temperature_unit' => 'fahrenheit',
                'weight' => $validated['weight'] ?? null,
                'weight_unit' => 'lbs',
                'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
                'steps' => $validated['steps'] ?? null,
                'sleep_hours' => $validated['sleep_hours'] ?? null,
                'mood' => $validated['mood'] ?? null,
                'energy_level' => $validated['energy_level'] ?? null,
                'pain_level' => $validated['pain_level'] ?? null,
                'symptoms' => !empty($validated['symptoms']) ? json_encode($validated['symptoms']) : null,
                'notes' => $validated['notes'] ?? null,
                'measured_at' => now(),
                'recorded_by' => auth()->id(),
                'entry_method' => 'manual',
                'validated' => true,
            ];

            $riskAssessment = $this->calculateComprehensiveRiskAssessment($vitalData, $validated['symptoms'] ?? []);
            $vitalData = array_merge($vitalData, $riskAssessment);

            if (!empty($vitalData['weight']) && !empty($patient->height)) {
                $heightInInches = $patient->height;
                $weightInLbs = $vitalData['weight'];
                $bmi = ($weightInLbs / ($heightInInches * $heightInInches)) * 703;
                $vitalData['bmi'] = round($bmi, 2);
            }

            $vitalData['risk_assessment_notes'] = $this->generateRiskAssessmentNotes($vitalData, $validated['symptoms'] ?? []);

            $vital = VitalSign::create($vitalData);
            $this->notifyDoctors($patient, $vital);

            DB::commit();

            $message = 'Vital signs recorded successfully';
            if ($riskAssessment['risk_level'] === 'critical') {
                $message .= '. CRITICAL values detected - consider seeking immediate medical attention.';
            } elseif ($riskAssessment['risk_level'] === 'high_risk') {
                $message .= '. High risk values detected - consider contacting your healthcare provider.';
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $vital,
                    'risk_level' => $riskAssessment['risk_level'],
                    'redirect_url' => route('patient.vitals.index')
                ]);
            }

            return redirect()->route('patient.vitals.index')->with('success', $message);

        } catch (ValidationException $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
            }
            throw $e;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vital sign save failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'patient_id' => $patient->id ?? null
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save vital signs: ' . (config('app.debug') ? $e->getMessage() : 'Please try again.')
                ], 500);
            }

            return redirect()->back()->withInput()->with('error', 'Failed to save vital signs. Please try again.');
        }
    }

    public function storeVitals(Request $request)
    {
        return $this->store($request);
    }

    public function updateVitals(Request $request)
    {
        $patient = auth()->user()->patient;
        if (!$patient) {
            return response()->json(['success' => false, 'message' => 'Patient profile not found.'], 400);
        }

        $validated = $request->validate([
            'vital_type' => 'required|in:blood_pressure,heart_rate,temperature,weight,blood_glucose',
            'value' => 'required|string|max:20',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $vitalData = [
                'patient_id' => $patient->id,
                'measured_at' => now(),
                'recorded_by' => auth()->id(),
                'entry_method' => 'manual',
                'notes' => $validated['notes'] ?? null
            ];

            $vitalData[$validated['vital_type']] = $validated['value'];

            if ($validated['vital_type'] === 'blood_pressure' && str_contains($validated['value'], '/')) {
                [$systolic, $diastolic] = explode('/', $validated['value']);
                $vitalData['systolic_bp'] = (float)$systolic;
                $vitalData['diastolic_bp'] = (float)$diastolic;
            }

            $vital = VitalSign::create($vitalData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vital sign updated successfully',
                'data' => $vital
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quick vital update failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update vital sign'
            ], 500);
        }
    }

    public function destroy(VitalSign $vital)
    {
        try {
            $patient = auth()->user()->patient;
            if (!$patient || $vital->patient_id !== $patient->id) {
                if (request()->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
                }
                return redirect()->route('patient.vitals.index')->with('error', 'Unauthorized access.');
            }

            $vital->delete();

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Vital sign deleted successfully.']);
            }

            return redirect()->route('patient.vitals.index')->with('success', 'Vital sign deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Vital sign deletion failed', ['vital_id' => $vital->id, 'error' => $e->getMessage()]);

            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete vital sign.'], 500);
            }

            return redirect()->route('patient.vitals.index')->with('error', 'Failed to delete vital sign.');
        }
    }

    public function history(Request $request)
    {
        try {
            $patient = auth()->user()->patient;
            if (!$patient) {
                return redirect()->route('patient.dashboard')->with('error', 'Please complete your patient profile first.');
            }

            $perPage = $request->get('per_page', 25);
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            $query = VitalSign::where('patient_id', $patient->id);

            if ($dateFrom) {
                $query->where('measured_at', '>=', Carbon::parse($dateFrom)->startOfDay());
            }

            if ($dateTo) {
                $query->where('measured_at', '<=', Carbon::parse($dateTo)->endOfDay());
            }

            $vitals = $query->orderBy('measured_at', 'desc')->paginate($perPage);

            return view('patient.vitals-history', [
                'vitals' => $vitals,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'perPage' => $perPage
            ]);

        } catch (\Exception $e) {
            Log::error('Vitals history error: ' . $e->getMessage());
            return redirect()->route('patient.vitals.index')->with('error', 'Unable to load vitals history.');
        }
    }

    public function statistics(Request $request)
    {
        try {
            $patient = auth()->user()->patient;
            if (!$patient) {
                return response()->json(['success' => false, 'message' => 'Patient profile not found.'], 400);
            }

            $period = $request->get('period', 30);
            $stats = $this->calculateVitalStatistics($patient, $period);

            return response()->json(['success' => true, 'data' => $stats]);

        } catch (\Exception $e) {
            Log::error('Statistics calculation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to calculate statistics'], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            $patient = auth()->user()->patient;
            if (!$patient) {
                return redirect()->route('patient.vitals.index')->with('error', 'Patient profile not found.');
            }

            $format = $request->get('format', 'csv');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            $query = VitalSign::where('patient_id', $patient->id);

            if ($dateFrom) {
                $query->where('measured_at', '>=', Carbon::parse($dateFrom));
            }

            if ($dateTo) {
                $query->where('measured_at', '<=', Carbon::parse($dateTo));
            }

            $vitals = $query->orderBy('measured_at', 'desc')->get();

            if ($format === 'csv') {
                return $this->exportToCsv($vitals);
            }

            return redirect()->route('patient.vitals.index')->with('error', 'Invalid export format.');

        } catch (\Exception $e) {
            Log::error('Export failed: ' . $e->getMessage());
            return redirect()->route('patient.vitals.index')->with('error', 'Export failed. Please try again.');
        }
    }

    public function markMedication(Request $request)
    {
        $validated = $request->validate([
            'medication_name' => 'required|string|max:100',
            'taken_at' => 'nullable|date',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $patient = auth()->user()->patient;
            if (!$patient) {
                return response()->json(['success' => false, 'message' => 'Patient profile not found.'], 400);
            }

            $notes = "Medication taken: {$validated['medication_name']}";
            if ($validated['notes']) {
                $notes .= " - Notes: {$validated['notes']}";
            }

            VitalSign::create([
                'patient_id' => $patient->id,
                'measured_at' => $validated['taken_at'] ? Carbon::parse($validated['taken_at']) : now(),
                'recorded_by' => auth()->id(),
                'entry_method' => 'manual',
                'notes' => $notes,
                'status' => 'normal'
            ]);

            return response()->json(['success' => true, 'message' => 'Medication marked as taken']);

        } catch (\Exception $e) {
            Log::error('Mark medication failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to mark medication'], 500);
        }
    }

    private function calculateVitalStatistics($patient, $period)
    {
        try {
            $vitals = VitalSign::where('patient_id', $patient->id)
                ->where('measured_at', '>=', now()->subDays($period))
                ->orderBy('measured_at', 'desc')
                ->get();

            $totalRecords = $vitals->count();
            $weeklyRecords = $vitals->where('measured_at', '>=', now()->subDays(7))->count();
            $todayRecords = $vitals->where('measured_at', '>=', today())->count();

            $avgSystolic = $vitals->whereNotNull('systolic_bp')->avg('systolic_bp');
            $avgDiastolic = $vitals->whereNotNull('diastolic_bp')->avg('diastolic_bp');
            $avgBP = ($avgSystolic && $avgDiastolic) ? round($avgSystolic) . '/' . round($avgDiastolic) : null;

            $avgHR = $vitals->whereNotNull('heart_rate')->avg('heart_rate');
            $avgHR = $avgHR ? round($avgHR) : null;

            return [
                'totalRecords' => $totalRecords,
                'weeklyRecords' => $weeklyRecords,
                'todayRecords' => $todayRecords,
                'avgBP' => $avgBP,
                'avgHR' => $avgHR,
                'period' => $period,
                'latest_vital' => $vitals->first(),
                'risk_distribution' => [
                    'normal' => $vitals->where('risk_level', 'normal')->count(),
                    'borderline' => $vitals->where('risk_level', 'borderline')->count(), 
                    'high_risk' => $vitals->where('risk_level', 'high_risk')->count(),
                    'critical' => $vitals->where('risk_level', 'critical')->count(),
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Failed to calculate vital statistics: ' . $e->getMessage());
            return [
                'totalRecords' => 0,
                'weeklyRecords' => 0,
                'todayRecords' => 0,
                'avgBP' => null,
                'avgHR' => null,
                'period' => $period,
                'risk_distribution' => ['normal' => 0, 'borderline' => 0, 'high_risk' => 0, 'critical' => 0]
            ];
        }
    }

    private function calculateComprehensiveRiskAssessment($vitalData, $symptoms = [])
    {
        $riskLevel = 'normal';
        $riskScore = 0;
        $status = 'normal';
        $requiresAttention = false;

        if (!empty($vitalData['systolic_bp']) && !empty($vitalData['diastolic_bp'])) {
            $systolic = $vitalData['systolic_bp'];
            $diastolic = $vitalData['diastolic_bp'];

            if ($systolic >= 140 || $diastolic >= 90) {
                $riskLevel = 'high_risk';
                $riskScore += 30;
                $status = 'warning';
                $requiresAttention = true;
            } elseif ($systolic >= 120 || $diastolic >= 80) {
                if ($riskLevel === 'normal') $riskLevel = 'borderline';
                $riskScore += 15;
                $status = 'caution';
            }
        }

        if (!empty($vitalData['heart_rate'])) {
            $hr = $vitalData['heart_rate'];

            if ($hr < 40 || $hr > 140) {
                $riskLevel = 'critical';
                $riskScore += 50;
                $status = 'critical';
                $requiresAttention = true;
            } elseif (($hr >= 40 && $hr <= 50) || ($hr >= 120 && $hr <= 139)) {
                if ($riskLevel !== 'critical') $riskLevel = 'high_risk';
                $riskScore += 25;
                $status = 'warning';
                $requiresAttention = true;
            } elseif ($hr >= 100 && $hr <= 119) {
                if ($riskLevel === 'normal') $riskLevel = 'borderline';
                $riskScore += 10;
            }
        }

        if (!empty($vitalData['temperature'])) {
            $temp = $vitalData['temperature'];
            $tempC = ($temp - 32) * 5/9;

            if ($tempC < 32.0 || $tempC >= 40.0) {
                $riskLevel = 'critical';
                $riskScore += 50;
                $status = 'critical';
                $requiresAttention = true;
            } elseif (($tempC >= 32.0 && $tempC < 35.0) || ($tempC >= 39.0 && $tempC < 40.0)) {
                if ($riskLevel !== 'critical') $riskLevel = 'high_risk';
                $riskScore += 30;
                $status = 'warning';
                $requiresAttention = true;
            } elseif ($tempC >= 38.0 && $tempC < 39.0) {
                if ($riskLevel === 'normal') $riskLevel = 'borderline';
                $riskScore += 15;
            }
        }

        if (!empty($vitalData['blood_glucose'])) {
            $glucose = $vitalData['blood_glucose'];
            $unit = $vitalData['glucose_unit'] ?? 'mg/dl';

            if ($unit === 'mg/dl') {
                if ($glucose < 50 || $glucose > 600) {
                    $riskLevel = 'critical';
                    $riskScore += 50;
                    $status = 'critical';
                    $requiresAttention = true;
                } elseif (($glucose >= 50 && $glucose <= 70) || ($glucose >= 300 && $glucose <= 599)) {
                    if ($riskLevel !== 'critical') $riskLevel = 'high_risk';
                    $riskScore += 30;
                    $requiresAttention = true;
                } elseif ($glucose >= 250 && $glucose <= 299) {
                    if ($riskLevel === 'normal') $riskLevel = 'borderline';
                    $riskScore += 15;
                }
            } else {
                if ($glucose < 2.8 || $glucose > 33.3) {
                    $riskLevel = 'critical';
                    $riskScore += 50;
                    $status = 'critical';
                    $requiresAttention = true;
                } elseif (($glucose >= 2.8 && $glucose <= 3.9) || ($glucose >= 16.7 && $glucose <= 33.3)) {
                    if ($riskLevel !== 'critical') $riskLevel = 'high_risk';
                    $riskScore += 30;
                    $requiresAttention = true;
                } elseif ($glucose >= 14.0 && $glucose <= 16.6) {
                    if ($riskLevel === 'normal') $riskLevel = 'borderline';
                    $riskScore += 15;
                }
            }
        }

        if (!empty($vitalData['oxygen_saturation'])) {
            $o2 = $vitalData['oxygen_saturation'];

            if ($o2 < 85) {
                $riskLevel = 'critical';
                $riskScore += 50;
                $status = 'critical';
                $requiresAttention = true;
            } elseif ($o2 >= 85 && $o2 <= 89) {
                if ($riskLevel !== 'critical') $riskLevel = 'high_risk';
                $riskScore += 30;
                $requiresAttention = true;
            } elseif ($o2 >= 90 && $o2 <= 93) {
                if ($riskLevel === 'normal') $riskLevel = 'borderline';
                $riskScore += 15;
            }
        }

        if (!empty($vitalData['pain_level'])) {
            $pain = $vitalData['pain_level'];

            if ($pain >= 8) {
                if ($riskLevel !== 'critical') $riskLevel = 'high_risk';
                $riskScore += 25;
                $requiresAttention = true;
            } elseif ($pain >= 6) {
                if ($riskLevel === 'normal') $riskLevel = 'borderline';
                $riskScore += 10;
            }
        }

        if (!empty($symptoms)) {
            $criticalSymptoms = ['chest_pain', 'shortness_of_breath'];
            $highRiskSymptoms = ['fever', 'dizziness', 'nausea'];

            $hasCriticalSymptoms = !empty(array_intersect($symptoms, $criticalSymptoms));
            $hasHighRiskSymptoms = !empty(array_intersect($symptoms, $highRiskSymptoms));

            if ($hasCriticalSymptoms) {
                $riskLevel = 'critical';
                $riskScore += 40;
                $status = 'critical';
                $requiresAttention = true;
            } elseif ($hasHighRiskSymptoms) {
                if ($riskLevel !== 'critical') $riskLevel = 'high_risk';
                $riskScore += 20;
                $requiresAttention = true;
            }
        }

        $riskScore = min(100, $riskScore);

        return [
            'risk_level' => $riskLevel,
            'risk_score' => $riskScore,
            'status' => $status,
            'requires_attention' => $requiresAttention
        ];
    }

    private function generateRiskAssessmentNotes($vitalData, $symptoms = [])
    {
        $notes = [];
        $notes[] = "AUTOMATED RISK ASSESSMENT - " . now()->format('M d, Y g:i A');
        $notes[] = "";

        $riskLevel = $vitalData['risk_level'] ?? 'normal';
        $notes[] = "OVERALL RISK LEVEL: " . strtoupper($riskLevel);
        $notes[] = "";

        if (!empty($vitalData['blood_pressure'])) {
            $bp = $vitalData['blood_pressure'];
            [$systolic, $diastolic] = explode('/', $bp);
            $systolic = (int)$systolic;
            $diastolic = (int)$diastolic;

            if ($systolic >= 140 || $diastolic >= 90) {
                $notes[] = "HYPERTENSION: Blood pressure {$bp} indicates cardiovascular risk";
            } elseif ($systolic >= 120 || $diastolic >= 80) {
                $notes[] = "ELEVATED BP: Blood pressure {$bp} approaching hypertensive range";
            } else {
                $notes[] = "Blood pressure {$bp} within normal range";
            }
        }

        if (!empty($vitalData['heart_rate'])) {
            $hr = $vitalData['heart_rate'];
            if ($hr < 40 || $hr > 140) {
                $notes[] = "CRITICAL HEART RATE: {$hr} bpm requires immediate medical evaluation";
            } elseif (($hr >= 40 && $hr <= 50) || ($hr >= 120 && $hr <= 139)) {
                $notes[] = "HIGH RISK HEART RATE: {$hr} bpm - monitor cardiac function closely";
            } elseif ($hr >= 100 && $hr <= 119) {
                $notes[] = "BORDERLINE HEART RATE: {$hr} bpm - slightly elevated";
            } else {
                $notes[] = "Heart rate {$hr} bpm within normal range";
            }
        }

        if (!empty($symptoms)) {
            $symptomsText = implode(', ', $symptoms);
            $notes[] = "Symptoms reported: {$symptomsText}";
            
            if (in_array('chest_pain', $symptoms) && in_array('shortness_of_breath', $symptoms)) {
                $notes[] = "CARDIAC/RESPIRATORY EMERGENCY: Chest pain + breathing difficulty";
            }
        }

        $notes[] = "";
        switch ($riskLevel) {
            case 'critical':
                $notes[] = "RECOMMENDATION: Seek immediate emergency medical attention";
                break;
            case 'high_risk':
                $notes[] = "RECOMMENDATION: Contact healthcare provider urgently for evaluation";
                break;
            case 'borderline':
                $notes[] = "RECOMMENDATION: Monitor closely and consider healthcare consultation";
                break;
            default:
                $notes[] = "RECOMMENDATION: Continue regular monitoring";
                break;
        }

        return implode("\n", $notes);
    }

    private function notifyDoctors($patient, $vital)
    {
        try {
            $patient = Patient::with('user')->findOrFail($patient->id);
            $doctors = Doctor::with('user')->has('user')->get();

            Log::info("Notifying {$doctors->count()} doctors for patient {$patient->id}");

            foreach ($doctors as $doctor) {
                try {
                    $notification = new PatientVitalsUpdated($patient, $vital);
                    $doctor->user->notify($notification);
                    
                    Log::info("Notification sent to doctor {$doctor->id}");
                } catch (\Exception $e) {
                    Log::error("Notification failed for doctor {$doctor->id}: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error("Doctor notification process failed: " . $e->getMessage());
        }
    }

    private function exportToCsv($vitals)
    {
        $filename = 'vitals-export-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($vitals) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Date/Time', 'Blood Pressure', 'Heart Rate', 'Temperature', 
                'Weight', 'Blood Glucose', 'Oxygen Saturation', 'Steps', 
                'Sleep Hours', 'Mood', 'Energy Level', 'Pain Level', 
                'Symptoms', 'Notes', 'Risk Level'
            ]);

            foreach ($vitals as $vital) {
                fputcsv($file, [
                    $vital->measured_at->format('Y-m-d H:i:s'),
                    $vital->blood_pressure ?: '',
                    $vital->heart_rate ?: '',
                    $vital->temperature ?: '',
                    $vital->weight ?: '',
                    $vital->blood_glucose ?: '',
                    $vital->oxygen_saturation ?: '',
                    $vital->steps ?: '',
                    $vital->sleep_hours ?: '',
                    $vital->mood ?: '',
                    $vital->energy_level ?: '',
                    $vital->pain_level ?: '',
                    $vital->symptoms ? implode(', ', json_decode($vital->symptoms, true)) : '',
                    $vital->notes ?: '',
                    $vital->risk_level ?: 'normal'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}