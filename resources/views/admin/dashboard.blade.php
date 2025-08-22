<?php
// resources/views/admin/dashboard.blade.php
?>
@extends('admin.layout')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Admin Dashboard</h2>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="text-blue-500 text-3xl mr-4">👥</div>
                <div>
                    <p class="text-gray-500 text-sm">Total Users</p>
                    <p class="text-2xl font-bold">{{ $totalUsers }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="text-green-500 text-3xl mr-4">👨‍⚕️</div>
                <div>
                    <p class="text-gray-500 text-sm">Active Doctors</p>
                    <p class="text-2xl font-bold">{{ $activeDoctors }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="text-purple-500 text-3xl mr-4">🏥</div>
                <div>
                    <p class="text-gray-500 text-sm">Total Patients</p>
                    <p class="text-2xl font-bold">{{ $totalPatients }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="text-red-500 text-3xl mr-4">🚨</div>
                <div>
                    <p class="text-gray-500 text-sm">Active Alerts</p>
                    <p class="text-2xl font-bold">{{ $activeAlerts }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Monthly Registrations</h3>
            <canvas id="registrationsChart"></canvas>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">System Activity</h3>
            <canvas id="activityChart"></canvas>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold">Recent Activities</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($recentActivities as $activity)
                <div class="flex items-center justify-between py-2 border-b">
                    <div>
                        <p class="font-medium">{{ $activity->description }}</p>
                        <p class="text-sm text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                        {{ $activity->type }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
// Charts initialization
const ctx1 = document.getElementById('registrationsChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: {!! json_encode($registrationLabels) !!},
        datasets: [{
            label: 'New Registrations',
            data: {!! json_encode($registrationData) !!},
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

const ctx2 = document.getElementById('activityChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['Doctors', 'Patients', 'Admins'],
        datasets: [{
            data: [{{ $activeDoctors }}, {{ $totalPatients }}, {{ $totalAdmins }}],
            backgroundColor: ['#10B981', '#8B5CF6', '#F59E0B']
        }]
    },
    options: {
        responsive: true
    }
});
</script>
@endsection
