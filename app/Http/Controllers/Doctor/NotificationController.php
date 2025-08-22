<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return view('doctor.notifications', compact('notifications'));
    }

public function notifications()
    {
        // Logic to fetch and display notifications
        return view('doctor.notifications.index');
    }
}
