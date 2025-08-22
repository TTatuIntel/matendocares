<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('temp_accesses', function (Blueprint $table) {
            $table->id();
            
            // Core relationship fields
            $table->uuid('patient_id');
            $table->uuid('generated_by');
            
            // Access control fields
            $table->string('token', 64)->unique();
            $table->string('verification_code', 8);
            $table->boolean('is_active')->default(true);
            $table->boolean('doctor_verified')->default(false);
            $table->integer('access_count')->default(0);
            
            // Doctor information (filled after verification)
            $table->string('doctor_name')->nullable();
            $table->string('doctor_specialty')->nullable();
            $table->string('doctor_facility')->nullable();
            $table->string('doctor_phone')->nullable();
            
            // Timestamps for access tracking
            $table->timestamp('expires_at');
            $table->timestamp('accessed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            
            // Configuration and metadata
            $table->json('permissions')->nullable();
            $table->text('access_reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('revocation_reason')->nullable();
            
            // Audit fields
            $table->ipAddress('last_access_ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->integer('failed_verification_attempts')->default(0);
            
            $table->timestamps();

            // Foreign key constraints with proper UUID references
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('generated_by')->references('id')->on('users')->onDelete('cascade');
            
            // Performance indexes
            $table->index(['token', 'is_active']);
            $table->index(['patient_id', 'is_active']);
            $table->index(['expires_at']);
            $table->index(['doctor_verified', 'is_active']);
            $table->index(['verification_code']);
            $table->index(['created_at', 'expires_at']);
            
            // Compound indexes for common queries
            $table->index(['patient_id', 'is_active', 'expires_at']);
            $table->index(['doctor_verified', 'is_active', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_accesses');
    }
};