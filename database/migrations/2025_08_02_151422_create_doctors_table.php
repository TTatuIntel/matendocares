<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('license_number')->unique()->nullable();
            $table->string('specialization');
            $table->text('qualifications')->nullable();
            $table->string('hospital_affiliation')->nullable();
            $table->integer('years_experience')->default(0);
            $table->text('bio')->nullable();
            $table->longText('available_hours')->nullable(); // Changed from json to longtext
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->decimal('consultation_fee', 8, 2)->nullable();
            $table->boolean('accepts_emergency_calls')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            $table->string('status')->default('active');
            $table->boolean('is_verified')->default(true);

            // Foreign keys and indexes
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->index('specialization');
            $table->index('verification_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctors');
    }
};
