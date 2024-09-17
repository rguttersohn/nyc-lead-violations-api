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
        Schema::create('schedule_runs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->dateTime('completed_on')->nullable();
            $table->boolean('success');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_runs');
    }
};
