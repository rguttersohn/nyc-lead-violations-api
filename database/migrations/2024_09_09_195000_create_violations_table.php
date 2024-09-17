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
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('nyc_open_data_violation_id')->unique();
            $table->string('apartment', 10)->nullable();
            $table->foreignId('building_id')->constrained('buildings', 'nyc_open_data_building_id')->cascadeOnDelete();
            $table->foreignId('ordernumber')->constrained('codes', 'ordernumber')->cascadeOnDelete();
            $table->date('inspectiondate');
            $table->date('currentstatusdate');
            $table->integer('currentstatusid');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
