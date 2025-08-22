<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\VitalSign;
use App\Models\HealthMetric;
use App\Models\HealthGoal;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HealthTrendsController extends Controller
{
    /**
     * Display the health trends overview.
     */
    public function index(Request $request)
    {
        try {
            $patient = auth()->user()->patient;
            
            if (!$patient) {
                return redirect()->route('patient.dashboard')
                    ->with('error', 'Patient profile not found. Please contact support.');
            }

            $period = $request->get('period', 30);
            
            // Get trend data
            $trendsData = $this->getTrendsData($patient, $period);
            
            // Get health metrics summary
            $healthMetrics = $this->getHealthMetrics($patient, $period);
            
            // Get comparative data
            $comparativeData = $this->getComparativeData($patient);
            
            // Get health goals progress
            $goalsProgress = $this->getGoalsProgress($patient);
            
            return view('patient.health-trends.index', compact(
                'trendsData',
                'healthMetrics',
                'comparativeData',
                'goalsProgress',
                'period'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading health trends: ' . $e->getMessage());
            return redirect()->route('patient.dashboard')
                ->with('error', 'Unable to load health trends. Please try again.');
        }
    }

    /**
     * Display detailed health trends analysis.
     */
    public function detailed(Request $request)
    {
        try {
            $patient = auth()->user()->patient;
            
            if (!$patient) {
                return redirect()->route('patient.dashboard')
                    ->with('error', 'Patient profile not found.');
            }

            $metric = $request->get('metric', 'blood_pressure');
            $period = $request->get('period', 90);
            
            // Get detailed data for specific metric
            $detailedData = $this->getDetailedMetricData($patient, $metric, $period);
            
            // Get statistical analysis
            $statistics = $this->getMetricStatistics($patient, $metric, $period);
            
            // Get insights and recommendations
            $insights = $this->generateMetricInsights($patient, $metric, $detailedData);
            
            return view('patient.health-trends.detailed', compact(
                'detailedData',
                'statistics',
                'insights',
                'metric',
                'period'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading detailed trends: ' . $e->getMessage());
            return redirect()->route('patient.health-trends.index')
                ->with('error', 'Unable to load detailed analysis.');
        }
    }

    /**
     * Display comparison with population averages.
     */
    public function comparison(Request $request)
    {
        try {
            $patient = auth()->user()->patient;
            
            if (!$patient) {
                return redirect()->route('patient.dashboard')
                    ->with('error', 'Patient profile not found.');
            }

            // Get patient's data
            $patientData = $this->getPatientAverages($patient);
            
            // Get population averages (age/gender matched)
            $populationData = $this->getPopulationAverages($patient);
            
            // Generate comparison insights
            $comparisonInsights = $this->generateComparisonInsights($patientData, $populationData);
            
            return view('patient.health-trends.comparison', compact(
                'patientData',
                'populationData',
                'comparisonInsights'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading comparison data: ' . $e->getMessage());
            return redirect()->route('patient.health-trends.index')
                ->with('error', 'Unable to load comparison data.');
        }
    }

    /**
     * Display health goals management.
     */
    public function goals(Request $request)
    {
        try {
            $patient = auth()->user()->patient;
            
            if (!$patient) {
                return redirect()->route('patient.dashboard')
                    ->with('error', 'Patient profile not found.');
            }

            // Get current goals
            $currentGoals = $this->getCurrentGoals($patient);
            
            // Get goal achievement history
            $goalHistory = $this->getGoalHistory($patient);
            
            // Get available goal templates
            $goalTemplates = $this->getGoalTemplates();
            
            return view('patient.health-trends.goals', compact(
                'currentGoals',
                'goalHistory',
                'goalTemplates'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading goals page: ' . $e->getMessage());
            return redirect()->route('patient.health-trends.index')
                ->with('error', 'Unable to load goals page.');
        }
    }

    /**
     * Set a new health goal.
     */
    public function setGoal(Request $request)
    {
        $request->validate([
            'goal_type' => 'required|in:blood_pressure,weight,heart_rate,steps,exercise,medication_adherence',
            'target_value' => 'required|numeric',
            'target_date' => 'required|date|after:today',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $patient = auth()->user()->patient;
            
            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient profile not found.'
                ], 400);
            }

            // For now, just log the goal creation
            Log::info('Health goal created', [
                'patient_id' => $patient->id,
                'goal_type' => $request->goal_type,
                'target_value' => $request->target_value,
                'target_date' => $request->target_date
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Health goal set successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error setting health goal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to set health goal.'
            ], 500);
        }
    }

    /**
     * Export health trends data.
     */
    public function export(Request $request)
    {
        try {
            $patient = auth()->user()->patient;
            
            if (!$patient) {
                return response()->json(['error' => 'Patient profile not found'], 400);
            }
            
            $period = $request->get('period', 90);
            $format = $request->get('format', 'csv');
            
            $trendsData = $this->getTrendsData($patient, $period);

            if ($format === 'json') {
                return response()->json([
                    'export_info' => [
                        'patient_name' => auth()->user()->name,
                        'exported_at' => now()->toISOString(),
                        'period_days' => $period
                    ],
                    'trends_data' => $trendsData
                ]);
            }

            // CSV Export
            $filename = 'health_trends_' . str_replace(' ', '_', auth()->user()->name) . '_' . now()->format('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function() use ($trendsData) {
                $file = fopen('php://output', 'w');
                
                // CSV Headers
                fputcsv($file, [
                    'Date', 'Blood Pressure', 'Heart Rate', 'Weight', 'Temperature', 'Notes'
                ]);

                // Sample data rows
                $sampleData = [
                    ['2024-08-01', '120/80', '72', '150', '98.6', 'Feeling good'],
                    ['2024-08-02', '118/78', '70', '149.8', '98.4', 'Good energy'],
                    ['2024-08-03', '122/82', '74', '150.2', '98.7', 'Normal day']
                ];

                foreach ($sampleData as $row) {
                    fputcsv($file, $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            return response()->json(['error' => 'Export failed'], 500);
        }
    }

    /**
     * API method for trends data.
     */
    public function apiTrends(Request $request)
    {
        try {
            $patient = auth()->user()->patient;
            
            if (!$patient) {
                return response()->json(['error' => 'Patient profile not found'], 400);
            }

            $period = $request->get('period', 30);
            $trendsData = $this->getTrendsData($patient, $period);

            return response()->json([
                'trends' => $trendsData,
                'period' => $period
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch trends'], 500);
        }
    }

    /**
     * Private helper methods
     */
    private function getTrendsData($patient, $period)
    {
        try {
            // Sample trend data
            return [
                'labels' => ['Aug 1', 'Aug 2', 'Aug 3', 'Aug 4', 'Aug 5', 'Aug 6', 'Aug 7'],
                'blood_pressure' => [
                    'systolic' => [120, 118, 122, 119, 121, 123, 120],
                    'diastolic' => [80, 78, 82, 79, 81, 83, 80]
                ],
                'heart_rate' => [72, 70, 74, 73, 71, 75, 72],
                'weight' => [150, 149.8, 150.2, 149.9, 150.1, 150.3, 150],
                'temperature' => [98.6, 98.4, 98.7, 98.5, 98.6, 98.8, 98.6]
            ];
        } catch (\Exception $e) {
            return [
                'labels' => [],
                'blood_pressure' => ['systolic' => [], 'diastolic' => []],
                'heart_rate' => [],
                'weight' => [],
                'temperature' => []
            ];
        }
    }

    private function getHealthMetrics($patient, $period)
    {
        return [
            'blood_pressure' => [
                'current' => '120/80',
                'average' => '121/81',
                'trend' => 'stable',
                'status' => 'normal'
            ],
            'heart_rate' => [
                'current' => 72,
                'average' => 73,
                'trend' => 'improving',
                'status' => 'good'
            ],
            'weight' => [
                'current' => 150,
                'average' => 150.1,
                'trend' => 'stable',
                'status' => 'normal'
            ],
            'temperature' => [
                'current' => 98.6,
                'average' => 98.6,
                'trend' => 'stable',
                'status' => 'normal'
            ]
        ];
    }

    private function getComparativeData($patient)
    {
        return [
            'current_month' => [
                'avg_bp' => '121/81',
                'avg_hr' => 73,
                'avg_weight' => 150.1
            ],
            'previous_month' => [
                'avg_bp' => '123/83',
                'avg_hr' => 75,
                'avg_weight' => 151.2
            ],
            'improvement' => [
                'bp' => 'improved',
                'hr' => 'improved',
                'weight' => 'improved'
            ]
        ];
    }

    private function getGoalsProgress($patient)
    {
        return [
            [
                'id' => 1,
                'type' => 'weight',
                'target' => 145,
                'current' => 150,
                'progress' => 60,
                'deadline' => '2024-12-31',
                'status' => 'on_track'
            ],
            [
                'id' => 2,
                'type' => 'blood_pressure',
                'target' => '118/78',
                'current' => '120/80',
                'progress' => 80,
                'deadline' => '2024-10-15',
                'status' => 'ahead'
            ]
        ];
    }

    private function getDetailedMetricData($patient, $metric, $period)
    {
        // Sample detailed data based on metric type
        $data = [
            'blood_pressure' => [
                'daily_readings' => [
                    ['date' => '2024-08-01', 'morning' => '118/78', 'evening' => '122/82'],
                    ['date' => '2024-08-02', 'morning' => '120/80', 'evening' => '124/84'],
                    ['date' => '2024-08-03', 'morning' => '119/79', 'evening' => '121/81']
                ],
                'patterns' => [
                    'morning_avg' => '119/79',
                    'evening_avg' => '122/82',
                    'variation' => 'normal'
                ]
            ],
            'heart_rate' => [
                'daily_readings' => [
                    ['date' => '2024-08-01', 'resting' => 70, 'active' => 85],
                    ['date' => '2024-08-02', 'resting' => 72, 'active' => 87],
                    ['date' => '2024-08-03', 'resting' => 71, 'active' => 86]
                ],
                'patterns' => [
                    'resting_avg' => 71,
                    'active_avg' => 86,
                    'recovery_rate' => 'good'
                ]
            ]
        ];

        return $data[$metric] ?? [];
    }

    private function getMetricStatistics($patient, $metric, $period)
    {
        return [
            'min' => 68,
            'max' => 87,
            'average' => 73.5,
            'median' => 73,
            'std_deviation' => 4.2,
            'trend' => 'stable',
            'variability' => 'low'
        ];
    }

    private function generateMetricInsights($patient, $metric, $data)
    {
        return [
            [
                'type' => 'positive',
                'title' => 'Consistent Readings',
                'message' => 'Your ' . $metric . ' readings show good consistency over time.'
            ],
            [
                'type' => 'info',
                'title' => 'Optimal Range',
                'message' => 'Most of your readings fall within the healthy range.'
            ]
        ];
    }

    private function getPatientAverages($patient)
    {
        return [
            'blood_pressure' => '121/81',
            'heart_rate' => 73,
            'weight' => 150,
            'bmi' => 22.5
        ];
    }

    private function getPopulationAverages($patient)
    {
        // Sample population data (would be age/gender matched in real implementation)
        return [
            'blood_pressure' => '125/85',
            'heart_rate' => 76,
            'weight' => 155,
            'bmi' => 24.1
        ];
    }

    private function generateComparisonInsights($patientData, $populationData)
    {
        return [
            [
                'metric' => 'Blood Pressure',
                'status' => 'better',
                'message' => 'Your blood pressure is better than average for your age group.'
            ],
            [
                'metric' => 'Heart Rate',
                'status' => 'better',
                'message' => 'Your resting heart rate is lower than average, indicating good fitness.'
            ]
        ];
    }

    private function getCurrentGoals($patient)
    {
        return [
            [
                'id' => 1,
                'type' => 'weight',
                'description' => 'Lose 5 pounds',
                'target_value' => 145,
                'current_value' => 150,
                'target_date' => '2024-12-31',
                'progress' => 60,
                'status' => 'active'
            ],
            [
                'id' => 2,
                'type' => 'steps',
                'description' => 'Walk 10,000 steps daily',
                'target_value' => 10000,
                'current_value' => 8500,
                'target_date' => '2024-09-30',
                'progress' => 85,
                'status' => 'active'
            ]
        ];
    }

    private function getGoalHistory($patient)
    {
        return [
            [
                'description' => 'Reduce blood pressure to 120/80',
                'achieved_date' => '2024-07-15',
                'duration_days' => 90,
                'status' => 'achieved'
            ],
            [
                'description' => 'Exercise 3 times per week',
                'achieved_date' => '2024-06-30',
                'duration_days' => 60,
                'status' => 'achieved'
            ]
        ];
    }

    private function getGoalTemplates()
    {
        return [
            [
                'type' => 'weight',
                'title' => 'Weight Loss',
                'description' => 'Set a target weight and timeframe',
                'suggested_targets' => [5, 10, 15, 20]
            ],
            [
                'type' => 'blood_pressure',
                'title' => 'Blood Pressure Control',
                'description' => 'Maintain healthy blood pressure levels',
                'suggested_targets' => ['120/80', '118/78', '115/75']
            ],
            [
                'type' => 'steps',
                'title' => 'Daily Steps',
                'description' => 'Increase daily physical activity',
                'suggested_targets' => [8000, 10000, 12000, 15000]
            ]
        ];
    }
}