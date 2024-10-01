<?php

use Database\Seeders\DistrictSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\District;
use App\Models\DistrictType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            
            $table->id();
            $table->timestamps();
            $table->foreignId('district_type_id')->constrained('district_types', 'id');
            $table->smallInteger('number');
            $table->string('geo_type', 100)->nullable();
            $table->geometry('polygon', subtype: 'polygon', srid: 4326)->nullable();
            $table->geometry('multipolygon', subtype: 'multipolygon', srid: 4326)->nullable();
        
        });

        $district = new District();
        $district_type = new DistrictType();
        $seeder = new DistrictSeeder();
        $seeder->run($district, $district_type);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
