<?php

use App\Models\DistrictType;
use Database\Seeders\DistricTypeSeeder;
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
        Schema::create('district_types', function (Blueprint $table) {
            
            $table->id();
            $table->timestamps();
            $table->text('type');
            
        });

        $district_type = new DistrictType();
        $seeder = new DistricTypeSeeder();
        $seeder->run($district_type);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('district_types');
    }
};
