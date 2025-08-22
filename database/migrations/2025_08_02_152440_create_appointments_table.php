<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();  // doctor’s user_id
            $table->uuid('patient_id');
            $table->uuid('doctor_id')->nullable(); // nullable FK to users.id
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', [
                'consultation',
                'follow_up',
                'emergency',
                'routine_checkup',
                'specialist_referral',
                'telemedicine'
            ]);
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(30);
            $table->string('status')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->uuid('cancelled_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->longText('notes')->nullable();
            $table->longText('vital_signs_taken')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('prescriptions')->nullable();
            $table->dateTime('next_appointment_recommended')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_telemedicine')->default(false);
            $table->string('meeting_link')->nullable();
            $table->longText('reminders_sent')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('set null'); // ✅ changed to users
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['doctor_id', 'scheduled_at']);
            $table->index(['patient_id', 'scheduled_at']);
            $table->index(['status', 'scheduled_at']);
            $table->index('scheduled_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
