<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_read_models', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->boolean('email_verified')->default(false);
            $table->timestamp('registration_date');
            $table->timestamp('last_login')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('email');
            $table->index('status');
            $table->index('email_verified');
            $table->index('registration_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_read_models');
    }
};