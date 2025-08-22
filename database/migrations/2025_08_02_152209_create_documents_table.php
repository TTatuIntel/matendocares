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
        Schema::create('documents', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('patient_id', 36);
            $table->char('doctor_id', 36)->nullable();
            $table->char('uploaded_by', 36)->nullable();

            // New fields from your table
            $table->string('upload_source', 255)->nullable();
            $table->unsignedBigInteger('temp_access_id')->nullable();

            // Document metadata
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('file_name', 255);
            $table->string('file_type', 255);
            $table->bigInteger('file_size');
            $table->longText('file_data');
            $table->string('file_hash', 255);

            // Categorization
            $table->enum('category', [
                'lab_report',
                'imaging',
                'prescription',
                'insurance',
                'consultation_note',
                'discharge_summary',
                'referral',
                'consent_form',
                'other'
            ]);

            $table->text('tags')->nullable();
            $table->boolean('is_confidential')->default(false);
            $table->enum('status', ['active', 'archived', 'deleted'])->default('active');
            $table->text('metadata')->nullable();

            // Timestamps
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            // Indexes (updated to match your table exactly)
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('uploaded_by');
            $table->index('temp_access_id');
            $table->index('file_hash');
            $table->index('category');
            $table->index('created_at');

            // Foreign key constraints
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('set null');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
