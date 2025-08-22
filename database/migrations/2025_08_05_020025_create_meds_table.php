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
        Schema::create('meds', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Use UUID for consistency

            // Match the users.id type (char(36))
            $table->uuid('user_id');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Your other columns
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('dosage');
            $table->string('frequency');
            $table->json('times');
            $table->date('start_date');
            $table->string('prescribed_by')->nullable();
            $table->string('purpose')->nullable();
            $table->text('instructions')->nullable();
            $table->integer('refills')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            // Index for better performance
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meds');
    }
};
