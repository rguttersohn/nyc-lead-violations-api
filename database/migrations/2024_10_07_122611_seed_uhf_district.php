<?php

use App\Models\District;
use App\Models\DistrictType;
use Database\Seeders\UHFSeeder;
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
        $district = new District();

        $district_type = new DistrictType();

        $seeder = new UHFSeeder();

        $seeder->run($district_type, $district);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
