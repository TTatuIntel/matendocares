<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointment_notifications', function (Blueprint $table) {
            $table->id();

            // Change these to match the appointments.id type (char(36))
            $table->uuid('appointment_id');
            $table->uuid('user_id');

            $table->string('type');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Add indexes
            $table->index(['user_id', 'read_at']);
        });

        // Add foreign key constraints separately
        Schema::table('appointment_notifications', function (Blueprint $table) {
            $table->foreign('appointment_id')
                  ->references('id')
                  ->on('appointments')
                  ->onDelete('cascade');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('appointment_notifications', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('appointment_notifications');
    }
};
