<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2024_01_01_000005_create_medical_records_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_id');
            $table->uuid('doctor_id')->nullable();
            $table->enum('record_type', ['vital_signs', 'symptoms', 'diagnosis', 'prescription', 'lab_result', 'imaging', 'procedure', 'note']);
            $table->json('data'); // Flexible data storage for different record types
            $table->text('notes')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->nullable();
            $table->boolean('requires_attention')->default(false);
            $table->timestamp('recorded_at');
            $table->uuid('recorded_by')->nullable(); // User who recorded this
            $table->enum('status', ['active', 'archived', 'deleted'])->default('active');
            $table->json('attachments')->nullable(); // Base64 encoded files or file paths
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('set null');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['patient_id', 'record_type', 'recorded_at']);
            $table->index(['recorded_at', 'severity']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('medical_records');
    }
};