<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('health_tips', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('title');
            $table->text('content');
            $table->string('category')->default('general');
            $table->string('provided_by')->nullable();
            $table->string('provider_facility')->nullable();
            $table->string('provider_specialty')->nullable();
            $table->enum('source', ['temp_access', 'registered_doctor', 'system', 'patient'])->default('system');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'is_active']);
            $table->index(['category', 'source']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('health_tips');
    }
};