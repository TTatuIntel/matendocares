<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DoctorObserver
{
    public function created(User $user)
    {
        $this->handleRoleChange($user, null, $user->role);
    }

    public function updated(User $user)
    {
        if ($user->isDirty('role')) {
            $originalRole = $user->getOriginal('role');
            $newRole = $user->role;
            $this->handleRoleChange($user, $originalRole, $newRole);
        }
    }

    protected function handleRoleChange(User $user, $originalRole, $newRole)
    {
        DB::transaction(function () use ($user, $originalRole, $newRole) {
            // Remove from old role table
            if ($originalRole === 'doctor') {
                Doctor::where('user_id', $user->id)->delete();
                Log::info("Removed doctor record for user: {$user->id}");
            } 
            elseif ($originalRole === 'patient') {
                Patient::where('user_id', $user->id)->delete();
                Log::info("Removed patient record for user: {$user->id}");
            }

            // Add to new role table
            if ($newRole === 'doctor') {
                Doctor::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'specialization' => 'General',
                        'license_number' => 'MD-' . strtoupper(Str::random(6))
                    ]
                );
                Log::info("Created doctor record for user: {$user->id}");
            } 
            elseif ($newRole === 'patient') {
                Patient::firstOrCreate(
                    ['user_id' => $user->id],
                    ['medical_record_number' => 'MRN-' . strtoupper(Str::random(8))]
                );
                Log::info("Created patient record for user: {$user->id}");
            }
        });
    }

    public function deleted(User $user)
    {
        Doctor::where('user_id', $user->id)->delete();
        Patient::where('user_id', $user->id)->delete();
        Log::info("Cleaned up role records for deleted user: {$user->id}");
    }
}