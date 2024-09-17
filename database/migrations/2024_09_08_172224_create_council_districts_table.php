<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\CouncilDistrictSeeder;
use App\Models\CouncilDistrict;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('council_districts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->smallInteger('councildistrict')->unique();
            $table->string('geo_type', 100)->nullable();
            $table->geometry('polygon', subtype: 'polygon', srid: 4326)->nullable();
            $table->geometry('multipolygon', subtype: 'multipolygon', srid: 4326)->nullable();
        });

        $seeder = new CouncilDistrictSeeder();

        $council_district = new CouncilDistrict();
        
        $seeder->run($council_district);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('council_districts');
    }
};
