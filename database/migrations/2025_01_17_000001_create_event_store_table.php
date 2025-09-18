<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_store', function (Blueprint $table) {
            $table->id();
            $table->uuid('aggregate_id');
            $table->string('event_type');
            $table->json('event_data');
            $table->integer('version');
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['aggregate_id', 'version']);
            $table->unique(['aggregate_id', 'version']);
            $table->index('event_type');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_store');
    }
};