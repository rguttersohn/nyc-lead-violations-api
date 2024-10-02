<?php

use App\Models\Housing;
use App\Models\District;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\HousingSeeder;
use App\Models\DistrictType;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('housing', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('district_id')->constrained('districts', 'id')->cascadeOnDelete();
            $table->integer('units');
            $table->text('source');
        });


        $district = new District();
        $housing = new Housing();
        $district_type = new DistrictType();
        $seeder = new HousingSeeder();

        $seeder->run($housing, $district, $district_type);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housing');
    }
};
