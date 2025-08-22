<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ActivityController extends Controller
{
    /**
     * Display the activities index page
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $patient = $user->patient;

            if (!$patient) {
                return redirect()->route('patient.dashboard')
                    ->with('error', 'Patient profile not found.');
            }

            // Get activity statistics
            $stats = $this->getActivityStats($user);
            
            // Get recent activities
            $recentActivities = $this->getRecentActivities($user);
            
            // Get weekly summary
            $weeklySummary = $this->getWeeklySummary($user);

            return view('patient.activities.index', compact(
                'stats',
                'recentActivities', 
                'weeklySummary'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading activities page: ' . $e->getMessage());
            return redirect()->route('patient.dashboard')
                ->with('error', 'Unable to load activities. Please try again.');
        }
    }

    /**
     * Store a new activity
     */
    public function store(Request $request)
    {
        $request->validate([
            'activity_type' => 'required|in:exercise,walk,run,swim,cycle,yoga,strength,cardio,other',
            'duration_minutes' => 'required|numeric|min:1|max:600',
            'intensity' => 'required|in:low,moderate,high',
            'calories_burned' => 'nullable|numeric|min:0|max:2000',
            'notes' => 'nullable|string|max:500',
            'date' => 'nullable|date|before_or_equal:today'
        ]);

        try {
            $user = Auth::user();
            $patient = $user->patient;

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient profile not found.'
                ], 400);
            }

            // For demo purposes, just log the activity
            Log::info('Activity logged', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'activity_type' => $request->activity_type,
                'duration' => $request->duration_minutes,
                'intensity' => $request->intensity,
                'date' => $request->date ?? now()->toDateString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Activity logged successfully!',
                'activity' => [
                    'id' => rand(1, 1000),
                    'activity_type' => $request->activity_type,
                    'duration_minutes' => $request->duration_minutes,
                    'intensity' => $request->intensity,
                    'calories_burned' => $request->calories_burned ?? $this->estimateCalories($request->activity_type, $request->duration_minutes, $request->intensity),
                    'date' => $request->date ?? now()->toDateString(),
                    'created_at' => now()->toDateTimeString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error storing activity: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to log activity. Please try again.'
            ], 500);
        }
    }

    /**
     * Log activity (alias for store)
     */
    public function logActivity(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Get activity statistics
     */
    public function stats()
    {
        try {
            $user = Auth::user();
            $stats = $this->getActivityStats($user);

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching activity stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.'
            ], 500);
        }
    }

    /**
     * Get activity history
     */
    public function history(Request $request)
    {
        try {
            $user = Auth::user();
            $days = $request->get('days', 30);
            
            $activities = $this->getActivityHistory($user, $days);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'activities' => $activities,
                    'period' => $days
                ]);
            }

            return view('patient.activities.history', compact('activities', 'days'));

        } catch (\Exception $e) {
            Log::error('Error fetching activity history: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch activity history.'
                ], 500);
            }

            return redirect()->route('patient.activities.index')
                ->with('error', 'Unable to load activity history.');
        }
    }

    /**
     * Export activities data
     */
    public function export(Request $request)
    {
        try {
            $user = Auth::user();
            $format = $request->get('format', 'csv');
            $days = $request->get('days', 90);
            
            $activities = $this->getActivityHistory($user, $days);

            if ($format === 'json') {
                return response()->json([
                    'export_info' => [
                        'user_name' => $user->name,
                        'exported_at' => now()->toISOString(),
                        'period_days' => $days
                    ],
                    'activities' => $activities
                ]);
            }

            // CSV Export
            $filename = 'activities_' . str_replace(' ', '_', $user->name) . '_' . now()->format('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function() use ($activities) {
                $file = fopen('php://output', 'w');
                
                // CSV Headers
                fputcsv($file, [
                    'Date', 'Activity Type', 'Duration (minutes)', 'Intensity', 
                    'Calories Burned', 'Notes'
                ]);

                foreach ($activities as $activity) {
                    fputcsv($file, [
                        $activity['date'],
                        ucfirst($activity['activity_type']),
                        $activity['duration_minutes'],
                        ucfirst($activity['intensity']),
                        $activity['calories_burned'] ?? 0,
                        $activity['notes'] ?? ''
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Activity export error: ' . $e->getMessage());
            return response()->json(['error' => 'Export failed'], 500);
        }
    }

    /**
     * Private helper methods
     */
    private function getActivityStats($user)
    {
        // Demo data - in real implementation, fetch from database
        return [
            'total_activities_this_week' => 5,
            'total_minutes_this_week' => 180,
            'total_calories_this_week' => 850,
            'average_daily_minutes' => 26,
            'most_common_activity' => 'Walking',
            'weekly_goal_progress' => 75, // percentage
            'streak_days' => 3,
            'total_activities_this_month' => 18,
            'total_minutes_this_month' => 720,
            'favorite_intensity' => 'Moderate'
        ];
    }

    private function getRecentActivities($user, $limit = 10)
    {
        // Demo data - in real implementation, fetch from database
        return [
            [
                'id' => 1,
                'activity_type' => 'walk',
                'duration_minutes' => 30,
                'intensity' => 'moderate',
                'calories_burned' => 120,
                'date' => now()->subDays(1)->format('Y-m-d'),
                'notes' => 'Morning walk in the park',
                'created_at' => now()->subDays(1)->format('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'activity_type' => 'yoga',
                'duration_minutes' => 45,
                'intensity' => 'low',
                'calories_burned' => 90,
                'date' => now()->subDays(2)->format('Y-m-d'),
                'notes' => 'Evening yoga session',
                'created_at' => now()->subDays(2)->format('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'activity_type' => 'run',
                'duration_minutes' => 25,
                'intensity' => 'high',
                'calories_burned' => 250,
                'date' => now()->subDays(3)->format('Y-m-d'),
                'notes' => 'Quick morning jog',
                'created_at' => now()->subDays(3)->format('Y-m-d H:i:s')
            ]
        ];
    }

    private function getWeeklySummary($user)
    {
        // Demo data for weekly summary
        return [
            'monday' => ['minutes' => 30, 'activities' => 1],
            'tuesday' => ['minutes' => 45, 'activities' => 1],
            'wednesday' => ['minutes' => 0, 'activities' => 0],
            'thursday' => ['minutes' => 25, 'activities' => 1],
            'friday' => ['minutes' => 60, 'activities' => 2],
            'saturday' => ['minutes' => 20, 'activities' => 1],
            'sunday' => ['minutes' => 0, 'activities' => 0]
        ];
    }

    private function getActivityHistory($user, $days)
    {
        // Demo data - in real implementation, fetch from database
        $activities = [];
        
        for ($i = 0; $i < min($days, 30); $i++) {
            if (rand(0, 2) > 0) { // Random chance of activity
                $activities[] = [
                    'id' => $i + 1,
                    'activity_type' => ['walk', 'run', 'yoga', 'swim', 'cycle'][rand(0, 4)],
                    'duration_minutes' => rand(15, 90),
                    'intensity' => ['low', 'moderate', 'high'][rand(0, 2)],
                    'calories_burned' => rand(50, 300),
                    'date' => now()->subDays($i)->format('Y-m-d'),
                    'notes' => '',
                    'created_at' => now()->subDays($i)->format('Y-m-d H:i:s')
                ];
            }
        }

        return array_reverse($activities);
    }

    private function estimateCalories($activityType, $minutes, $intensity)
    {
        // Simple calorie estimation based on activity type and intensity
        $baseCaloriesPerMinute = [
            'walk' => 3,
            'run' => 8,
            'swim' => 6,
            'cycle' => 5,
            'yoga' => 2,
            'strength' => 4,
            'cardio' => 7,
            'exercise' => 5,
            'other' => 3
        ];

        $intensityMultiplier = [
            'low' => 0.8,
            'moderate' => 1.0,
            'high' => 1.3
        ];

        $base = $baseCaloriesPerMinute[$activityType] ?? 3;
        $multiplier = $intensityMultiplier[$intensity] ?? 1.0;

        return round($base * $minutes * $multiplier);
    }
}