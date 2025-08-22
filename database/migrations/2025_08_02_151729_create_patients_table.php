<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2024_01_01_000003_create_patients_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('medical_record_number')->unique();
            $table->string('blood_type')->nullable();
            $table->decimal('height', 5, 2)->nullable(); // in cm
            $table->decimal('current_weight', 5, 2)->nullable(); // in kg
            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->text('current_medications')->nullable();
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->text('family_medical_history')->nullable();
            $table->enum('activity_level', ['sedentary', 'lightly_active', 'moderately_active', 'very_active', 'extremely_active'])->nullable();
            $table->boolean('smoker')->default(false);
            $table->integer('alcohol_consumption')->default(0); // drinks per week
            $table->text('dietary_restrictions')->nullable();
            $table->json('emergency_contacts')->nullable(); // Store multiple emergency contacts
            $table->decimal('baseline_heart_rate', 5, 2)->nullable();
            $table->string('baseline_blood_pressure')->nullable();
            $table->decimal('baseline_temperature', 4, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('medical_record_number');
            $table->index(['blood_type', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('patients');
    }
};
