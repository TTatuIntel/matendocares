<?php

// app/Http/Controllers/Admin/AdminDashboardController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Alert;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $activeDoctors = Doctor::whereHas('user', function($q) {
            $q->where('status', 'active');
        })->count();
        $totalPatients = Patient::count();
        $activeAlerts = Alert::where('status', 'active')
            ->where('severity', 'critical')
            ->count();
        $totalAdmins = User::where('role', 'admin')->count();

        // Registration data for chart
        $registrationData = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $registrationLabels = $registrationData->pluck('date')->map(function($date) {
            return Carbon::parse($date)->format('M d');
        });
        $registrationCounts = $registrationData->pluck('count');

        // Recent activities
        $recentActivities = ActivityLog::with('causer')
            ->latest()
            ->take(10)
            ->get()
            ->map(function($activity) {
                return (object) [
                    'description' => $activity->description,
                    'type' => $activity->log_name ?? 'System',
                    'created_at' => $activity->created_at
                ];
            });

        return view('admin.dashboard', compact(
            'totalUsers', 'activeDoctors', 'totalPatients', 'activeAlerts', 'totalAdmins',
            'registrationLabels', 'registrationData', 'recentActivities'
        ));
    }
}