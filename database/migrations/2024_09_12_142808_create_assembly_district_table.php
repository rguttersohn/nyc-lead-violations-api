<?php

use App\Models\AssemblyDistrict;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\AssemblyDistrictSeeder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assembly_districts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->smallInteger('assemblydistrict')->unique();
            $table->string('geo_type', 100)->nullable();
            $table->geometry('polygon', subtype: 'polygon', srid: 4326)->nullable();
            $table->geometry('multipolygon', subtype: 'multipolygon', srid: 4326)->nullable();
        });

        $assembly_district = new AssemblyDistrict();

        $seeder = new AssemblyDistrictSeeder();

        $seeder->run($assembly_district);


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assembly_districts');
    }
};
