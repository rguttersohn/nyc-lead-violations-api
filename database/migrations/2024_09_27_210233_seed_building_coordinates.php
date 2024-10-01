<?php

use Illuminate\Database\Migrations\Migration;
use Database\Seeders\BuildingCoordsSeeder;
use App\Models\Building;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $building = new Building();
        $seeder = new BuildingCoordsSeeder();

        $seeder->run($building);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
