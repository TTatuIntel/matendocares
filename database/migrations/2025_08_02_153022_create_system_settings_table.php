<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2024_01_01_000015_create_system_settings_table.php
return new class extends Migration
{
    public function up()
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json, etc.
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // Group settings by category
            $table->boolean('is_public')->default(false); // Can be accessed by non-admin users
            $table->boolean('requires_restart')->default(false); // If changing requires system restart
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['category', 'is_public']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_settings');
    }
};