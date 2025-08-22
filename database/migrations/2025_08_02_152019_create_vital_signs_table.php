<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_id');
            
            // Primary Vitals - Blood Pressure
            $table->decimal('systolic_bp', 5, 2)->nullable();
            $table->decimal('diastolic_bp', 5, 2)->nullable();
            $table->string('blood_pressure')->nullable(); // Combined format like "120/80"
            
            // Primary Vitals - Heart Rate
            $table->decimal('heart_rate', 5, 2)->nullable();
            $table->decimal('resting_heart_rate', 5, 2)->nullable();
            
            // Primary Vitals - Blood Glucose
            $table->decimal('blood_glucose', 6, 2)->nullable(); // Increased precision for glucose
            $table->enum('glucose_unit', ['mg/dl', 'mmol/l'])->default('mg/dl');
            $table->enum('glucose_type', ['fasting', 'random'])->default('fasting');
            $table->string('original_glucose_unit')->nullable(); // Track original input unit
            
            // Physical Measurements - Temperature
            $table->decimal('temperature', 5, 2)->nullable();
            $table->enum('temperature_unit', ['celsius', 'fahrenheit'])->default('fahrenheit');
            
            // Physical Measurements - Weight & Height
            $table->decimal('weight', 6, 2)->nullable();
            $table->enum('weight_unit', ['kg', 'lbs'])->default('lbs');
            $table->decimal('height', 6, 2)->nullable();
            $table->enum('height_unit', ['cm', 'inches'])->default('inches');
            $table->decimal('bmi', 5, 2)->nullable(); // Calculated field
            
            // Physical Measurements - Respiratory
            $table->decimal('oxygen_saturation', 5, 2)->nullable();
            $table->integer('respiratory_rate')->nullable();
            
            // Activity & Sleep Measurements
            $table->integer('steps')->nullable();
            $table->decimal('sleep_hours', 4, 2)->nullable();
            
            // Wellness Assessment
            $table->enum('mood', ['excellent', 'good', 'fair', 'poor', 'very_poor'])->nullable();
            $table->integer('energy_level')->nullable(); // 1-10 scale
            $table->integer('pain_level')->nullable(); // 0-10 scale
            
            // Symptoms and Clinical Notes
            $table->json('symptoms')->nullable(); // Array of current symptoms
            $table->text('notes')->nullable(); // User notes
            $table->text('clinical_notes')->nullable(); // Healthcare provider notes
            
            // Risk Assessment (Auto-generated)
            $table->enum('status', ['normal', 'caution', 'warning', 'critical'])->default('normal');
            $table->enum('risk_level', ['normal', 'borderline', 'high_risk', 'critical'])->default('normal');
            $table->integer('risk_score')->default(0); // 0-100 scale
            $table->text('risk_assessment_notes')->nullable(); // Auto-generated assessment
            
            // Data Entry Information
            $table->enum('entry_method', ['manual', 'device', 'api', 'import'])->default('manual');
            $table->string('device_type')->nullable(); // If entered via device
            $table->json('device_metadata')->nullable(); // Device-specific data
            $table->timestamp('measured_at'); // When vitals were taken
            $table->uuid('recorded_by')->nullable(); // Who entered the data
            
            // Medical Review
            $table->boolean('reviewed_by_doctor')->default(false);
            $table->uuid('reviewed_by')->nullable(); // Doctor who reviewed
            $table->timestamp('reviewed_at')->nullable();
            $table->text('doctor_notes')->nullable();
            
            // Data Quality & Validation
            $table->boolean('validated')->default(true);
            $table->json('validation_flags')->nullable(); // Any data quality issues
            $table->boolean('requires_attention')->default(false);
            
            // System Fields
            $table->softDeletes();
            $table->timestamps();
            
            // Foreign Key Constraints
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null'); // Changed to users table
            
            // Indexes for Performance
            $table->index(['patient_id', 'measured_at']);
            $table->index(['status', 'reviewed_by_doctor']);
            $table->index(['risk_level', 'requires_attention']);
            $table->index(['measured_at']);
            $table->index(['patient_id', 'risk_level']);
            $table->index(['entry_method', 'measured_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('vital_signs');
    }
};