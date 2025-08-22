<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Create this as: 2025_08_02_160000_create_sessions_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('user_id')->nullable(); // Link to our users table
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
            
            // Medical system specific fields
            $table->enum('user_role', ['admin', 'doctor', 'patient', 'guest'])->nullable();
            $table->string('device_type')->nullable(); // web, mobile, tablet
            $table->json('session_metadata')->nullable(); // Store additional session data
            $table->timestamp('login_at')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('location')->nullable(); // IP-based location
            $table->boolean('is_temp_access')->default(false); // For temporary doctor access
            $table->uuid('temp_access_token')->nullable(); // Link to temp access
            
            // Security fields
            $table->boolean('force_logout')->default(false); // For admin forced logout
            $table->timestamp('expires_at')->nullable(); // Session expiration
            $table->integer('failed_attempts')->default(0); // Track failed actions
            $table->timestamp('blocked_until')->nullable(); // Temporary blocking
            
            // Indexes for performance
            $table->index(['user_id', 'is_active']);
            $table->index(['user_role', 'last_activity']);
            $table->index(['is_temp_access', 'temp_access_token']);
            $table->index('expires_at');
            
            // Foreign key to users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sessions');
    }
};