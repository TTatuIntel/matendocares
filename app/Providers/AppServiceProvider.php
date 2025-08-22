<?php



use App\Models\User;
use App\Observers\DoctorObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
public function boot(): void
    {
        // Register the User observer
        User::observe(DoctorObserver::class);

Notification::extend('custom_appointment', function ($app) {
        return new \App\Channels\AppointmentNotificationChannel();
    });
    }

// app/Providers/AppServiceProvider.php

}
