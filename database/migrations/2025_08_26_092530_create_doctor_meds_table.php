<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('doctor_meds', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT(20) UNSIGNED AUTO_INCREMENT
            $table->char('user_id', 36); // Patient ID
            $table->char('doctor_id', 36)->nullable(); // Doctor ID (nullable)
            $table->unsignedBigInteger('temp_access_id')->nullable(); // Missing field
            $table->string('name', 255);
            $table->string('generic_name', 255)->nullable();
            $table->string('dosage', 255)->nullable();
            $table->string('frequency', 255)->nullable();
            $table->longText('times')->nullable();
            $table->date('start_date')->default('2025-08-18');
            $table->string('prescribed_by', 255)->nullable();
            $table->string('purpose', 255)->nullable();
            $table->text('instructions')->nullable();
            $table->integer('refills')->default(0);
            $table->string('status', 255)->default('active');
            $table->text('health_tips')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('temp_access_id')->references('id')->on('temp_accesses')->onDelete('cascade');

            // Indexes
            $table->index('user_id');
            $table->index('doctor_id');
            $table->index('temp_access_id');
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('doctor_meds');
    }
};
