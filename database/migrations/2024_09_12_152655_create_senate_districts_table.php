<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SenateDistrict;
use Database\Seeders\SenateDistrictsSeeder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('senate_districts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->smallInteger('senatedistrict')->unique();
            $table->string('geo_type', 100)->nullable();
            $table->geometry('polygon', subtype: 'polygon', srid: 4326)->nullable();
            $table->geometry('multipolygon', subtype: 'multipolygon', srid: 4326)->nullable();
        });

        $senate_district = new SenateDistrict();

        $seeder = new SenateDistrictsSeeder();

        $seeder->run($senate_district);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('senate_districts');
    }
};
