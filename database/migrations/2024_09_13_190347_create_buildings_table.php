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
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('nyc_open_data_building_id')->unique();
            $table->integer('bin')->nullable();
            $table->text('housenumber');
            $table->text('streetname');
            $table->string('geo_type', 100);
            $table->geometry('point', subtype: 'point', srid: 4326)->nullable();
            $table->text('boro')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
