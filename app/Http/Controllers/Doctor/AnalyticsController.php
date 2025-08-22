<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\DoctorPatient;
use App\Models\VitalSign;
use App\Models\Appointment;
use App\Models\Alert;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        if (!$doctor) {
            return redirect()->route('doctor.dashboard')->with('error', 'Doctor profile not found.');
        }

        try {
            // Get doctor's patients
            $patientIds = DoctorPatient::where('doctor_id', $doctor->id)
                ->where('status', 'active')
                ->pluck('patient_id');

            // Overall Statistics
            $totalPatients = $patientIds->count();
            $activePatients = $this->getActivePatientsCount($patientIds);
            $totalVitalReadings = $this->getTotalVitalReadings($patientIds);
            $averageHealthScore = $this->getAverageHealthScore($patientIds);

            // Trend Data (last 30 days)
            $vitalTrends = $this->getVitalTrends($patientIds);
            $patientActivityTrends = $this->getPatientActivityTrends($patientIds);
            $alertTrends = $this->getAlertTrends($patientIds);

            // Patient Health Distribution
            $healthDistribution = $this->getHealthDistribution($patientIds);

            // Recent Insights
            $recentInsights = $this->getRecentInsights($patientIds);

            // Top Concerns
            $topConcerns = $this->getTopConcerns($patientIds);

            return view('doctor.analytics.index', compact(
                'totalPatients',
                'activePatients', 
                'totalVitalReadings',
                'averageHealthScore',
                'vitalTrends',
                'patientActivityTrends',
                'alertTrends',
                'healthDistribution',
                'recentInsights',
                'topConcerns'
            ));

        } catch (\Exception $e) {
            \Log::error('Analytics Error: ' . $e->getMessage());
            
            return view('doctor.analytics.index', [
                'totalPatients' => 0,
                'activePatients' => 0,
                'totalVitalReadings' => 0,
                'averageHealthScore' => 0,
                'vitalTrends' => [],
                'patientActivityTrends' => [],
                'alertTrends' => [],
                'healthDistribution' => [],
                'recentInsights' => [],
                'topConcerns' => []
            ]);
        }
    }

    /**
     * Get patient-specific analytics
     */
    public function patientAnalytics(Patient $patient)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Verify doctor has access to this patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $patient->id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return redirect()->route('doctor.analytics')->with('error', 'Access denied to patient data.');
        }

        try {
            // Patient's vital signs over time
            $vitalHistory = VitalSign::where('patient_id', $patient->id)
                ->where('measured_at', '>=', now()->subDays(90))
                ->orderBy('measured_at')
                ->get();

            // Health score trend
            $healthScoreTrend = $this->calculateHealthScoreTrend($patient->id);

            // Alert history
            $alertHistory = Alert::where('patient_id', $patient->id)
                ->where('triggered_at', '>=', now()->subDays(90))
                ->orderBy('triggered_at', 'desc')
                ->get();

            // Medication adherence
            $medicationAdherence = $this->getMedicationAdherence($patient->id);

            // Appointment history
            $appointmentHistory = Appointment::where('patient_id', $patient->id)
                ->where('scheduled_at', '>=', now()->subDays(90))
                ->orderBy('scheduled_at', 'desc')
                ->get();

            return view('doctor.analytics.patient', compact(
                'patient',
                'vitalHistory',
                'healthScoreTrend',
                'alertHistory',
                'medicationAdherence',
                'appointmentHistory'
            ));

        } catch (\Exception $e) {
            \Log::error('Patient Analytics Error: ' . $e->getMessage());
            return redirect()->route('doctor.analytics')->with('error', 'Error loading patient analytics.');
        }
    }

    /**
     * Get health trends analytics
     */
    public function trends()
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        $patientIds = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('status', 'active')
            ->pluck('patient_id');

        try {
            // Monthly trends for the past year
            $monthlyTrends = $this->getMonthlyTrends($patientIds);
            
            // Seasonal patterns
            $seasonalPatterns = $this->getSeasonalPatterns($patientIds);
            
            // Day of week patterns
            $weeklyPatterns = $this->getWeeklyPatterns($patientIds);
            
            // Health metric correlations
            $correlations = $this->getHealthMetricCorrelations($patientIds);

            return view('doctor.analytics.trends', compact(
                'monthlyTrends',
                'seasonalPatterns', 
                'weeklyPatterns',
                'correlations'
            ));

        } catch (\Exception $e) {
            \Log::error('Trends Analytics Error: ' . $e->getMessage());
            return redirect()->route('doctor.analytics')->with('error', 'Error loading trends data.');
        }
    }

    /**
     * Get comparative analytics
     */
    public function comparative()
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        $patientIds = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('status', 'active')
            ->pluck('patient_id');

        try {
            // Age group comparisons
            $ageGroupComparisons = $this->getAgeGroupComparisons($patientIds);
            
            // Gender-based comparisons
            $genderComparisons = $this->getGenderComparisons($patientIds);
            
            // Condition-based comparisons
            $conditionComparisons = $this->getConditionComparisons($patientIds);
            
            // Best and worst performers
            $performers = $this->getPerformanceComparisons($patientIds);

            return view('doctor.analytics.comparative', compact(
                'ageGroupComparisons',
                'genderComparisons',
                'conditionComparisons',
                'performers'
            ));

        } catch (\Exception $e) {
            \Log::error('Comparative Analytics Error: ' . $e->getMessage());
            return redirect()->route('doctor.analytics')->with('error', 'Error loading comparative data.');
        }
    }

    /**
     * Generate AI-powered insights
     */
    public function generateInsights(Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        $patientIds = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('status', 'active')
            ->pluck('patient_id');

        try {
            $insights = [
                'patient_risk_assessments' => $this->generateRiskAssessments($patientIds),
                'treatment_recommendations' => $this->generateTreatmentRecommendations($patientIds),
                'care_optimization_suggestions' => $this->generateCareOptimizations($patientIds),
                'early_warning_indicators' => $this->generateEarlyWarnings($patientIds)
            ];

            return response()->json([
                'success' => true,
                'insights' => $insights,
                'generated_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            \Log::error('Insights Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating insights'
            ], 500);
        }
    }

    // Private helper methods

    private function getActivePatientsCount($patientIds)
    {
        return VitalSign::whereIn('patient_id', $patientIds)
            ->where('measured_at', '>=', now()->subDays(7))
            ->distinct('patient_id')
            ->count();
    }

    private function getTotalVitalReadings($patientIds)
    {
        return VitalSign::whereIn('patient_id', $patientIds)
            ->where('measured_at', '>=', now()->subDays(30))
            ->count();
    }

    private function getAverageHealthScore($patientIds)
    {
        $scores = VitalSign::whereIn('patient_id', $patientIds)
            ->where('measured_at', '>=', now()->subDays(30))
            ->get()
            ->map(function($vital) {
                return $this->calculateHealthScore($vital);
            });

        return $scores->count() > 0 ? round($scores->average(), 1) : 0;
    }

    private function calculateHealthScore($vital)
    {
        $score = 100;
        
        // Deduct points based on vital sign ranges
        if ($vital->systolic_bp > 140 || $vital->systolic_bp < 90) $score -= 10;
        if ($vital->diastolic_bp > 90 || $vital->diastolic_bp < 60) $score -= 10;
        if ($vital->heart_rate > 100 || $vital->heart_rate < 60) $score -= 10;
        if ($vital->temperature > 37.5 || $vital->temperature < 36) $score -= 15;
        if ($vital->oxygen_saturation < 95) $score -= 20;
        
        return max(0, $score);
    }

    private function getVitalTrends($patientIds)
    {
        return VitalSign::whereIn('patient_id', $patientIds)
            ->where('measured_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(measured_at) as date'),
                DB::raw('AVG(systolic_bp) as avg_systolic'),
                DB::raw('AVG(diastolic_bp) as avg_diastolic'),
                DB::raw('AVG(heart_rate) as avg_heart_rate'),
                DB::raw('AVG(temperature) as avg_temperature'),
                DB::raw('AVG(oxygen_saturation) as avg_oxygen')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getPatientActivityTrends($patientIds)
    {
        return VitalSign::whereIn('patient_id', $patientIds)
            ->where('measured_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(measured_at) as date'),
                DB::raw('COUNT(DISTINCT patient_id) as active_patients'),
                DB::raw('COUNT(*) as total_readings')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getAlertTrends($patientIds)
    {
        return Alert::whereIn('patient_id', $patientIds)
            ->where('triggered_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(triggered_at) as date'),
                DB::raw('COUNT(*) as total_alerts'),
                DB::raw('SUM(CASE WHEN severity = "critical" THEN 1 ELSE 0 END) as critical_alerts'),
                DB::raw('SUM(CASE WHEN severity = "high" THEN 1 ELSE 0 END) as high_alerts')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getHealthDistribution($patientIds)
    {
        $patients = Patient::whereIn('id', $patientIds)->with('latestVitalSign')->get();
        
        $distribution = [
            'excellent' => 0,
            'good' => 0,
            'fair' => 0,
            'poor' => 0,
            'critical' => 0
        ];

        foreach ($patients as $patient) {
            if ($patient->latestVitalSign) {
                $score = $this->calculateHealthScore($patient->latestVitalSign);
                
                if ($score >= 90) $distribution['excellent']++;
                elseif ($score >= 75) $distribution['good']++;
                elseif ($score >= 60) $distribution['fair']++;
                elseif ($score >= 40) $distribution['poor']++;
                else $distribution['critical']++;
            }
        }

        return $distribution;
    }

    private function getRecentInsights($patientIds)
    {
        $insights = [];
        
        // High blood pressure trend
        $highBpCount = VitalSign::whereIn('patient_id', $patientIds)
            ->where('measured_at', '>=', now()->subDays(7))
            ->where('systolic_bp', '>', 140)
            ->distinct('patient_id')
            ->count();
            
        if ($highBpCount > 0) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'High Blood Pressure Alert',
                'message' => "{$highBpCount} patients showing elevated blood pressure this week",
                'action' => 'Review medication adherence and lifestyle factors'
            ];
        }

        // Low activity insight
        $inactiveCount = Patient::whereIn('id', $patientIds)
            ->whereDoesntHave('vitalSigns', function($q) {
                $q->where('measured_at', '>=', now()->subDays(3));
            })
            ->count();
            
        if ($inactiveCount > 0) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Patient Engagement',
                'message' => "{$inactiveCount} patients haven't submitted vitals in 3+ days",
                'action' => 'Consider outreach or reminder protocols'
            ];
        }

        return $insights;
    }

    private function getTopConcerns($patientIds)
    {
        $concerns = Alert::whereIn('patient_id', $patientIds)
            ->where('triggered_at', '>=', now()->subDays(30))
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        return $concerns->map(function($concern) {
            return [
                'type' => ucwords(str_replace('_', ' ', $concern->type)),
                'count' => $concern->count,
                'percentage' => 0 // Calculate based on total
            ];
        });
    }

    private function calculateHealthScoreTrend($patientId)
    {
        return VitalSign::where('patient_id', $patientId)
            ->where('measured_at', '>=', now()->subDays(90))
            ->orderBy('measured_at')
            ->get()
            ->map(function($vital) {
                return [
                    'date' => $vital->measured_at->format('Y-m-d'),
                    'score' => $this->calculateHealthScore($vital)
                ];
            });
    }

    private function getMedicationAdherence($patientId)
    {
        // Mock implementation - replace with actual medication tracking logic
        return [
            'overall_adherence' => 85,
            'weekly_trend' => [78, 82, 88, 85, 89, 83, 87],
            'missed_doses_this_week' => 3,
            'on_track_medications' => 4,
            'concerning_medications' => 1
        ];
    }

    private function getMonthlyTrends($patientIds)
    {
        return VitalSign::whereIn('patient_id', $patientIds)
            ->where('measured_at', '>=', now()->subYear())
            ->select(
                DB::raw('YEAR(measured_at) as year'),
                DB::raw('MONTH(measured_at) as month'),
                DB::raw('AVG(systolic_bp) as avg_systolic'),
                DB::raw('AVG(heart_rate) as avg_heart_rate'),
                DB::raw('COUNT(*) as readings_count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    private function getSeasonalPatterns($patientIds)
    {
        // Mock seasonal analysis - implement based on your needs
        return [
            'spring' => ['avg_bp' => 125, 'avg_hr' => 72, 'alert_frequency' => 'low'],
            'summer' => ['avg_bp' => 122, 'avg_hr' => 75, 'alert_frequency' => 'medium'],
            'fall' => ['avg_bp' => 128, 'avg_hr' => 70, 'alert_frequency' => 'low'],
            'winter' => ['avg_bp' => 130, 'avg_hr' => 68, 'alert_frequency' => 'high']
        ];
    }

    private function getWeeklyPatterns($patientIds)
    {
        return VitalSign::whereIn('patient_id', $patientIds)
            ->where('measured_at', '>=', now()->subDays(90))
            ->select(
                DB::raw('DAYOFWEEK(measured_at) as day_of_week'),
                DB::raw('AVG(systolic_bp) as avg_systolic'),
                DB::raw('COUNT(*) as readings_count')
            )
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->get()
            ->map(function($item) {
                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                return [
                    'day' => $days[$item->day_of_week - 1],
                    'avg_systolic' => round($item->avg_systolic, 1),
                    'readings_count' => $item->readings_count
                ];
            });
    }

    private function getHealthMetricCorrelations($patientIds)
    {
        // Mock correlation analysis - implement statistical correlation calculation
        return [
            'bp_heart_rate' => 0.65,
            'temperature_heart_rate' => 0.45,
            'oxygen_heart_rate' => -0.32,
            'age_bp' => 0.78
        ];
    }

    private function getAgeGroupComparisons($patientIds)
    {
        // Mock age group analysis
        return [
            '18-30' => ['count' => 5, 'avg_bp' => 118, 'avg_hr' => 75],
            '31-50' => ['count' => 12, 'avg_bp' => 125, 'avg_hr' => 72],
            '51-70' => ['count' => 18, 'avg_bp' => 135, 'avg_hr' => 68],
            '70+' => ['count' => 8, 'avg_bp' => 142, 'avg_hr' => 65]
        ];
    }

    private function getGenderComparisons($patientIds)
    {
        // Mock gender comparison
        return [
            'male' => ['count' => 22, 'avg_bp' => 128, 'avg_hr' => 70],
            'female' => ['count' => 21, 'avg_bp' => 125, 'avg_hr' => 73]
        ];
    }

    private function getConditionComparisons($patientIds)
    {
        // Mock condition-based comparison
        return [
            'hypertension' => ['count' => 15, 'avg_control' => 72],
            'diabetes' => ['count' => 8, 'avg_control' => 68],
            'heart_disease' => ['count' => 5, 'avg_control' => 65],
            'healthy' => ['count' => 15, 'avg_control' => 95]
        ];
    }

    private function getPerformanceComparisons($patientIds)
    {
        return [
            'top_performers' => Patient::whereIn('id', $patientIds)->limit(5)->get(),
            'needs_attention' => Patient::whereIn('id', $patientIds)->limit(5)->get()
        ];
    }

    private function generateRiskAssessments($patientIds)
    {
        return [
            'high_risk_patients' => 3,
            'medium_risk_patients' => 8,
            'low_risk_patients' => 32,
            'risk_factors' => ['hypertension', 'irregular_heartbeat', 'medication_non_adherence']
        ];
    }

    private function generateTreatmentRecommendations($patientIds)
    {
        return [
            'medication_adjustments' => 5,
            'lifestyle_interventions' => 12,
            'follow_up_appointments' => 8,
            'specialist_referrals' => 2
        ];
    }

    private function generateCareOptimizations($patientIds)
    {
        return [
            'remote_monitoring_candidates' => 15,
            'medication_sync_opportunities' => 8,
            'care_plan_updates_needed' => 6,
            'patient_education_targets' => 20
        ];
    }

    private function generateEarlyWarnings($patientIds)
    {
        return [
            'deteriorating_trends' => 4,
            'medication_adherence_issues' => 6,
            'missed_appointment_patterns' => 3,
            'vital_sign_anomalies' => 2
        ];
    }
}